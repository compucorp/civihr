<?php

/**
 * Helper class to interact with a CiviCRM navigation menu array
 */
class CRM_HRCore_Helper_NavigationMenuHelper {

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
   * Inserts a navigation menu item as a child of the given parent
   *
   * @param array $menu
   *   menu hierarchy
   * @param string $parentName
   *   Name of the parent menu item
   * @param array $itemAttributes
   *   Attributes of item to insert, e.g. ['name' => 'New Item']
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
   * Moves a menu item to after another specified menu item
   *
   * @param array $menu
   *   The full menu structure
   * @param string $itemToMoveName
   *   The name of the menu item we want to move
   * @param string $precedingItemName
   *   The name of the item that the target should be inserted after
   */
  public static function relocateAfter(
    &$menu,
    $itemToMoveName,
    $precedingItemName
  ) {
    $siblings = &self::getSiblingsReference($menu, $precedingItemName);
    $itemToMove = self::findMenuItemByName($menu, $itemToMoveName);

    // remove original from the menu
    self::remove($menu, $itemToMoveName);

    // re-index and find point to insert the new item
    $siblings = array_values($siblings);
    $insertionIndex = self::getMenuItemIndex($siblings, $precedingItemName);

    if (NULL === $insertionIndex) {
      $err = sprintf('Could not find menu item "%s"', $precedingItemName);
      throw new \Exception($err);
    }

    $insertionIndex++; // we want to insert it after
    array_splice($siblings, $insertionIndex, 0, [$itemToMove]);
  }

  /**
   * Removes an item from the menu by name
   *
   * @param array $menu
   * @param string $name
   */
  public static function remove(&$menu, $name) {
    $siblings = &self::getSiblingsReference($menu, $name);

    foreach ($siblings as $index => $sibling) {
      if ($sibling['attributes']['name'] === $name) {
        unset($siblings[$index]);
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
  public static function findParentItemName($menu, $childName) {
    return self::findNestedItemParentName($menu, $childName, 'root');
  }

  /**
   * Recursively search for a menu item's parent name by the child name
   *
   * @param array $menu
   *   The full menu structure
   * @param string $childName
   *   The name of the child we are searching for
   * @param string $parentAtThisLevel
   *   The name of the parent at the current level of nesting
   *
   * @return string|null
   *   The parent item name, if found, or null otherwise.
   *   If item is at top level parent name will be 'root'
   */
  private static function findNestedItemParentName(
    $menu,
    $childName,
    $parentAtThisLevel
  ) {
    foreach ($menu as $item) {
      if ($item['attributes']['name'] === $childName) {
        return $parentAtThisLevel;
      }
      if (!empty($item['child'])) {
        $children = $item['child'];
        $found = self::findNestedItemParentName(
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
   * Returns an array containing all sibling items on the same menu level.
   * This includes the target item.
   *
   * @param array $menu
   *   The full menu structure
   * @param string $name
   *   The name of the menu item whose siblings we want
   *
   * @return array
   */
  private static function &getSiblingsReference(&$menu, $name) {
    $parentName = self::findParentItemName($menu, $name);

    if ($parentName === NULL) {
      $err = sprintf('Cannot find parent of menu item "%s"', $name);
      throw new \Exception($err);
    }

    if ($parentName === 'root') {
      $siblings = &$menu;
    }
    else {
      $parent = &self::findMenuItemReferenceByName($menu, $parentName);
      $siblings = &$parent['child'];
    }

    return $siblings;
  }

  /**
   * Like array_search, but for menu items
   *
   * @param array $items
   * @param string $name
   *
   * @return bool|int
   */
  private static function getMenuItemIndex($items, $name) {
    foreach ($items as $index => $sibling) {
      if ($sibling['attributes']['name'] === $name) {
        return $index;
      }
    }

    return FALSE;
  }

}
