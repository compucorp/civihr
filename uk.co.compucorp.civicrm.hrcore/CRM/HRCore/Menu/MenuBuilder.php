<?php

use CRM_HRCore_Menu_Config as MenuConfig;
use CRM_HRCore_Menu_Item as MenuItem;


class CRM_HRCore_Menu_MenuBuilder {

  /**
   * This function reads the items from a menu config class
   * and translates it to an array of CiviHR MenuItem objects.
   * This array of objects can be given to any other function
   * to translate to whatever format is needed in order to
   * build the navigation menu.
   *
   * @param MenuConfig $menuConfig
   *
   * @return MenuItem
   */
  public function getMenuItems(MenuConfig $menuConfig) {
   $items = $menuConfig->getItems();
   $menuObjects = $this->generateMenuObjects($items);

   // The parent menu is a blank menu item that has the top level
   // navigation menu items as children.
   $parentMenu = new MenuItem('');

   foreach($menuObjects as $item) {
     $parentMenu->addChild($item);
   }

   return $parentMenu;
  }

  /**
   * Generates an array of menuItem objects from the
   * menu config Items.
   *
   * @param array $items
   *
   * @return MenuItem[]
   */
  private function generateMenuObjects($items) {
    $menuObjects = [];

    foreach ($items as $label => $attributes) {
      // If the attribute is not an array, it is the URL based on the
      // Menu config format.
      if (!is_array($attributes)) {
        $attributes = ['url' => $attributes];
      }

      $menu = new MenuItem($label);
      $menu->setUrl(CRM_Utils_Array::value('url', $attributes))
           ->setPermission(CRM_Utils_Array::value('permission', $attributes))
           ->setOperator(CRM_Utils_Array::value('operator', $attributes))
           ->setIcon(CRM_Utils_Array::value('icon', $attributes));

      if (!empty($attributes['separator'])) {
        $menu->addSeparator();
      }

      if (!empty($attributes['children'])) {
        $children = $this->generateMenuObjects($attributes['children']);
        foreach($children as $child) {
          $menu->addChild($child);
        }
      }

      $menuObjects[] = $menu;
    }

    return $menuObjects;
  }
}
