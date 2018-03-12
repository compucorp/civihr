<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1020 {

  /**
   * Adds links to edit option groups related to leave and absence
   *
   * @return bool
   */
  public function upgrade_1020() {
    $permission = 'administer leave and absences';
    $params = ['return' => 'id', 'name' => 'leave_and_absences'];
    $parentId = (int) civicrm_api3('Navigation', 'getvalue', $params);
    civicrm_api3('Navigation', 'create', ['id' => $parentId, 'weight' => -96]);

    $optionGroupLinks = [
      'Sickness Reasons' => 'hrleaveandabsences_sickness_reason',
      'TOIL to be Accrued' => 'hrleaveandabsences_toil_amounts',
      'Work Pattern Change Reasons' => 'hrleaveandabsences_work_pattern_change_reason',
      'Work Pattern Day Equivalents' => 'hrleaveandabsences_leave_days_amounts'
    ];

    foreach ($optionGroupLinks as $itemName => $optionGroup) {
      $link = 'civicrm/admin/options/' . $optionGroup . '?reset=1';
      $params = ['url' => $link];
      $this->up1020_createNavItem($itemName, $permission, $parentId, $params);
    }

    return TRUE;
  }

  /**
   * Creates a navigation menu item using the API
   *
   * @param string $name
   * @param string $permission
   * @param int $parentID
   * @param array $params
   *
   * @return array
   */
  private function up1020_createNavItem(
    $name,
    $permission,
    $parentID,
    $params = []
  ) {
    $params = array_merge([
      'name' => $name,
      'label' => ts($name),
      'permission' => $permission,
      'parent_id' => $parentID,
      'is_active' => 1,
    ], $params);

    return civicrm_api3('navigation', 'create', $params);
  }
}
