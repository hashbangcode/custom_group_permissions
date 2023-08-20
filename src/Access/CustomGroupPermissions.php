<?php

namespace Drupal\custom_group_permissions\Access;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions for groups of different types.
 *
 * This class is included via the permission_callbacks operation in the
 * x.group.permissions.yml file. It will create a permission for every field in
 * each group.
 *
 * No access checks exist for these permissions. With the exception of the
 * title field, which has an access check in the x.module file.
 */
class CustomGroupPermissions implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = new static();
    $instance->setEntityFieldManager($container->get('entity_field.manager'));
    return $instance;
  }

  /**
   * Sets the entity field manager service.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager service.
   *
   * @return self
   *   The current object.
   */
  public function setEntityFieldManager(EntityFieldManagerInterface $entityFieldManager): self {
    $this->entityFieldManager = $entityFieldManager;
    return $this;
  }

  /**
   * Returns an array of group type permissions.
   *
   * @return array
   *   The group permissions.
   */
  public function groupPermissions() {
    $perms = [];

    foreach ($this->entityFieldManager->getBaseFieldDefinitions('group') as $field => $definition) {
      if ($definition['read-only'] === TRUE) {
        continue;
      }
      $perms['edit group ' . $field] = [
        'title' => 'Edit group @fieldname',
        'title_args' => [
          '@fieldname' => $definition['label'],
        ]
      ];
    }

    return $perms;
  }

}
