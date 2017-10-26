<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1008 {

  /**
   * Upgrader to set icon for top-level 'Leave' menu item
   *
   * @return bool
   */
  public function upgrade_4706() {
    $params = [
      'name' => 'leave_and_absences_dashboard',
      'api.Navigation.create' => ['id' => '$value.id', 'icon' => 'fa fa-briefcase'],
      'parent_id' => ['IS NULL' => true],
    ];
    civicrm_api3('Navigation', 'get', $params);

    return TRUE;
  }
}
