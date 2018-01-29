<?php

trait CRM_HRCore_Upgrader_Steps_1005 {

  /**
   * This is the desired state of scheduled jobs at this time. Jobs not on this
   * list will be disabled.
   *
   * @var array
   */
  protected $jobStates = [
    'CiviCRM Update Check' => [
      'run_frequency' => 'Daily',
      'is_active' => TRUE
    ],
    'Clean-up Temporary Data and Files' => [
      'run_frequency' => 'Hourly',
      'is_active' => FALSE
    ],
    'Clone Documents' => [
      'run_frequency' => 'Daily',
      'is_active' => TRUE
    ],
    'Create expiry records for expired LeaveBalanceChanges' => [
      'run_frequency' => 'Daily',
      'is_active' => TRUE
    ],
    'Disable expired relationships' => [
      'run_frequency' => 'Daily',
      'is_active' => FALSE
    ],
    'Fetch Bounces' => [
      'run_frequency' => 'Hourly',
      'is_active' => FALSE
    ],
    'Geocode and Parse Addresses' => [
      'run_frequency' => 'Daily',
      'is_active' => FALSE
    ],
    'Length of service updater' => [
      'run_frequency' => 'Daily',
      'is_active' => TRUE
    ],
    'Mail Reports' => [
      'run_frequency' => 'Daily',
      'is_active' => FALSE
    ],
    'Process Inbound Emails' => [
      'run_frequency' => 'Daily',
      'is_active' => FALSE
    ],
    'Process Pledges' => [
      'run_frequency' => 'Daily',
      'is_active' => FALSE
    ],
    'Process Public Holiday Leave Requests Updates' => [
      'run_frequency' => 'Always',
      'is_active' => TRUE
    ],
    'Process Survey Respondents' => [
      'run_frequency' => 'Always',
      'is_active' => FALSE
    ],
    'Rebuild Smart Group Cache' => [
      'run_frequency' => 'Always',
      'is_active' => FALSE
    ],
    'Send Scheduled Mailings' => [
      'run_frequency' => 'Always',
      'is_active' => FALSE
    ],
    'Send Scheduled Reminders' => [
      'run_frequency' => 'Daily',
      'is_active' => TRUE
    ],
    'Send Scheduled SMS' => [
      'run_frequency' => 'Always',
      'is_active' => FALSE
    ],
    'Tasks and Assignments Daily Reminder' => [
      'run_frequency' => 'Daily',
      'is_active' => TRUE
    ],
    'Tasks and Assignments Documents Notification' => [
      'run_frequency' => 'Daily',
      'is_active' => TRUE
    ],
    'Update Greetings and Addressees' => [
      'run_frequency' => 'Daily',
      'is_active' => FALSE
    ],
    'Update Membership Statuses' => [
      'run_frequency' => 'Daily',
      'is_active' => FALSE
    ],
    'Update Participant Statuses' => [
      'run_frequency' => 'Always',
      'is_active' => FALSE
    ],
    'Validate Email Address from Mailings.' => [
      'run_frequency' => 'Daily',
      'is_active' => FALSE
    ],
  ];

  /**
   * Ensure all scheduled job are in the expected state
   * @see https://compucorp.atlassian.net/wiki/x/owD_BQ
   *
   * @return TRUE
   */
  public function upgrade_1005() {
    $allExistingJobs = $this->up1005_getExistingJobs();

    foreach ($allExistingJobs as $existingJob) {
      $name = $existingJob['name'];
      $inWhitelist = isset($this->jobStates[$name]);

      if ($inWhitelist) {
        $desiredFrequency = $this->jobStates[$name]['run_frequency'];
        $shouldBeActive = $this->jobStates[$name]['is_active'];
      }
      else {
        $desiredFrequency = $existingJob['run_frequency']; // no change
        $shouldBeActive = FALSE;
      }

      $wasActive = (bool) $existingJob['is_active'];
      $oldFrequency = $existingJob['run_frequency'];
      $statusChanged = $wasActive !== $shouldBeActive;
      $frequencyChanged = $oldFrequency !== $desiredFrequency;
      $isChanged = $statusChanged || $frequencyChanged;

      if (!$isChanged) {
        continue;
      }

      $params = [
        'id' => $existingJob['id'],
        'is_active' => $shouldBeActive,
        'run_frequency' => $desiredFrequency,
      ];

      civicrm_api3('Job', 'create', $params);
    }

    return TRUE;
  }

  /**
   * Gets the existing jobs for the first domain in the database.
   *
   * @return array
   */
  private function up1005_getExistingJobs() {
    $domains = civicrm_api3('Domain', 'get')['values'];
    $domain = array_shift($domains);
    $params = ['domain_id' => $domain['id']];

    return civicrm_api3('Job', 'get', $params)['values'];
  }

}
