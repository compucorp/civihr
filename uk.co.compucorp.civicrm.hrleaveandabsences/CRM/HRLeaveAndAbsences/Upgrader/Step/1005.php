<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1005 {

  /**
   * Renames the two "Leave and Absences" menu items to just "Absences"
   *
   * @return bool
   */
  public function upgrade_1005() {
    $default = [];
    $paramsDashboard = ['name' => 'leave_and_absences_dashboard', 'url' => 'civicrm/leaveandabsences/dashboard'];
    $paramsAdmin = ['name' => 'leave_and_absences', 'url' => null];

    $menuItems = [
      'dashboard' => CRM_Core_BAO_Navigation::retrieve($paramsDashboard, $default),
      'admin' => CRM_Core_BAO_Navigation::retrieve($paramsAdmin, $default)
    ];
    
    foreach ($menuItems as $menuItem) {
      $menuItem->label = 'Absences';
      $menuItem->save();
    }

    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }
}
