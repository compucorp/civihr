<?php

trait CRM_HRCore_Upgrader_Steps_1005 {

  /**
   * @var bool
   */
  protected $tasksEnabled = FALSE;

  /**
   * Add new activity types
   */
  public function upgrade_1005() {
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
   * @param array $newActivityTypes
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
   * @param string $activityType
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
    $status = CRM_Extension_System::singleton()->getManager()->getStatus($key);

    return $status === CRM_Extension_Manager::STATUS_INSTALLED;
  }

}
