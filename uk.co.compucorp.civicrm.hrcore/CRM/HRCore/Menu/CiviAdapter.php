<?php

use CRM_HRCore_Menu_Item as MenuItem;

class CRM_HRCore_Menu_CiviAdapter {

  /**
   * Builds the navigation menu tree and returns in a format expected
   * by Civi in order to generate the navigation menu. It accepts a
   * Menu Item instance
   *
   * @param MenuItem $menuItem
   *
   * @return array
   *  Sample return format:
   *  [
   *    'attributes' =>
   *      [
   *        'label' => 'Components',
   *         'url' => 'civicrm/bla',
   *         'permission' => 'sample permission',
   *         'operator' => 'AND',
   *      ],
   *   ],
   */
  public function getNavigationTree(MenuItem $menuItem) {
    $navID = 1;
    //The parentId is zero here to indicate that the top level navigation items
    //has no parents. Also the navID is incremented by 1 for each menu item generated
    //in the returning array, it starts at 1 and is passed by reference here.
    return $this->buildNavigationTree($menuItem->getChildren(), 0, $navID);
  }

  /**
   * Accepts an array of MenuItem Objects to build the menu tree, alongside
   * the parent and navigation Id required by civi to build the navigation
   * menu
   *
   * @param array $items
   *   An array of MenuItem Objects.
   * @param int $parentID
   *   The parent id of the navigation menu items
   *   supplied.
   * @param int $navID
   *   The navigation id is incremented in the function and
   *   its last value is passed here by reference in order to
   *   continue the increment (by 1).
   *
   * @return array
   */
  private function buildNavigationTree(array $items, $parentID = 0, &$navID) {
    $weight = 1;
    $navigationTree = [];

    foreach ($items as $menuObject) {
      $attributes = [
        'label' => $menuObject->getLabel(),
        'name' => $menuObject->getLabel(),
        'url' => $menuObject->getUrl(),
        'target' => $menuObject->getTarget(),
        'icon' => $menuObject->getIcon(),
        'weight' => $weight,
        'permission' => $menuObject->getPermission(),
        'operator' => $menuObject->getOperator(),
        // Civi understands 1 to be the value for separator after menu item.
        'separator' => $menuObject->hasSeparator() ? 1 : NULL,
        'parentID' => $parentID,
        'navID' => $navID,
        'active' => 1,
      ];

      $menuItem = ['attributes' => $attributes];
      $hasChildren = !empty($menuObject->getChildren());

      if ($hasChildren) {
        $navID++;
        $menuItem['child'] = $this->buildNavigationTree($menuObject->getChildren(), $attributes['navID'], $navID);
      }

      $navigationTree[] = $menuItem;
      if (!$hasChildren) {
        $navID++;
      }
      $weight++;
    }

    return $navigationTree;
  }
}
