<?php

use CRM_HRContactActionsMenu_Component_Menu as ActionsMenu;

/**
 * Class CRM_HRContactActionsMenu_Hook_AddContactMenuActions
 */
class CRM_HRContactActionsMenu_Hook_AddContactMenuActions {

  /**
   * Runs all extensions implementation of
   * hook_addContactMenuActions and returns the
   * menu object.
   *
   * @return \CRM_HRContactActionsMenu_Component_Menu
   */
  public static function invoke() {
    $menu =  new ActionsMenu;

    CRM_Utils_Hook::singleton()->invoke(['menu'],
      $menu,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      'addContactMenuActions'
    );

    return $menu;
  }
}
