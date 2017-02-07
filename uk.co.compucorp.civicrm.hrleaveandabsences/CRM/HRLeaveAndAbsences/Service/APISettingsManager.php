<?php

use CRM_HRLeaveAndAbsences_Service_SettingsManager as SettingsManager;

/**
 * A CRM_HRLeaveAndAbsences_Service_SettingsManager implementation that uses the
 * CiviCRM Setting API to store and retrieve settings.
 */
class CRM_HRLeaveAndAbsences_Service_APISettingsManager implements SettingsManager {

  /**
   * {@inheritdoc}
   */
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

  /**
   * {@inheritdoc}
   */
  public function set($setting, $value) {
    civicrm_api3('Setting', 'create', [
      'sequential' => 1,
      $setting => $value
    ]);
  }
}
