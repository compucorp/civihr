<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1008 {

  /**
   * Sets icon for top-level 'Leave' menu item
   *
   * @return bool
   */
  public function upgrade_4706() {
    $params = [
      'name' => 'leave_and_absences_dashboard',
      'api.Navigation.create' => ['id' => '$value.id', 'icon' => 'crm-i fa-briefcase'],
      'parent_id' => ['IS NULL' => true],
    ];
    civicrm_api3('Navigation', 'get', $params);

    return TRUE;
  }
}
