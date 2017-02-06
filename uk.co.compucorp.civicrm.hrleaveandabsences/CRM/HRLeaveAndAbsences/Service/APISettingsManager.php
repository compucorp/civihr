<?php

use CRM_HRLeaveAndAbsences_Service_SettingsManager as SettingsManager;

class CRM_HRLeaveAndAbsences_Service_APISettingsManager implements SettingsManager {

  public function get($setting) {
    $result = civicrm_api3('Setting', 'get', [
      'sequential' => 1,
      'return' => [$setting],
    ]);

    if(!empty($result['values'][0][$setting])) {
      return $result['values'][0][$setting];
    }

    return null;
  }

  public function set($setting, $value) {
    civicrm_api3('Setting', 'create', [
      'sequential' => 1,
      $setting => $value
    ]);
  }
}
