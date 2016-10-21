<?php

require_once 'civihrsampledata.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function civihrsampledata_civicrm_config(&$config) {
  _civihrsampledata_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function civihrsampledata_civicrm_xmlMenu(&$files) {
  _civihrsampledata_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function civihrsampledata_civicrm_install() {
  return _civihrsampledata_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_postInstall
 */
function civihrsampledata_civicrm_postInstall() {
  $extensionDirectory = CRM_Core_Resources::singleton()->getPath('uk.co.compucorp.civicrm.civihrsampledata');

  _civihrsampledata_copyContactPhotos($extensionDirectory);
  _civihrsampledata_importSampleData($extensionDirectory);
}

  /**
 * Implementation of hook_civicrm_uninstall
 */
function civihrsampledata_civicrm_uninstall() {
  return _civihrsampledata_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function civihrsampledata_civicrm_enable() {
  return _civihrsampledata_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function civihrsampledata_civicrm_disable() {
  return _civihrsampledata_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function civihrsampledata_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civihrsampledata_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function civihrsampledata_civicrm_managed(&$entities) {
  return _civihrsampledata_civix_civicrm_managed($entities);
}

/**
 * Copies photos to the public CiviCRM directory
 *
 * @param string $extensionDirectory
 */
function _civihrsampledata_copyContactPhotos($extensionDirectory) {
  $imgDir = $extensionDirectory . "/resources/photos/";
  $config = CRM_Core_Config::singleton();
  $uploadDir= $config->customFileUploadDir;

  $copier = new CRM_CiviHRSampleData_FileCopier();
  $copier->recurseCopy($imgDir, $uploadDir);
}
/**
 * Imports CiviHR sample data
 *
 * @param string $extensionDirectory
 */
function _civihrsampledata_importSampleData($extensionDirectory) {

  $csvDir = $extensionDirectory . "/resources/csv";

  // These files will be imported in order
  $csvFiles  = [
    'OptionValue' => 'civicrm_option_value',
    'LocationType' => 'civicrm_location_type',
    'Contact' => 'civicrm_contact',
    'ContactEmail' => 'civicrm_email',
    'ContactPhone' => 'civicrm_phone',
    'ContactAddress' => 'civicrm_address',
    'Case' => 'civicrm_case',
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
    'VacancyValue' => 'civicrm_value_vacancy',
  ];

  foreach($csvFiles as $class => $file) {
    $fileToImport = new SplFileObject("{$csvDir}/{$file}.csv");

    $importerClassName = "CRM_CiviHRSampleData_Importer_{$class}";
    $importer = new $importerClassName();
    $importer->setSplFileObject($fileToImport);

    $importer->import();

    $fileToImport = null;
  }
}

