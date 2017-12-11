<?php

use CRM_HRCore_Model_CiviHRStatistics as CiviHRStatistics;
use CRM_HRCore_Model_ReportConfiguration as ReportConfiguration;
use CRM_HRCore_Model_ReportConfigurationAgeGroup as AgeGroup;
use CRM_HRCore_CMSData_Variable_VariableServiceInterface as VariableServiceInterface;
use CRM_HRCore_Helper_ExtensionHelper as ExtensionHelper;
use CRM_HRCore_CMSData_Variable_DrupalVariableService as DrupalVariableService;

/**
 * Responsible for gathering all required site statistics that will be sent to
 * monitor site usage.
 */
class CRM_HRCore_Service_CiviHRStatsGatherer {

  /**
   * @var VariableServiceInterface
   */
  protected $cmsVariableService;

  /**
   * @param VariableServiceInterface $cmsVariableService
   */
  public function __construct(VariableServiceInterface $cmsVariableService) {
    $this->cmsVariableService = $cmsVariableService;
  }

  /**
   * Fetch and set all required statistics.
   *
   * @return CiviHRStatistics
   */
  public function gather() {
    $stats = new CiviHRStatistics();
    $stats->setGenerationDate(new \DateTime());
    $stats->setSiteName($this->cmsVariableService->get('site_name'));
    $this->setBaseUrl($stats);
    $this->setEntityCounts($stats);
    $this->setContactSubtypes($stats);
    $this->setReportConfigurations($stats);
    $this->setAgeGroups($stats);

    return $stats;
  }

  /**
   * Sets the site base URL
   *
   * @param CiviHRStatistics $stats
   */
  private function setBaseUrl(CiviHRStatistics $stats) {
    $config = & CRM_Core_Config::singleton();
    $stats->setSiteUrl($config->userFrameworkBaseURL);
  }

  /**
   * Fetches counts and sets them for all required entities
   *
   * @param CiviHRStatistics $stats
   * @throws CiviCRM_API3_Exception
   */
  private function setEntityCounts(CiviHRStatistics $stats) {
    $taskAssignmentsKey = 'uk.co.compucorp.civicrm.tasksassignments';
    $leaveAndAbsenceKey = 'uk.co.compucorp.civicrm.hrleaveandabsences';
    $recruitmentKey = 'org.civicrm.hrrecruitment';

    $stats->setEntityCount('contact', $this->getEntityCount('Contact'));

    if (ExtensionHelper::isExtensionEnabled($taskAssignmentsKey)) {
      $stats->setEntityCount('assignment', $this->getEntityCount('Assignment'));
      $stats->setEntityCount('task', $this->getEntityCount('Task'));
      $stats->setEntityCount('document', $this->getEntityCount('Document'));
    }

    if (ExtensionHelper::isExtensionEnabled($leaveAndAbsenceKey)) {
      // leave requests in last 100 days
      $format = 'Y-m-d H:i:s';
      $oneHundredDaysAgo = (new \DateTime('today - 100 days'))->format($format);
      $params = ['from_date' => ['>=' => $oneHundredDaysAgo]];
      $last100DaysCount = (int) civicrm_api3('LeaveRequest', 'getcount', $params);
      $stats->setEntityCount('leaveRequestInLast100Days', $last100DaysCount);

      // total leave requests
      $leaveRequestCount = $this->getEntityCount('LeaveRequest');
      $stats->setEntityCount('leaveRequest', $leaveRequestCount);
    }

    if (ExtensionHelper::isExtensionEnabled($recruitmentKey)) {
      $stats->setEntityCount('vacancy', $this->getEntityCount('HRVacancy'));
    }

    $stats->setEntityCount('drupalUser', $this->getEntityCount('UFMatch'));
  }

  /**
   * Gets the number of a certain entity in the system.
   *
   * @param string $entity
   *
   * @return int
   */
  private function getEntityCount($entity) {
    return (int) civicrm_api3($entity, 'getcount');
  }

  /**
   * Fetches contact subtypes
   *
   * @param CiviHRStatistics $stats
   * @throws CiviCRM_API3_Exception
   */
  private function setContactSubtypes(CiviHRStatistics $stats) {
    $params = ['parent_id' => ['IS NULL' => 1]];
    $contactTypes = civicrm_api3('ContactType', 'get', $params)['values'];
    foreach ($contactTypes as $contactType) {
      $name = $contactType['name'];
      $count = civicrm_api3('Contact', 'getcount', ['contact_type' => $name]);
      $stats->setContactSubtypeCount($name, (int) $count);
    }
  }

  /**
   * Fetches report configurations
   *
   * @param CiviHRStatistics $stats
   */
  private function setReportConfigurations(CiviHRStatistics $stats) {
    // Reports are only available in Drupal
    if (!$this->cmsVariableService instanceof DrupalVariableService) {
      return;
    }

    $query = db_select('reports_configuration', 'rc')->fields('rc');
    $result = $query->execute();

    while ($row = $result->fetchAssoc()) {
      $config = new ReportConfiguration();
      $config
        ->setId($row['id'])
        ->setLabel($row['label'])
        ->setName($row['name'])
        ->setJsonConfig($row['json_config']);
      $stats->addReportConfiguration($config);
    }
  }

  /**
   * Sets age group settings
   *
   * @param CiviHRStatistics $stats
   */
  private function setAgeGroups(CiviHRStatistics $stats) {
    // Reports are only available in Drupal
    if (!$this->cmsVariableService instanceof DrupalVariableService) {
      return;
    }

    $query = db_select('reports_settings_age_group', 'ag')->fields('ag');
    $result = $query->execute();

    while ($row = $result->fetchAssoc()) {
      $group = new AgeGroup();
      $group
        ->setId($row['id'])
        ->setLabel($row['label'])
        ->setAgeFrom($row['age_from'])
        ->setAgeTo($row['age_to']);
      $stats->addReportConfigurationAgeGroup($group);
    }
  }

}
