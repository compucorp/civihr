<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1001 {

  /**
   * Adds the Import Leave/Absence menu to the Administer/Leave and Absences
   * menu tree. But it firsts resolves a conflict in the Navigation name attribute
   * of the Leave and Absences Dashboard menu and the Administer/Leave And Absences
   * menu by renaming the former.
   *
   * @return bool
   */
  public function upgrade_1001(){
    $this->up1001_renameLeaveAndAbsenceDashboardNavigation();
    $this->up1001_addLeaveAndAbsenceDataImportMenu();

    return true;
  }

  /**
   * Renames the Leave and Absences Dashboard menu 'name' navigation attribute
   * since it conflicts with the Administer/Leave and Absences 'name' navigation
   * attribute.
   */
  private function up1001_renameLeaveAndAbsenceDashboardNavigation() {
    $params = ['name' => 'leave_and_absences', 'url' => 'civicrm/leaveandabsences/dashboard'];
    $default = [];
    $dashboardNavigation = CRM_Core_BAO_Navigation::retrieve($params, $default);
    if ($dashboardNavigation) {
      $dashboardNavigation->name = 'leave_and_absences_dashboard';
      $dashboardNavigation->save();
    }
  }

  /**
   * Adds the Import Leave/Absence menu to the Administer/Leave and Absences
   * menu tree.
   */
  private function up1001_addLeaveAndAbsenceDataImportMenu() {
    $leaveAndAbsenceNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'leave_and_absences', 'id', 'name');
    $weight = CRM_Core_BAO_Navigation::calculateWeight($leaveAndAbsenceNavId);

    $menuItem = [
      'label' => ts('Import Leave/Absence Requests'),
      'name' => 'leave_and_absences_import',
      'url' => 'civicrm/admin/leaveandabsences/import',
      'permission' => 'administer leave and absences',
      'parent_id' => $leaveAndAbsenceNavId,
      'weight' => $weight,
      'is_active' => 1
    ];

    CRM_Core_BAO_Navigation::add($menuItem);
    CRM_Core_BAO_Navigation::resetNavigation();
  }
}
