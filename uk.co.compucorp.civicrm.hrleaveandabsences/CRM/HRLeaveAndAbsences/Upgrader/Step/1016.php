<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1016 {
  /**
   * Updates the URL for the Leave Balances menu item.
   *
   * @return bool
   */
  public function upgrade_1016() {
    $leaveBalances = $this->up1016_getLeaveBalancesMenuItem();
    $leaveBalances->url = 'civicrm/leaveandabsences/dashboard#/leave-balances';
    $leaveBalances->save();
    CRM_Core_BAO_Navigation::resetNavigation();

    return true;
  }

  /**
   * Returns the the navigation menu item for Leave Balances.
   *
   * @return CRM_Core_BAO_Navigation
   */
  private function up1016_getLeaveBalancesMenuItem() {
    $default = [];
    $menuItemQueryParams = ['name' => 'leave_and_absences_leave_balances'];

    return CRM_Core_BAO_Navigation::retrieve($menuItemQueryParams, $default);
  }
}
