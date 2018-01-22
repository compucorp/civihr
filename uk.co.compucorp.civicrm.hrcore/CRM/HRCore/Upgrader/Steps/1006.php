<?php

trait CRM_HRCore_Upgrader_Steps_1006 {

  /**
   * Creates a scheduled job to send CiviHR pingback statistics.
   *
   * @return TRUE
   */
  public function upgrade_1006() {
    $description = 'Checks for CiviHR version updates. Also sends basic site '
      . 'info and basic usage statistics to civihr.org to assist in '
      . 'prioritising ongoing development efforts.';

    $params = [
      'run_frequency' => 'Daily',
      'name' => 'CiviHR Update Check',
      'api_entity' => 'Job',
      'api_action' => 'check_civihr_version',
      'description' => $description,
      'is_active' => TRUE,
    ];
    $existing = civicrm_api3('Job', 'get', $params);

    if ($existing['count'] != 0) {
      return TRUE;
    }

    civicrm_api3('Job', 'create', $params);

    return TRUE;
  }

}
