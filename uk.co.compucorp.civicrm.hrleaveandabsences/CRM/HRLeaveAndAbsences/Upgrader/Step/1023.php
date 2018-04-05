<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1023 {
  
  /**
   * Deletes the 'zero_days' option value of the toil amounts
   * option group.
   *
   * @return bool
   */
  public function upgrade_1023() {
    $result = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'hrleaveandabsences_toil_amounts',
      'name' => 'zero_days',
    ]);

    if (empty($result['id'])) {
      return TRUE;
    }

    civicrm_api3('OptionValue', 'delete', [
      'id' => $result['id']
    ]);

    return TRUE;
  }
}
