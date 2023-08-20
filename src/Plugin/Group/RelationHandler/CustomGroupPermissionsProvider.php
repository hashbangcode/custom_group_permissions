<?php

namespace Drupal\custom_group_permissions\Plugin\Group\RelationHandler;

use Drupal\group\Plugin\Group\RelationHandler\PermissionProviderInterface;
use Drupal\group\Plugin\Group\RelationHandler\PermissionProviderTrait;

/**
 * Provides group permissions for the custom_group_permissions relation plugin.
 *
 * @see \Drupal\custom_group_permissions\Plugin\Group\Relation\CustomGroupAccessControl
 */
class CustomGroupPermissionsProvider implements PermissionProviderInterface {
  use PermissionProviderTrait;

  /**
   * Constructs a new GroupMembershipPermissionProvider.
   *
   * @param \Drupal\group\Plugin\Group\RelationHandler\PermissionProviderInterface $parent
   *   The parent permission provider.
   */
  public function __construct(PermissionProviderInterface $parent) {
    $this->parent = $parent;
  }

  /**
   * {@inheritdoc}
   */
  public function getPermission($operation, $target, $scope = 'any') {
    // Backwards compatible permission name for 'any' scope.
    if ($operation === 'view' && $target === 'entity' && $scope === 'any') {
      return "$operation $this->pluginId $target";
    }
  }

}
