<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1020 {

  /**
   * Adds links to edit option groups related to leave and absence
   *
   * @return bool
   */
  public function upgrade_1021() {
    $params = ['return' => 'id', 'name' => 'leave_and_absences'];
    $parentId = (int) civicrm_api3('Navigation', 'getvalue', $params);
    civicrm_api3('Navigation', 'create', ['id' => $parentId, 'weight' => -96]);

    $this->createNewLinks($parentId);
    $this->relabelExistingLinks($parentId);
    $this->addSeparators($parentId);
    $this->updateWeight($parentId);

    return TRUE;
  }

  /**
   * Creates new entries in the leave submenu
   *
   * @param int $parentId
   */
  private function createNewLinks($parentId) {
    $permission = 'administer leave and absences';
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
  }

  /**
   * Relabels some of the existing leave items
   *
   * @param int $parentId
   */
  private function relabelExistingLinks($parentId) {
    $nameToLabelMapping = [
      'leave_and_absence_types' => ts('Leave Types'),
      'leave_and_absence_periods' => ts('Leave Periods'),
      'leave_and_absence_manage_work_patterns' => ts('Work Patterns'),
      'leave_and_absence_general_settings' => ts('Leave Settings'),
      'leave_and_absences_import' => ts('Import Leave Requests'),
    ];

    foreach ($nameToLabelMapping as $name => $label) {
      $params = ['parent_id' => $parentId, 'name' => $name, 'return' => 'id'];
      $id = (int) civicrm_api3('Navigation', 'getvalue', $params);
      civicrm_api3('Navigation', 'create', ['id' => $id, 'label' => $label]);
    }
  }

  /**
   * Adds separators after certain items in the leave submenu
   *
   * @param int $parentId
   */
  private function addSeparators($parentId) {
    $itemsWithSeparators = [
      'leave_and_absence_general_settings',
      'Work Pattern Day Equivalents',
    ];

    CRM_Core_PseudoConstant::flush();

    foreach ($itemsWithSeparators as $name) {
      $params = ['parent_id' => $parentId, 'name' => $name, 'return' => 'id'];
      $id = (int) civicrm_api3('Navigation', 'getvalue', $params);
      civicrm_api3('Navigation', 'create', ['id' => $id, 'has_separator' => 1]);
    }
  }

  /**
   * Sets the weight for the "Import" item so it will appear last
   *
   * @param int $parentId
   */
  private function updateWeight($parentId) {
    $name = 'leave_and_absences_import';
    $params = ['parent_id' => $parentId, 'name' => $name, 'return' => 'id'];
    $id = (int) civicrm_api3('Navigation', 'getvalue', $params);
    $maxWeight = CRM_Core_BAO_Navigation::calculateWeight($parentId);
    civicrm_api3('Navigation', 'create', ['id' => $id, 'weight' => $maxWeight]);
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

    $existing = civicrm_api3('Navigation', 'get', $params);

    if ($existing['count'] > 0) {
      return array_shift($existing['values']);
    }

    return civicrm_api3('Navigation', 'create', $params);
  }
}
