<?php

trait CRM_HRCore_Upgrader_Steps_1032 {

  /**
   * Changes the Staff Menu Structure
   *
   * @return bool
   */
  public function upgrade_1032() {
    $this->up1032_modifyNewIndividualMenu();
    $this->up1032_createStaffDirectoryMenu();
    $this->up1032_createCommunicationsMenu();
    $this->up1032_moveNewEmailSubmenu();
    $this->up1032_createCommunicationsSubmenus();
    $this->up1032_removeOldMenuItems([
      'New Organisation',
      'New Organization',
      'New Group',
      'Manage Groups',
      'Find and Merge Duplicate Contacts',
    ]);
    $this->up1032_disableImportMenus([
      'Import Contacts',
      'import_export_job_contracts',
      'import_custom_fields',
    ]);

    return TRUE;
  }

  /**
   * Renames the New Individual Menu to Add New Staff Member
   */
  private function up1032_modifyNewIndividualMenu() {
    civicrm_api3('Navigation', 'get', [
      'name' => 'New Individual',
      'api.Navigation.create' => [
        'id' => '$value.id',
        'label' => 'Add New Staff Member',
        'name' => 'Add New Staff Member',
        'url' => 'civicrm/import/contact?reset=1&force=1',
      ],
    ]);
  }

  /**
   * Creates a new menu Item
   */
  private function up1032_createStaffDirectoryMenu() {
    $menu = civicrm_api3('Navigation', 'get', [
      'name' => 'Add New Staff Member',
    ]);

    $menu = array_shift($menu['values']);
    $menuExists = civicrm_api3('Navigation', 'get', [
      'name' => 'Staff Directory',
    ]);
    if ($menuExists['count'] === 0) {
      civicrm_api3('Navigation', 'create', [
        'label' => 'Staff Directory',
        'name' => 'Staff Directory',
        'url' => 'civicrm/contact/search/advanced?reset=1&force=1',
        'parent_id' => $menu['parent_id'],
        'domain_id' => $menu['domain_id'],
        'permission' => $menu['permission'],
        'is_active' => 1,
        'weight' => 2,
        'has_separator' => 1,
      ]);
      // If we don't flush it will not recognize newly created parent_id
      CRM_Core_PseudoConstant::flush();
    }

  }

  /**
   * Creates a new menu heading for Communications
   */
  private function up1032_createCommunicationsMenu() {
    $menu = civicrm_api3('Navigation', 'get', [
      'name' => 'Add New Staff Member',
    ]);

    $menu = array_shift($menu['values']);
    $menuExists = civicrm_api3('Navigation', 'get', [
      'name' => 'Record Communication',
    ]);
    if ($menuExists['count'] === 0) {
      civicrm_api3('Navigation', 'create', [
        'label' => 'Record Communication',
        'name' => 'Record Communication',
        'parent_id' => $menu['parent_id'],
        'domain_id' => $menu['domain_id'],
        'permission' => $menu['permission'],
        'is_active' => 1,
        'weight' => 3,
      ]);
      // If we don't flush it will not recognize newly created parent_id
      CRM_Core_PseudoConstant::flush();
    }

  }

  /**
   * Moves the New Email submenu to be child of Communications
   */
  private function up1032_moveNewEmailSubmenu() {
    $menu = civicrm_api3('Navigation', 'get', [
      'name' => 'Record Communication',
    ]);

    $menu = array_shift($menu['values']);
    civicrm_api3('Navigation', 'get', [
      'name' => 'New Email',
      'api.Navigation.create' => [
        'id' => '$value.id',
        'parent_id' => $menu['id'],
        'has_separator' => 0,
        'weight' => 1,
      ],
    ]);
  }

  /**
   * Creates a new submenu item under Communications
   */
  private function up1032_createCommunicationsSubmenus() {
    $menu = civicrm_api3('Navigation', 'get', [
      'name' => 'Record Communication',
    ]);

    $menu = array_shift($menu['values']);
    $menuExists = civicrm_api3('Navigation', 'get', [
      'name' => 'New Meeting',
    ]);
    if ($menuExists['count'] === 0) {
      civicrm_api3('Navigation', 'create', [
        'label' => 'New Meeting',
        'name' => 'New Meeting',
        'parent_id' => $menu['id'],
        'domain_id' => $menu['domain_id'],
        'permission' => $menu['permission'],
        'is_active' => 1,
        'weight' => 2,
        'url' => '/activity?reset=1&action=add&context=standalone',
      ]);
      // If we don't flush it will not recognize newly created parent_id
      CRM_Core_PseudoConstant::flush();
    }

  }

  /**
   * Removes the old menu items under Staff Menu
   *
   * @param array $menus
   */
  private function up1032_removeOldMenuItems($menus) {
    foreach ($menus as $menuLabel) {
      civicrm_api3('Navigation', 'get', [
        'name' => $menuLabel,
        'api.Navigation.delete' => ['id' => '$value.id'],
      ]);
    }
  }

  /**
   * Disables the Import Menu items
   *
   * @param array $menus
   */
  private function up1032_disableImportMenus($menus) {
    foreach ($menus as $menuLabel) {
      civicrm_api3('Navigation', 'get', [
        'name' => $menuLabel,
        'api.Navigation.create' => ['id' => '$value.id', 'is_active' => 0],
      ]);
    }
  }

}
