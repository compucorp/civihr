<?php

trait CRM_HRCore_Upgrader_Steps_1004 {

  /**
   * @var bool
   */
  protected $tasksEnabled = FALSE;

  /**
   * Add new activity types
   */
  public function upgrade_1004() {
    $key = 'uk.co.compucorp.civicrm.tasksassignments';
    $this->tasksEnabled = $this->isExtensionEnabled($key);

    $newActivityTypes = [
      'Send Onboarding Email',
      'Create User Account'
    ];

    $existingNames = $this->getExistingNames($newActivityTypes);
    $newActivityTypes = array_diff($newActivityTypes, $existingNames);

    foreach ($newActivityTypes as $activityType) {
      $this->createActivityType($activityType);
    }

    return TRUE;
  }

  /**
   * @param $newActivityTypes
   *
   * @return array
   */
  protected function getExistingNames($newActivityTypes) {
    $existing = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'activity_type',
      'name' => ['IN' => $newActivityTypes],
    ]);
    $existing = CRM_Utils_Array::value('values', $existing, []);

    return array_column($existing, 'name');
  }

  /**
   * @param $activityType
   */
  protected function createActivityType($activityType) {
    $params = [
      'option_group_id' => 'activity_type',
      'name' => $activityType,
    ];

    if ($this->tasksEnabled) {
      $params['component_id'] = 'CiviTask';
    }

    civicrm_api3('OptionValue', 'create', $params);
  }

  /**
   * Checks if tasks and assignments extension is installed or enabled
   *
   * @param string $key
   *   Extension unique key
   *
   * @return bool
   */
  private function isExtensionEnabled($key) {
    $isEnabled = CRM_Core_DAO::getFieldValue(
      'CRM_Core_DAO_Extension',
      $key,
      'is_active',
      'full_name'
    );
    return !empty($isEnabled) ? TRUE : FALSE;
  }

}
