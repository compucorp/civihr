<?php

trait CRM_HRCore_Upgrader_Steps_1035 {

  /**
   * Move Import Menus to Configure
   *
   * @return bool
   */
  public function upgrade_1035() {
    $this->up1035_createImportMenu();
    $this->up1035_moveImportMenus([
      'Import Contacts',
      'import_job_contracts',
    ]);
    $this->up1035_renameImportContactsMenu();
    $this->up1035_adjustImportMenuWeight();
    // If we don't flush it will not recognize newly created parent_id
    CRM_Core_PseudoConstant::flush();

    return TRUE;
  }

  /**
   * Creates a new menu heading for Import
   */
  private function up1035_createImportMenu() {
    $menu = civicrm_api3('Navigation', 'get', [
      'name' => 'leave_and_absences',
    ]);

    try {
      civicrm_api3('Navigation', 'getsingle', ['name' => 'Import']);
    } catch (CiviCRM_API3_Exception $e) {
      civicrm_api3('Navigation', 'create', [
        'label' => 'Import',
        'name' => 'Import',
        'parent_id' => $menu['parent_id'],
        'domain_id' => $menu['domain_id'],
        'permission' => $menu['permission'],
        'is_active' => 1,
        'weight' => $menu['weight'],
      ]);
      CRM_Core_PseudoConstant::flush();
    }

    return TRUE;
  }

  /**
   * Moves Old Import Menus to be child of Import in Configure
   *
   * @param array $menus
   */
  private function up1035_moveImportMenus($menus) {
    $menu = civicrm_api3('Navigation', 'getsingle', [
      'name' => 'Import',
    ]);

    foreach ($menus as $menuName) {
      try {
        $menuToMove = civicrm_api3('Navigation', 'getsingle', ['name' => $menuName]);
        civicrm_api3('Navigation', 'create', [
          'id' => $menuToMove['id'],
          'parent_id' => $menu['id'],
          'is_active' => 1,
        ]);
      } catch (CiviCRM_API3_Exception $e) {
      }
    }
    return TRUE;
  }

  /**
   * Renames Import Contacts Menu to become Import Staff
   */
  private function up1035_renameImportContactsMenu() {
    civicrm_api3('Navigation', 'get', [
      'name' => 'Import Contacts',
      'api.Navigation.create' => [
        'id' => '$value.id',
        'label' => 'Import Staff',
        'name' => 'Import Staff',
      ],
    ]);
  }

  /**
   * Adjust the Import heading to be below Leave.
   */
  private function up1035_adjustImportMenuWeight() {
    $leaveWeight = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'leave_and_absences', 'weight', 'name');
    civicrm_api3('Navigation', 'get', [
      'name' => 'Import',
      'api.Navigation.create' => [
        'id' => '$value.id',
        'weight' => $leaveWeight,
      ],
    ]);
  }

}
