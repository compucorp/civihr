<?php

/**
 * Helper class to interact with a CiviCRM navigation menu array
 */
class CRM_HRCore_Helper_NavigationMenuHelper {

  /**
   * Recursively search for a menu item by its name
   *
   * @param array $menu
   *   The menu array to search in
   * @param string $path
   *   The path to the item, e.g. "Events/Dashboard"
   *
   * @return array|null
   *   The menu item, if found, or null otherwise
   */
  public static function findMenuItemByPath($menu, $path) {
    return self::findMenuItemReferenceByPath($menu, $path);
  }

  /**
   * Inserts a navigation menu item as a child of the given parent
   *
   * @param array $menu
   *   menu hierarchy
   * @param string $parentPath
   *   Path to the parent menu item, e.g. "Events/Dashboard"
   * @param array $itemAttributes
   *   Attributes of item to insert, e.g. ['name' => 'New Item']
   */
  public static function insertChild(&$menu, $parentPath, $itemAttributes) {
    $parent = &self::findMenuItemReferenceByPath($menu, $parentPath);

    if (!$parent) {
      $err = sprintf('Cannot find parent item "%s"', $parentPath);
      throw new \Exception($err);
    }

    $label = CRM_Utils_Array::value('name', $itemAttributes);
    $defaults = ['label' => $label, 'active' => 1];
    $newItem = ['attributes' => array_merge($defaults, $itemAttributes)];

    $parent['child'][] = $newItem;
  }

  /**
   * Moves a menu item to before another specified menu item
   *
   * @param $menu
   *   The full menu structure
   * @param $itemToMovePath
   *   The path to the menu item we want to move
   * @param $followingItemPath
   *   The path to the item that the target should be inserted before
   */
  public static function relocateBefore(
    &$menu,
    $itemToMovePath,
    $followingItemPath
  ) {
    self::relocateWithOffset($menu, $itemToMovePath, $followingItemPath, -1);
  }

  /**
   * Moves a menu item to after another specified menu item
   *
   * @param array $menu
   *   The full menu structure
   * @param string $itemToMovePath
   *   The path to the menu item we want to move
   * @param string $precedingItemPath
   *   The path to the item that the target should be inserted after
   */
  public static function relocateAfter(
    &$menu,
    $itemToMovePath,
    $precedingItemPath
  ) {
    self::relocateWithOffset($menu, $itemToMovePath, $precedingItemPath, 1);
  }

  /**
   * Moves a given item to the same submenu as another target item. Can be
   * placed before or after the target item using an offset.
   *
   * @param $menu
   *   The full menu structure
   * @param $itemToMovePath
   *   The path to the menu item we want to move
   * @param $targetSiblingPath
   *   The path to the sibling the item will be moved next to
   * @param int $offset
   *   How many places the item should be before / after the sibling item. Use
   *   1 to place it right after it, -1 to put before it
   */
  private static function relocateWithOffset(
    &$menu,
    $itemToMovePath,
    $targetSiblingPath,
    $offset
  ) {
    if ($offset === 0) {
      throw new \Exception('Offset cannot be zero');
    }

    $siblings = &self::getSiblingsReference($menu, $targetSiblingPath);
    $itemToMove = self::findMenuItemByPath($menu, $itemToMovePath);

    // remove original from the menu
    self::remove($menu, $itemToMovePath);

    // re-index and find point to insert the new item
    $siblings = array_values($siblings);
    $precedingItemName = self::getItemNameFromPath($targetSiblingPath);
    $insertionIndex = self::getMenuItemIndex($siblings, $precedingItemName);

    if (NULL === $insertionIndex) {
      $err = sprintf('Could not find menu item "%s"', $targetSiblingPath);
      throw new \Exception($err);
    }

    $insertionIndex += $offset;

    // the offset is fine as it is for positive offsets, but we skip 0 when
    // changing from 'insert after' (offset 1) to 'insert before' (offset -1)
    // so we need to adjust negative offsets
    if ($offset < 0) {
      $insertionIndex++;
    }

    array_splice($siblings, $insertionIndex, 0, [$itemToMove]);
  }

  /**
   * Removes an item from the menu by path
   *
   * @param array $menu
   * @param string $path
   */
  public static function remove(&$menu, $path) {
    $siblings = &self::getSiblingsReference($menu, $path);
    $itemName = self::getItemNameFromPath($path);

    foreach ($siblings as $index => $sibling) {
      if ($sibling['attributes']['name'] === $itemName) {
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
   * @param string $path
   *   The path to the item, e.g. "Events/Dashboard"
   * @param string $newPermission
   *   The new permission for accessing this item
   */
  public static function updatePermissionByPath(&$menu, $path, $newPermission) {
    $item = &self::findMenuItemReferenceByPath($menu, $path);

    if (!$item) {
      $err = sprintf('Cannot find menu item with path "%s"', $path);
      throw new \Exception($err);
    }

    $item['attributes']['permission'] = $newPermission;
  }


  /**
   * @param array $menu
   *   The menu array to search in
   * @param string $path
   *   The path to the item, e.g. "Events/Dashboard"
   *
   * @return array|null
   *   The menu item if found, or NULL if not
   */
  private static function &findMenuItemReferenceByPath(&$menu, $path) {
    if ($path === '') {
      return $menu;
    }

    $path = explode('/', $path);
    $submenu = &$menu;

    while (count($path) > 1) {
      $parentName = array_shift($path);
      $submenu = &self::findMenuItemReferenceByName($submenu, $parentName);
      if (!isset($submenu['child'])) {
        $null = NULL; // must return reference to variable

        return $null;
      }
      $submenu = &$submenu['child'];
    }

    $itemName = array_shift($path);
    $item = &self::findMenuItemReferenceByName($submenu, $itemName);

    return $item;
  }

  /**
   * Search for a menu item by its name. Returns a reference to the item.
   *
   * @param array $menu
   *   The menu to search, can be a submenu of the full structure
   * @param string $name
   *   The name of the item to search for
   *
   * @return array|null
   *   The referenced menu item, if found, or null otherwise
   */
  private static function &findMenuItemReferenceByName(&$menu, $name) {
    foreach ($menu as &$item) {
      if ($item['attributes']['name'] === $name) {
        return $item;
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
   * @param string $path
   *   The path of the menu item whose siblings we want
   *
   * @return array
   */
  private static function &getSiblingsReference(&$menu, $path) {
    $finalForwardSlashPos = strrpos($path, '/');

    if (FALSE !== $finalForwardSlashPos) {
      $parentPath = substr($path, 0, $finalForwardSlashPos);
      $parent = &self::findMenuItemReferenceByPath($menu, $parentPath);
      $siblings = &$parent['child'];
    }
    else {
      $siblings = &$menu;
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

  /**
   * Gets the last item name in a menu item path
   *
   * @param string $path
   *   The path of the menu item, e.g. "Events/Dashboard"
   *
   * @return string
   *   The final item in the path, e.g. "Dashboard"
   */
  private static function getItemNameFromPath($path) {
    $path = explode('/', $path);

    return end($path);
  }

}
