<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1021 {
  
  /**
   * Create new option group hrleaveandabsences_work_pattern_change_reason
   * with values As per contract, Change in contractual hours and
   * Change in contract type.
   *
   * @return bool
   */
  public function upgrade_1021() {
    $groupName = 'hrleaveandabsences_work_pattern_change_reason';
    $result = civicrm_api3('OptionGroup', 'get', [
      'name' => $groupName
    ]);
    if ($result['count'] > 0) {
      return TRUE;
    }
    
    $groupValues = [
      'As per contract',
      'Change in contractual hours',
      'Change in contract type'
    ];
    $optionParams = [
      'name' => $groupName,
      'title' => 'Leave and Absence Work Pattern Change Reason',
      'is_active' => 1
    ];
    civicrm_api3('OptionGroup', 'create', $optionParams);
    foreach ($groupValues as $value) {
      $opValueParams = [
        'option_group_id' => $groupName,
        'name' => $value,
        'label' => $value,
      ];
      civicrm_api3('OptionValue', 'create', $opValueParams);
    }
    return TRUE;
  }
}
