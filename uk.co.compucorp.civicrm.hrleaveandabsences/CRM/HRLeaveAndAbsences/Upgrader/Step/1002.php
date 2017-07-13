<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1002 {

  /**
   * Creates the 'other' sickness reason option value.
   *
   * @return bool
   */
  public function upgrade_1002() {
    try {
      $result = civicrm_api3('OptionValue', 'get', [
        'sequential' => 1,
        'option_group_id' => 'hrleaveandabsences_sickness_reason',
        'name' => 'other',
      ]);

      if ($result['count'] == 0) {
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
    } catch(Exception $e) {
      // We run all the upgraders during the extension installation, but, during
      // this process, the hrleaveandabsences_sickness_reason option group
      // is still not available and the get API call will fail and throw an exception.
      // So, to avoid the installation process to stop, we simply catch the exception
      // and don't do anything. The option group and values will be created just
      // fine, based on the values set on xml/option_groups/sickness_reason_install.xml
    }

    return true;
  }
}
