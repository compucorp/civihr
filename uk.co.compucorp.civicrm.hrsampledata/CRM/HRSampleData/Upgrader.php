<?php

/**
 * Collection of upgrade steps.
 */
class CRM_HRSampleData_Upgrader extends CRM_HRSampleData_Upgrader_Base {

  private $csvDir;

  public function __construct($extensionName, $extensionDir) {
    parent::__construct($extensionName, $extensionDir);

    $this->csvDir = $this->extensionDir . "/resources/csv";
  }

  public function install() {
    set_time_limit(0);

    $this->cleanTablesData();
    $this->importSampleData();
    $this->copyContactPhotos();
    $this->changeDefaultUsersAttachedContacts();
  }

  public function uninstall() {
    set_time_limit(0);
    
    $this->cleanTablesData();
  }

  /**
   * Removes data from Specific tables
   */
  private function cleanTablesData() {
    $this->cleanContactTables();

    $this->executeSqlFile('sql/uninstall.sql');
    $this->cleanCustomFieldsValues();
    $this->cleanSampleDataValues();
  }

  /**
   * Cleaning contacts table and other
   * related ones
   */
  private function cleanContactTables() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0");
    $contactsWithAttachedUsers = $this->getContactsWithAttachedUsers();
    $contactsWithAttachedUsers = implode(',', $contactsWithAttachedUsers);
    $contactsDeleteQuery = "DELETE FROM civicrm_contact WHERE id != 1 AND id NOT IN ({$contactsWithAttachedUsers})";
    CRM_Core_DAO::executeQuery($contactsDeleteQuery);
    $emailDeleteQuery = "DELETE FROM civicrm_email WHERE contact_id != 1 AND contact_id NOT IN ({$contactsWithAttachedUsers})";
    CRM_Core_DAO::executeQuery($emailDeleteQuery);
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1");
  }

  /**
   * Gets contacts IDs in case they are
   * associated (connected) with a CMS user
   *
   * @return array
   *   The list of contacts with associated users IDs
   */
  private function getContactsWithAttachedUsers() {
    $ufMatchRecords = civicrm_api3('UFMatch', 'get', [
      'sequential' => 1,
      'return' => ['contact_id'],
    ]);
    $contactIDs = [];
    if ($ufMatchRecords['count'] > 0) {
      $contactIDs = array_column($ufMatchRecords['values'], 'contact_id');
    }
    return $contactIDs;
  }

  /**
   * Custom field values data need to be cleaned
   * differently by getting all custom field tables
   * from custom groups table and then cleaning
   * each table one by one.
   */
  private function cleanCustomFieldsValues() {
    $customFieldTables = civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'table_name' => ['IS NOT NULL' => 1],
      'return' => ['table_name'],
      'options' => ['limit' => 0],
    ])['values'];

    foreach ($customFieldTables as $table) {
      CRM_Core_DAO::executeQuery("TRUNCATE TABLE {$table['table_name']}");
    }
  }

  /**
   * Data such as option values, pay scales .. etc
   * which are created by this extension are cleaned here.
   */
  private function cleanSampleDataValues() {
    $filesToClean = [
      'AbsenceType' => 'civicrm_hrabsence_type',
      'HRHoursLocation' => 'civicrm_hrhours_location',
      'HRPayScale' => 'civicrm_hrpay_scale',
      'LocationType' => 'civicrm_location_type',
      'OptionValue' => 'civicrm_option_value',
      'PhotoFilesCleaner' => 'civicrm_contact',
    ];

    foreach($filesToClean as $class => $file) {
      $fileToClean = new SplFileObject("{$this->csvDir}/{$file}.csv");
      $processor = new CRM_HRSampleData_CSVProcessor($fileToClean);

      $cleanerClassName = "CRM_HRSampleData_Cleaner_{$class}";
      $dataCleaner = new $cleanerClassName();

      $processor->process($dataCleaner);
    }
  }

  /**
   * Imports CiviHR sample data
   */
  private function importSampleData() {
    // These files will be imported in order
    $csvFiles  = [
      'OptionValue' => 'civicrm_option_value',
      'LocationType' => 'civicrm_location_type',
      'Contact' => 'civicrm_contact',
      'ContactEmail' => 'civicrm_email',
      'ContactPhone' => 'civicrm_phone',
      'ContactAddress' => 'civicrm_address',
      'Relationships' => 'civicrm_relationship',
      'HRHoursLocation' => 'civicrm_hrhours_location',
      'HRPayScale' => 'civicrm_hrpay_scale',
      'AbsencePeriod' => 'civicrm_hrabsence_period',
      'AbsenceType' => 'civicrm_hrabsence_type',
      'Vacancy' => 'civicrm_hrvacancy',
      'VacancyStage' => 'civicrm_hrvacancy_stage',
      'JobContract' => 'civicrm_hrjobcontract',
      'JobRoles' => 'civicrm_hrjobroles',
      'Activity' => 'civicrm_activity',
      'BankDetails' => 'civicrm_value_bank_details',
      'EmergencyContacts' => 'civicrm_value_emergency_contacts',
      'ExtendedDemographics' => 'civicrm_value_extended_demographics',
    ];

    foreach($csvFiles as $class => $file) {
      $fileToImport = new SplFileObject("{$this->csvDir}/{$file}.csv");
      $processor = new CRM_HRSampleData_CSVProcessor($fileToImport);

      $importerClassName = "CRM_HRSampleData_Importer_{$class}";
      $importer = new $importerClassName();

      $processor->process($importer);
    }
  }

  /**
   * Copies photos to the public CiviCRM directory
   */
  private function copyContactPhotos() {
    $imgDir = $this->extensionDir . "/resources/photos/";

    $config = CRM_Core_Config::singleton();
    $uploadDir= $config->customFileUploadDir;

    $copier = new CRM_HRSampleData_FileCopier();
    $copier->recurseCopy($imgDir, $uploadDir);
  }

  /**
   * Changes Default CiviHR Users ( civihr_admin , civihr_manager and civihr_staff) attached
   * contacts to the new sample ones.
   */
  private function changeDefaultUsersAttachedContacts() {
    $usersToNewContacts = [
      'civihr_admin@compucorp.co.uk' => 'jake@sccs.org',
      'civihr_manager@compucorp.co.uk' => 'adam@sccs.org',
      'civihr_staff@compucorp.co.uk' => 'zoe@sccs.org',
    ];

    foreach($usersToNewContacts as $originalEmail => $newContactEmail) {
      $userToChangeID = $this->getCMSUserIDByEmail($originalEmail);
      if ($userToChangeID) {
        $this->updateCMSUserEmail($userToChangeID, $newContactEmail);
        $this->updateMappedContactID($userToChangeID, $newContactEmail);
      }
    }
  }

  /**
   * Get CMS user ID by email.
   * (Currently Supports Drupal 7 CMS Only)
   *
   * @param string $email
   *
   * @return int|boolean
   *   CMS User ID or false if no user found
   */
  private function getCMSUserIDByEmail($email) {
    $userObject = user_load_by_mail($email);
    if ($userObject) {
      return $userObject->uid;
    }

    return false;
  }

  /**
   * Updates user email on the CMS side.
   * (Currently Supports Drupal 7 CMS Only)
   *
   * @param int $cmsID
   * @param string $newEmail
   */
  private function updateCMSUserEmail($cmsID, $newEmail) {
    $existingUser = user_load($cmsID);

    // save new user email
    user_save($existingUser, ['mail' => $newEmail]);
  }

  /**
   * Updates the mapped contact ID in uf_match table
   * given the CMS user ID and the new contact email
   *
   * @param $cmsID
   * @param $newEmail
   */
  private function updateMappedContactID($cmsID, $newEmail) {
    $newEmailData = civicrm_api3('Email', 'get', [
      'sequential' => 1,
      'return' => ['contact_id'],
      'email' => $newEmail,
      'options' => ['limit' => 1, 'sort' => 'id DESC'],
    ]);
    if ($newEmailData['count']) {
      $newContactID = $newEmailData['values'][0]['contact_id'];
      civicrm_api3('UFMatch', 'get', [
        'uf_id' => $cmsID,
        'api.UFMatch.create' => ['id' => '$value.id', 'contact_id' => $newContactID],
      ]);
    }
  }
}
