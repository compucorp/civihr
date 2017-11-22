<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1006 {

  /**
   * Moves the "leave_and_absences_dashboard" menu item either after "Tasks"
   *  or after "Contacts", as a fallback in case T&A is not enabled
   *
   * @return bool
   */
  public function upgrade_1006() {
    $default = [];

    if ($this->up1006_isExtensionEnabled('uk.co.compucorp.civicrm.tasksassignments')) {
      $prevParams = ['name' => 'tasksassignments'];
    } else {
      $prevParams = ['name' => 'Contacts'];
    }

    $prevMenuItem = CRM_Core_BAO_Navigation::retrieve($prevParams, $default);

    $leaveParams = ['name' => 'leave_and_absences_dashboard', 'url' => null];
    $leaveMenuItem = CRM_Core_BAO_Navigation::retrieve($leaveParams, $default);
    $leaveMenuItem->weight = $prevMenuItem->weight + 1;
    $leaveMenuItem->save();

    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }

  private function up1006_isExtensionEnabled($extension) {
    return CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', $extension, 'is_active', 'full_name');
  }
}
