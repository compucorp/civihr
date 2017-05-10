<?php

trait CRM_HRCore_Upgrader_Steps_1004 {
  /**
   * Add new activity types
   */
  public function upgrade_1004() {
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
      'component_id' => 'CiviTask',
    ]);
    $existing = CRM_Utils_Array::value('values', $existing, []);

    return array_column($existing, 'name');
  }

  /**
   * @param $activityType
   */
  protected function createActivityType($activityType) {
    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => 'activity_type',
      'name' => $activityType,
      'component_id' => 'CiviTask',
    ]);
  }

}
