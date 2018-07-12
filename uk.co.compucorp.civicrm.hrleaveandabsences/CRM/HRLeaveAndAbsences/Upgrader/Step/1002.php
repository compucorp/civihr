<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1002 {

  /**
   * Creates the 'other' sickness reason option value.
   *
   * @return bool
   */
  public function upgrade_1002() {
    $result = civicrm_api3('OptionValue', 'getcount', [
      'option_group_id' => 'hrleaveandabsences_sickness_reason',
      'name' => 'other',
    ]);

    if ($result == 0) {
      civicrm_api3('OptionValue', 'create', [
        'option_group_id' => 'hrleaveandabsences_sickness_reason',
        'name' => 'other',
        'label' => 'Other - Please leave a comment',
        'value' => 12,
        'weight' => 11,
        'is_reserved' => 1,
        'is_default' => 0,
        'is_active' => 1
      ]);
    }

    return true;
  }
}
