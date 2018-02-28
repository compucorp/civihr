<?php

/**
 * Helper class to interact with a CiviCRM navigation menu array
 */
class CRM_HRCore_Helper_NavigationMenuHelper {

  /**
   * Inserts a navigation menu item at a given place in the hierarchy.
   *
   * @param array $menu
   *   menu hierarchy
   * @param string $parentName
   *   Name of the parent menu item
   * @param array $itemAttributes
   *   Menu you need to insert
   */
  public static function insertChild(&$menu, $parentName, $itemAttributes) {
    $parent = &self::findMenuItemReferenceByName($menu, $parentName);

    if (!$parent) {
      $err = sprintf('Cannot find parent item "%s"', $parentName);
      throw new \Exception($err);
    }

    $label = CRM_Utils_Array::value('name', $itemAttributes);
    $defaults = ['label' => $label, 'active' => 1];
    $newItem = ['attributes' => array_merge($defaults, $itemAttributes)];

    $parent['child'][] = $newItem;
  }

  /**
   * @param $menu
   * @param $nameOfItemToMove
   * @param $nameOfItemBefore
   * @throws Exception
   */
  public static function relocateAfter(&$menu, $nameOfItemToMove, $nameOfItemBefore) {
    $parentName = self::findParentItemName($menu, $nameOfItemBefore);

    if ($parentName === 'root') {
      $children = &$menu;
    }
    else {
      $parent = &self::findMenuItemReferenceByName($menu, $parentName);
      $children = &$parent['child'];
    }

    $itemToMove = self::findMenuItemByName($menu, $nameOfItemToMove);
    self::remove($menu, $nameOfItemToMove);

    $targetIndex = NULL;
    $offset = 1; // because we want to insert it after
    $found = FALSE;
    foreach ($children as $index => $child) {
      if ($child['attributes']['name'] === $nameOfItemBefore) {
        $found = TRUE;
        break;
      }
      $offset++;
    }

    if (!$found) {
      $err = sprintf('Could not find menu item "%s"', $nameOfItemBefore);
      throw new \Exception($err);
    }

    array_splice($children, $offset, 0, [$itemToMove]);
  }

  public static function remove(&$menu, $name) {
    $parentName = self::findParentItemName($menu, $name);

    if ($parentName === NULL) {
      $err = sprintf('Cannot find parent of menu item "%s"', $name);
      throw new \Exception($err);
    }

    if ($parentName === 'root') {
      $children = &$menu;
    }
    else {
      $parent = &self::findMenuItemReferenceByName($menu, $parentName);
      $children = &$parent['child'];
    }

    foreach ($children as $index => $child) {
      if ($child['attributes']['name'] === $name) {
        unset($children[$index]);
        return;
      }
    }
  }

  /**
   * Sets the permission on a named menu item
   *
   * @param array $menu
   *   The full menu
   * @param string $name
   *   The name of the target menu item
   * @param string $newPermission
   *   The new permission for accessing this item
   */
  public static function updatePermissionByName(&$menu, $name, $newPermission) {
    $item = &self::findMenuItemReferenceByName($menu, $name);

    if (!$item) {
      $err = sprintf('Cannot find menu item with name "%s"', $name);
      throw new \Exception($err);
    }

    $item['attributes']['permission'] = $newPermission;
  }

  /**
   * Recursively search for a menu item by its name
   *
   * @param array $menu
   * @param string $name
   *
   * @return array|null
   *   The menu item, if found, or null otherwise
   */
  public static function findMenuItemByName($menu, $name) {
    return self::findMenuItemReferenceByName($menu, $name);
  }

  /**
   * Recursively search for a menu item by its name. Returns a reference to
   * the item.
   *
   * @param array $menu
   * @param string $name
   *
   * @return array|null
   *   The referenced menu item, if found, or null otherwise
   */
  private static function &findMenuItemReferenceByName(&$menu, $name) {
    foreach ($menu as &$item) {
      if ($item['attributes']['name'] === $name) {
        return $item;
      }
      if (!empty($item['child'])) {
        $found = &self::findMenuItemReferenceByName($item['child'], $name);

        if ($found) {
          return $found;
        }
      }
    }

    // must return reference to variable
    $item = NULL;

    return $item;
  }

  /**
   * Recursively search for a menu item's parent name by the child name
   *
   * This is a wrapper for a private function that requires a third argument.
   *
   * @param array $menu
   * @param string $childName
   *
   * @return string|null
   *   The parent item name, if found, or null otherwise.
   *   If item is at top level parent name will be 'root'
   */
  public static function findParentItemName($menu, $childName
  ) {
    return self::findParentItemNameFromRoot($menu, $childName, 'root');
  }

  /**
   * Recursively search for a menu item's parent name by the child name
   *
   * @param array $menu
   * @param string $childName
   * @param string $parentName
   *
   * @return string|null
   *   The parent item name, if found, or null otherwise.
   *   If item is at top level parent name will be 'root'
   */
  private static function findParentItemNameFromRoot(
    $menu,
    $childName,
    $parentName
  ) {
    foreach ($menu as $item) {
      if ($item['attributes']['name'] === $childName) {
        return $parentName;
      }
      if (!empty($item['child'])) {
        $children = $item['child'];
        $found = self::findParentItemNameFromRoot(
          $children,
          $childName,
          $item['attributes']['name']
        );

        if ($found) {
          return $found;
        }
      }
    }

    return NULL;
  }

}
