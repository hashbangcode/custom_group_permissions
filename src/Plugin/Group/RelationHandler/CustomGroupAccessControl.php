<?php

namespace Drupal\custom_group_permissions\Plugin\Group\RelationHandler;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Plugin\Group\RelationHandler\AccessControlInterface;
use Drupal\group\Plugin\Group\RelationHandler\AccessControlTrait;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides group permissions for the custom_group_permissions relation plugin.
 *
 * @see \Drupal\custom_group_permissions\Plugin\Group\Relation\CustomGroupAccessControl
 */
class CustomGroupAccessControl implements AccessControlInterface {
  use AccessControlTrait;

  /**
   * Constructs a new CustomGroupAccessControl.
   *
   * @param \Drupal\group\Plugin\Group\RelationHandler\AccessControlInterface $parent
   *   The parent access control handler.
   */
  public function __construct(AccessControlInterface $parent) {
    $this->parent = $parent;
  }

  /**
   * {@inheritdoc}
   */
  public function entityAccess(EntityInterface $entity, $operation, AccountInterface $account, $return_as_object = FALSE) {
    // Assume we will return a neutral permission check by default.
    $access = AccessResultNeutral::neutral();

    if ($this->supportsOperation($operation, 'entity') === FALSE) {
      return $access;
    }

    $storage = $this->entityTypeManager->getStorage('group_relationship');
    $groupRelationships = $storage->loadByEntity($entity);
    if (empty($groupRelationships)) {
      // If the entity does not belong to any group, we have nothing to say.
      return $access;
    }

    /** @var \Drupal\group\Entity\GroupRelationship $groupRelationship */
    foreach ($groupRelationships as $groupRelationship) {
      $group = $groupRelationship->getGroup();
      $access = AccessResult::allowedIf($group->hasPermission("$operation $this->pluginId entity", $account));

      $owner_access = $access->orIf(AccessResult::allowedIf(
        $group->hasPermission("$operation $this->pluginId entity", $account)
        && $group->hasPermission("$operation own $this->pluginId entity", $account)
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
