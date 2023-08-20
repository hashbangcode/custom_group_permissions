<?php

namespace Drupal\custom_group_permissions;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Drupal\custom_group_permissions\Access\CustomGroupUserPermissions;

/**
 * Service provider for the custom_group_permissions module.
 */
class CustomGroupPermissionsServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritDoc}
   */
  public function alter(ContainerBuilder $container) {
      // Decorate the entity access check.
//      $customGroupUserPermissionDefinition = new Definition(CustomGroupUserPermissions::class, [
//        new Reference('entity_type.manager'),
//        new Reference('group_relation_type.manager'),
//      ]);
//      $customGroupUserPermissionDefinition->setPublic(TRUE);
//      $customGroupUserPermissionDefinition->setDecoratedService('access_check.entity');
//      $container->setDefinition('custom_group_permissions.entity', $customGroupUserPermissionDefinition);
  }

}
