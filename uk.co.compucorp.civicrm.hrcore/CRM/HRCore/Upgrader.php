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
   */
  private $scheduledJobsToBeEnabled = [
    'CiviCRM Update Check',
    'Clean-up Temporary Data and Files',
    'Disable expired relationships',
    'Mail Reports',
    'Process Inbound Emails',
    'Send Scheduled Mailings',
    'Send Scheduled Reminders'
  ];

  /**
   * @var array
   */
  private $scheduledJobsToBeDisabled = [
    'Fetch Bounces',
    'Geocode and Parse Addresses',
    'Process Pledges',
    'Process Survey Respondents',
    'Rebuild Smart Group Cache',
    'Send Scheduled SMS',
		'Update Greetings and Addressees',
    'Update Membership Statuses',
    'Update Participant Statuses',
    'Validate Email Address from Mailings.'
  ];

  public function install() {
    $this->setScheduledJobsDefaultStatus();
    $this->deleteLocationTypes();
    $this->deleteUnneededCustomGroups();
    $this->runAllUpgraders();
  }

  /**
   * Deletes custom fields for given custom groups and then deletes the custom
   * groups.
   */
  private function deleteUnneededCustomGroups() {
    $customGroups = ['Food_Preference', 'Donor_Information', 'constituent_information'];
    try {
      civicrm_api3('CustomField', 'get', [
        'custom_group_id' => ['IN' => $customGroups],
        'api.CustomField.delete' => ['id' => '$value.id']
      ]);

      civicrm_api3('CustomGroup', 'get', [
        'name' => ['IN' => $customGroups],
        'api.CustomGroup.delete' => ['id' => '$value.id']
      ]);
    } catch (Exception $e) {}
  }

  /**
   * Sets default status for scheduled jobs in CiviCRM Core.
   */
  private function setScheduledJobsDefaultStatus() {
    foreach ($this->scheduledJobsToBeEnabled as $job) {
      $this->setJobStatus($job, 1);
    }

    foreach ($this->scheduledJobsToBeDisabled as $job) {
      $this->setJobStatus($job, 0);
    }
  }

  /**
   * Sets given status to provided job.
   *
   * @param string $jobName
   *   Name of job for which status needs to be set.
   * @param int $isActive
   *   Status to be set, 1 if active, 0 otherwise.
   */
  private function setJobStatus($jobName, $isActive) {
    civicrm_api3('Job', 'get', [
      'sequential' => 1,
      'name' => $jobName,
      'api.Job.create' => ['id' => "\$value.id", 'is_active' => $isActive]
    ]);
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
