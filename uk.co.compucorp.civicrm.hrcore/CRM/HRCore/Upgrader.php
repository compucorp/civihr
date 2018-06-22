<?php

use CRM_HRCore_Helper_ExtensionHelper as ExtensionHelper;

/**
 * Collection of upgrade steps.
 */
class CRM_HRCore_Upgrader extends CRM_HRCore_Upgrader_Base {

  use CRM_HRCore_Upgrader_Steps_1000;
  use CRM_HRCore_Upgrader_Steps_1001;
  use CRM_HRCore_Upgrader_Steps_1002;
  use CRM_HRCore_Upgrader_Steps_1003;
  use CRM_HRCore_Upgrader_Steps_1004;
  use CRM_HRCore_Upgrader_Steps_1005;
  use CRM_HRCore_Upgrader_Steps_1006;
  use CRM_HRCore_Upgrader_Steps_1007;
  use CRM_HRCore_Upgrader_Steps_1008;
  use CRM_HRCore_Upgrader_Steps_1009;
  use CRM_HRCore_Upgrader_Steps_1010;
  use CRM_HRCore_Upgrader_Steps_1011;
  use CRM_HRCore_Upgrader_Steps_1012;
  use CRM_HRCore_Upgrader_Steps_1013;
  use CRM_HRCore_Upgrader_Steps_1014;
  use CRM_HRCore_Upgrader_Steps_1015;
  use CRM_HRCore_Upgrader_Steps_1016;
  use CRM_HRCore_Upgrader_Steps_1017;
  use CRM_HRCore_Upgrader_Steps_1018;
  use CRM_HRCore_Upgrader_Steps_1019;
  use CRM_HRCore_Upgrader_Steps_1020;
  use CRM_HRCore_Upgrader_Steps_1021;
  use CRM_HRCore_Upgrader_Steps_1022;
  use CRM_HRCore_Upgrader_Steps_1023;
  use CRM_HRCore_Upgrader_Steps_1024;


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

  /**
   * Callback called when the extension is installed
   */
  public function install() {
    $this->setScheduledJobsDefaultStatus();
    $this->deleteLocationTypes();
    $this->createRequiredLocationTypes();
    $this->deleteUnneededCustomGroups();
    $this->createDefaultRelationshipTypes();
    $this->makeAllCurrenciesAvailable();
    $this->runAllUpgraders();
  }

  /**
   * Callback method called when the extension is uninstalled.
   *
   * It should cleanup anything created by the extension installation and
   * upgraders. Note that things like Option Values and Option Groups might
   * be in use by other extensions and removing them might result in rendering
   * those extensions useless, so we should only remove things that are safe to
   * delete.
   */
  public function uninstall() {
    $this->removeDefaultRelationshipTypes();
  }

  /**
   * Callback method called when the extension is disabled
   */
  public function disable() {
    $this->toggleRelationshipTypes(0);
  }

  /**
   * Callback method called when the extension is enabled
   */
  public function enable() {
    $this->toggleRelationshipTypes(1);
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
    }
    catch (Exception $e) {}
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
   * Creates location types required by CiviHR and makes them reserved
   */
  private function createRequiredLocationTypes() {
    $locationTypesToCreate = [
      'Personal',
    ];

    foreach ($locationTypesToCreate as $locationTypeName) {
      $existing = civicrm_api3('LocationType', 'get', [
        'name' => $locationTypeName
      ]);

      if ($existing['count'] > 0) {
        continue;
      }

      civicrm_api3('LocationType', 'create', [
        'name' => $locationTypeName,
        'display_name' => $locationTypeName,
        'vcard_name' => strtoupper($locationTypeName),
        'is_reserved' => 1,
        'is_active' => 1
      ]);
    }
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

  /**
   * Create all the default relationship types
   */
  private function createDefaultRelationshipTypes() {
    foreach ($this->defaultRelationshipsTypes() as $relationshipType) {
      civicrm_api3('RelationshipType', 'create', [
        'name_a_b' => $relationshipType['name_a_b'],
        'label_a_b' => $relationshipType['name_b_a'],
        'name_b_a' => $relationshipType['name_b_a'],
        'label_b_a' => $relationshipType['name_b_a'],
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
        'is_reserved' => 0,
        'is_active' => 1,
      ]);
    }
  }

  /**
   * Making All Currencies Available for new installations
   */
  private function makeAllCurrenciesAvailable() {
    $result = civicrm_api3('OptionValue', 'get', [
      'return' => ['name'],
      'option_group_id' => 'currencies_enabled',
    ]);
    $enabledCurrencies = array_column($result['values'], 'name');

    $dao = CRM_Core_DAO::executeQuery('SELECT * from civicrm_currency');
    while ($dao->fetch()) {
      if (!in_array($dao->name, $enabledCurrencies)) {
        civicrm_api3('OptionValue', 'create', [
          'option_group_id' => 'currencies_enabled',
          'label' => $dao->name . ' (' . $dao->symbol . ')',
          'value' => $dao->name,
          'name' => $dao->name . ' (' . $dao->symbol . ')',
        ]);
      }
    }
  }

  /**
   * Removes default relationship types
   */
  private function removeDefaultRelationshipTypes() {
    foreach ($this->defaultRelationshipsTypes() as $relationshipType) {
      // chained API call to delete the relationship type
      civicrm_api3('RelationshipType', 'get', [
        'name_b_a' => $relationshipType['name_b_a'],
        'api.RelationshipType.delete' => ['id' => '$value.id'],
      ]);
    }
  }

  /**
   * Enables/Disables a defined list of relationship types
   *
   * @param int $setActive
   *   0: disable , 1: enable
   */
  public function toggleRelationshipTypes($setActive) {
    foreach ($this->defaultRelationshipsTypes() as $relationshipType) {
      // chained API call to activate/disable the relationship type
      civicrm_api3('RelationshipType', 'get', [
        'name_b_a' => $relationshipType['name_b_a'],
        'api.RelationshipType.create' => [
          'id' => '$value.id',
          'name_a_b' => '$value.name_a_b',
          'name_b_a' => '$value.name_b_a',
          'is_active' => $setActive
        ],
      ]);
    }
  }

  /**
   * A list of relationship types to be managed by this extension.
   *
   * @return array
   */
  public function defaultRelationshipsTypes() {
    $list = [
      ['name_a_b' => 'HR Manager is', 'name_b_a' => 'HR Manager', 'description' => 'HR Manager'],
      ['name_a_b' => 'Line Manager is', 'name_b_a' => 'Line Manager', 'description' => 'Line Manager'],
    ];

    // (Recruiting Manager) should be included only if hrrecruitment extension is disabled.
    if (!ExtensionHelper::isExtensionEnabled('org.civicrm.hrrecruitment')) {
      $list[] = [
        'name_a_b' => 'Recruiting Manager is',
        'name_b_a' => 'Recruiting Manager',
        'description' => 'Recruiting Manager'
      ];
    }

    return $list;
  }

}
