<?php

use CRM_HRCore_Menu_Item as MenuItem;

class CRM_HRCore_Helper_Menu_Parser {

  /**
   * Builds the navigation menu tree and returns in a format expected
   * by Civi in order to generate the navigation menu. It accepts an
   * array of MenuItem objects
   *
   * @param array $menuObjects
   *   An array of MenuItem Objects
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
  public static function getNavigationTree(array $menuObjects) {
    $navID =1;
    return self::buildMenuTree($menuObjects, 0, $navID);
  }

  /**
   * Accepts an array of MenuItem Objects to build the menu tree, alongside
   * the parent and navigation Id required by civi to build the navigation
   * menu
   *
   * @param array $items
   *   An array of MenuItem Objects.
   * @param int $parentID
   * @param int $navID
   *
   * @return array
   */
  private static function buildMenuTree(array $items, $parentID = 0, &$navID) {
    $weight = 1;
    $navigationTree = [];

    foreach ($items as $menuObject) {
      if(!$menuObject instanceof MenuItem) {
        throw new RuntimeException('Menu Item should be an instance of '.  MenuItem::class);
      }

      $attributes = [
        'label' => $menuObject->getLabel(),
        'name' => $menuObject->getLabel(),
        'url' => $menuObject->getUrl(),
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
        $menuItem['child'] = self::buildMenuTree($menuObject->getChildren(), $attributes['navID'], $navID);
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
