<?php

namespace Drupal\custom_group_permissions\Controller;

use Drupal\config_pages\Entity\ConfigPages;
use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\GroupInterface;

/**
 * A simple controller to show the use of Group permissions in routes.
 */
class ReportController extends ControllerBase {

  /**
   * A route that prints out a single Group title.
   *
   * See custom_group_permissions.example for the definition of this route.
   *
   * To access this route the user must:
   * - Have the Drupal permission 'access content'.
   * - Have the Group permission 'access group reports page', either as an
   *   insider or an outsider.
   * - Be a member of the Group.
   *
   * @param GroupInterface $group
   *   The Group entity from the path.
   *
   * @return array
   *   The render array.
   */
  public function report(GroupInterface $group) {
    $output = [];

    $output['title'] = [
      '#markup' => $group->label(),
    ];

    return $output;
  }

}
