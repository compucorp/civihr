<?php

/**
 * Collection of upgrade steps.
 */
class CRM_HRCore_Upgrader extends CRM_HRCore_Upgrader_Base {

  use CRM_HRCore_Upgrader_Steps_1000;
  use CRM_HRCore_Upgrader_Steps_1001;
  use CRM_HRCore_Upgrader_Steps_1002;
  use CRM_HRCore_Upgrader_Steps_1003;

  /**
   * @var array
   *   List of jobs in CiviCRM and their intended default status in the form [job_name => is_active]
   */
  private $scheduledJobsDefaultStatus = [
    // Enabled Jobs
    'CiviCRM Update Check' => 1,
    'Clean-up Temporary Data and Files' => 1,
    'Disable expired relationships' => 1,
    'Mail Reports' => 1,
    'Process Inbound Emails' => 1,
    'Send Scheduled Mailings' => 1,
    'Send Scheduled Reminders' => 1,

    // Disabled Jobs
    'Fetch Bounces' => 0,
    'Geocode and Parse Addresses' => 0,
    'Process Pledges' => 0,
    'Process Survey Respondents' => 0,
    'Rebuild Smart Group Cache' => 0,
    'Send Scheduled SMS' => 0,
    'Update Membership Statuses' => 0,
    'Update Participant Statuses' => 0,
    'Validate Email Address from Mailings.' => 0
  ];

  public function install() {
    $this->setScheduledJobsDefaultStatus();
    $this->deleteLocationTypes();
    $this->runAllUpgraders();
  }

  /**
   * Sets default status for scheduled jobs in CiviCRM Core.
   */
  private function setScheduledJobsDefaultStatus() {
    foreach ($this->scheduledJobsDefaultStatus as $job => $isEnabled) {
      civicrm_api3('Job', 'get', [
        'sequential' => 1,
        'name' => $job,
        'api.Job.create' => ['id' => "\$value.id", 'is_active' => $isEnabled]
      ]);
    }
  }

  private function deleteLocationTypes() {
    $locationsToDelete = [
      'Main',
      'Other'
    ];

    civicrm_api3('LocationType', 'get', [
      'name' => ['IN' => $locationsToDelete],
      'api.LocationType.delete' => ['id' => "\$value.id"]
    ]);
  }

  /**
   * Runs all the upgrader methods when installing the extension
   */
  private function runAllUpgraders() {
    $revisions = $this->getRevisions();

    foreach ($revisions as $revision) {
      $methodName = 'upgrade_' . $revision;

      if (is_callable([$this, $methodName])) {
        $this->{$methodName}();
      }
    }
  }

}
