<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1006 {

  /**
   * Renames the two "Leave and Absences" menu items to just "Absences"
   *
   * @return bool
   */
  public function upgrade_1006() {
    $default = [];

    $vacanciesParams = ['name' => 'Vacancies', 'url' => null];
    $vacanciesMenuItem = CRM_Core_BAO_Navigation::retrieve($vacanciesParams, $default);

    $leaveParams = ['name' => 'leave_and_absences_dashboard', 'url' => 'civicrm/leaveandabsences/dashboard'];
    $leaveMenuItem = CRM_Core_BAO_Navigation::retrieve($leaveParams, $default);
    $leaveMenuItem->weight = $vacanciesMenuItem->weight - 1;
    $leaveMenuItem->save();

    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }
}
