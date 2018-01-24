<?php

trait CRM_HRCore_Upgrader_Steps_1007 {

  /**
   * Creates the "Reports" item in the Navigation Menu
   *
   * @return bool
   */
  public function upgrade_1007() {
    if(!empty($this->up1007_getExistingReportsMenu())) {
      return TRUE;
    }

    $this->up1007_createReportsMenuItem();

    return TRUE;
  }

  /**
   * Returns the Reports menu item, if it already exists
   *
   * @return array
   */
  private function up1007_getExistingReportsMenu() {
    $result = civicrm_api3('Navigation', 'get', [
      'name' => 'hr-reports'
    ]);

    return $result['values'];
  }

  /**
   * Returns the weight of the Administer menu item
   *
   * @return int
   */
  private function up1007_getAdministerMenuItemWeight() {
    $result = civicrm_api3('Navigation', 'get', [
      'name' => 'Administer'
    ])['values'];

    $menuItem = reset($result);

    return $menuItem['weight'];
  }

  /**
   * Creates the Reports menu item and places it before the "Configure"
   * (administer) item
   */
  private function up1007_createReportsMenuItem() {
    $params = [
      'name' => 'hr-reports',
      'label' => ts('Reports'),
      'url' => 'civicrm/reports',
      'is_active' => TRUE,
      'permission' => 'access hrreports',
      'icon' => 'fa fa-table',
    ];

    $result = civicrm_api3('Navigation', 'create', $params);
    $reportsMenuItem = reset($result['values']);

    // We want to place the item as the last one on the list right before
    // the "Configure" (administer) item
    $recruitmentWeight = $this->up1007_getAdministerMenuItemWeight();
    $reportsMenuItem['weight'] = $recruitmentWeight - 1;
    civicrm_api3('Navigation', 'create', $reportsMenuItem);

    CRM_Core_BAO_Navigation::resetNavigation();
  }
}
