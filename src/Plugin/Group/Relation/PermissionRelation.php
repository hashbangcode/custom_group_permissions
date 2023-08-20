<?php

namespace Drupal\custom_group_permissions\Plugin\Group\Relation;

use Drupal\group\Plugin\Group\Relation\GroupRelationBase;

/**
 * Provides a group relation for User entities.
 *
 * @GroupRelationType(
 *   id = "custom_group_permissions",
 *   label = @Translation("My Custom Group Permissions"),
 *   description = @Translation("Adds permissions to the group."),
 *   entity_type_id = "user",
 *   entity_access = TRUE
 * )
 */
class PermissionRelation extends GroupRelationBase {

}
