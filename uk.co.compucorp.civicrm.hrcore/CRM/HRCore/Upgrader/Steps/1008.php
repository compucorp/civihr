<?php

/**
 * Trait CRM_HRCore_Upgrader_Steps_1008
 */
trait CRM_HRCore_Upgrader_Steps_1008 {

  /**
   * This upgrader makes changes necessary to display custom
   * activity types on the activities tab of the contact summary page.
   *
   * Basically, It updates the filter column of some activity types to zero
   * so that they can show up on the add activity links and also updates the
   * label of the Print PDF Letter activity option value from Print/Merge Document
   * to Print PDF letter.
   */
  public function upgrade_1008() {
    $toSetFilterToZero = ['Inbound Email', 'Reminder Sent'];
    $printPdfActivity = ['Print PDF Letter'];
    $allActivityTypes = $this->getActivityTypes(array_merge($toSetFilterToZero, $printPdfActivity));

    foreach($toSetFilterToZero as $activityType) {
      if(isset($allActivityTypes[$activityType])) {
        $this->up1008_setFilterColumnToZero($allActivityTypes[$activityType]);
      }
    }

    if(isset($allActivityTypes[$printPdfActivity[0]])){
      $this->up1008_updateActivityTypeLabel($allActivityTypes[$printPdfActivity[0]], 'Print PDF Letter');
    }

    return TRUE;
  }

  /**
   * Sets the filter column on the activity type option value
   * to zero. We need to do this because civi will not show
   * add activity links for activity types whose filter column is
   * not null or have a value of zero by default.
   *
   * @param array $activityType
   */
  private function up1008_setFilterColumnToZero($activityType) {
    if($activityType['filter'] == 0){
      return;
    }

    civicrm_api3('OptionValue', 'create', [
      'id' => $activityType['id'],
      'filter' => 0,
    ]);
  }

  /**
   * Updates the activity type option value's label.
   *
   * @param array $activityType
   * @param string $newLabel
   */
  private function up1008_updateActivityTypeLabel($activityType, $newLabel) {
    if($activityType['label'] == $newLabel) {
      return;
    }

    civicrm_api3('OptionValue', 'create', [
      'id' => $activityType['label'],
      'label' => $newLabel,
    ]);
  }

  /**
   * Gets the activity types with the given names from the
   * db. Returns in an array of the activity type indexed by the name.
   *
   * @param array $activityTypeNames
   *
   * @return array
   */
  private function getActivityTypes($activityTypeNames) {
    $result = civicrm_api3('OptionValue', 'get',[
      'option_group_id' => 'activity_type',
      'name' => ['IN' => $activityTypeNames],
    ]);

    $activityTypes = [];

    foreach($result['values'] as $activity) {
      $activityTypes[$activity['name']] = $activity;
    }

    return $activityTypes;
  }
}
