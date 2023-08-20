<?php

namespace Drupal\custom_group_permissions\Access;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityAccessCheck;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Plugin\Group\Relation\GroupRelationTypeManagerInterface;
use Drupal\user\EntityOwnerInterface;
use Symfony\Component\Routing\Route;

/**
 * Performs a permission check on user entities in Groups.
 *
 * This class is a decoration of the access_check.entity service that allows
 * access checks to be performed on any entity type associated with a Group.
 * The entity must first be in a relationship with the Group for the access
 * check to work.
 * The access check is triggered from the hook_entity_access() hook in the
 * module file.
 *
 * @see custom_group_permissions_entity_access().
 */
class CustomGroupUserPermissions extends EntityAccessCheck {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The group content enabler plugin manager.
   *
   * @var \Drupal\group\Plugin\Group\Relation\GroupRelationTypeManagerInterface
   */
  protected GroupRelationTypeManagerInterface $groupRelationTypeManager;

  /**
   * Constructs the group latest revision check object.
   *
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderation_information
   *   The moderation information service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\group\Plugin\Group\Relation\GroupRelationTypeManagerInterface $group_relation_type_manager
   *   The group content enabler plugin manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, GroupRelationTypeManagerInterface $group_relation_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->groupRelationTypeManager = $group_relation_type_manager;
  }

  /**
   * {@inheritDoc}
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    $access = parent::access($route, $route_match, $account);
    if (!$access->isAllowed()) {
      // Load the entity from the route.
      $requirement = $route->getRequirement('_entity_access');
      [$entity_type, $operation] = explode('.', $requirement);

      $parameters = $route_match->getParameters();
      if ($parameters->has($entity_type)) {
        $entity = $parameters->get($entity_type);
        if ($entity instanceof EntityInterface) {
          // Get the specific group access for this entity.
          $group_access = $this->checkGroupAccess($entity, $operation, $account);
          // Combine the group access with the upstream access.
          $access = $access->orIf($group_access);
        }
      }
    }

    return $access;
  }

  /**
   * Determine group-specific access to an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to check.
   * @param string $operation
   *   The operation to check access for.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user to check access for.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Returns allowed access if the entity belongs to a group, and the user
   *   has both the 'view custom_group_permissions entity' and the
   *   'view own custom_group_permissions entity' permission in a group it
   *   belongs to.
   */
  protected function checkGroupAccess(ContentEntityInterface $entity, $operation, AccountInterface $account) {
    // Assume we will return a neutral permission check by default.
    $access = AccessResultNeutral::neutral();

    $storage = $this->entityTypeManager->getStorage('group_relationship');
    $groupRelationships = $storage->loadByEntity($entity);
    if (empty($groupRelationships)) {
      // If the entity does not belong to any group, we have nothing to say.
      return $access;
    }

    /** @var \Drupal\group\Entity\GroupRelationship $groupRelationship */
    foreach ($groupRelationships as $groupRelationship) {
      $group = $groupRelationship->getGroup();
      $access = AccessResult::allowedIf($group->hasPermission("$operation custom_group_permissions entity", $account));

      $owner_access = $access->orIf(AccessResult::allowedIf(
        $group->hasPermission("$operation custom_group_permissions entity", $account)
        && $group->hasPermission("$operation own custom_group_permissions entity", $account)
        && $entity instanceof EntityOwnerInterface
        && $entity->getOwnerId() === $account->id()
      ));

      $access = $access->orIf($owner_access);

      $access->addCacheableDependency($groupRelationship);
      $access->addCacheableDependency($group);
    }

    return $access;
  }

}
