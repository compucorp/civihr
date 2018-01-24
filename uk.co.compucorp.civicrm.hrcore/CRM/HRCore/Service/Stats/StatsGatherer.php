<?php

use CRM_HRCore_Model_CiviHRStatistics as CiviHRStatistics;
use CRM_HRCore_Model_ReportConfiguration as ReportConfiguration;
use CRM_HRCore_Model_ReportConfigurationAgeGroup as AgeGroup;
use CRM_HRCore_CMSData_SiteInformation_SiteInformationInterface as SiteInformationInterface;
use CRM_HRCore_CMSData_Role_RoleServiceInterface as RoleServiceInterface;
use CRM_HRCore_Helper_ExtensionHelper as ExtensionHelper;

/**
 * Responsible for gathering all required site statistics that will be sent to
 * monitor site usage.
 */
class CRM_HRCore_Service_Stats_StatsGatherer {

  /**
   * @var SiteInformationInterface
   */
  protected $siteInformation;

  /**
   * @var RoleServiceInterface
   */
  protected $roleService;

  /**
   * @param SiteInformationInterface $siteInformation
   * @param RoleServiceInterface $roleService
   */
  public function __construct(
    SiteInformationInterface $siteInformation,
    RoleServiceInterface $roleService
  ) {
    $this->siteInformation = $siteInformation;
    $this->roleService = $roleService;
  }

  /**
   * Fetch and set all required statistics.
   *
   * @return CiviHRStatistics
   */
  public function gather() {
    $stats = new CiviHRStatistics();
    $stats->setGenerationDate(new \DateTime());
    $stats->setSiteName($this->siteInformation->getSiteName());
    $stats->setSiteUrl(CRM_Core_Config::singleton()->userFrameworkBaseURL);

    foreach ($this->getEntityCounts() as $entity => $count) {
      $stats->setEntityCount($entity, $count);
    }

    foreach ($this->getContactSubtypes() as $subtype => $count) {
      $stats->setContactSubtypeCount($subtype, $count);
    }

    $reportConfigs = $this->getReportConfigurations();
    foreach ($reportConfigs as $configuration) {
      $stats->addReportConfiguration($configuration);
      $stats->setEntityCount('reportConfiguration', count($reportConfigs));
    }

    foreach ($this->getAgeGroups() as $group) {
      $stats->addReportConfigurationAgeGroup($group);
    }

    foreach ($this->roleService->getLatestLoginByRole() as $role => $login) {
      $stats->setMostRecentLoginForRole($role, $login);
    }

    return $stats;
  }

  /**
   * Fetches counts for all required entities
   *
   * @return array
   */
  private function getEntityCounts() {
    $entityCounts = [];
    $entityCounts['contact'] = $this->getEntityCount('Contact');
    $entityCounts['cmsUser'] = $this->getEntityCount('UFMatch');
    $entityCounts += $this->getTaskAndAssignmentEntityCounts();
    $entityCounts += $this->getLeaveAndAbsenceEntityCounts();
    $entityCounts += $this->getRecruitmentEntityCounts();

    return $entityCounts;
  }

  /**
   * Gets the number of a certain entity in the system.
   *
   * @param string $entity
   *   The name of the entity to check
   * @param array $params
   *   Optional criteria for the count
   *
   * @return int
   */
  private function getEntityCount($entity, $params = []) {
    $params += ['is_deleted' => FALSE];

    return (int) civicrm_api3($entity, 'getcount', $params);
  }

  /**
   * Fetches counts for contact subtypes
   *
   * @return array
   */
  private function getContactSubtypes() {
    $contactTypes = civicrm_api3('ContactType', 'get')['values'];
    $contactTypeCounts = [];

    foreach ($contactTypes as $contactType) {
      $name = $contactType['name'];

      if (!empty($contactType['parent_id'])) {
        $params = ['contact_sub_type' => $name];
      } else {
        $params = ['contact_type' => $name];
      }

      $contactTypeCounts[$name] = $this->getEntityCount('Contact', $params);
    }

    return $contactTypeCounts;
  }

  /**
   * Fetches report configurations
   *
   * @return array
   */
  private function getReportConfigurations() {
    $configurations = [];

    // Reports are only available in Drupal
    if (!$this->isDrupal()) {
      return $configurations;
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
      $configurations[] = $config;
    }

    return $configurations;
  }

  /**
   * Gets age group settings
   *
   * @return array
   */
  private function getAgeGroups() {
    $groups = [];

    // Reports are only available in Drupal
    if (!$this->isDrupal()) {
      return $groups;
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
      $groups[] = $group;
    }

    return $groups;
  }

  /**
   * Fetches entity counts for the leave and absence entities
   *
   * @return array
   */
  private function getLeaveAndAbsenceEntityCounts() {
    $leaveAndAbsenceKey = 'uk.co.compucorp.civicrm.hrleaveandabsences';
    $counts = [];

    if (!ExtensionHelper::isExtensionEnabled($leaveAndAbsenceKey)) {
      return $counts;
    }

    // leave requests in last 100 days
    $oneHundredDaysAgo = CRM_Utils_Date::processDate('midnight today - 100 days');
    $today = CRM_Utils_Date::processDate('midnight today');

    $params = ['from_date' => ['BETWEEN' => [$oneHundredDaysAgo, $today]]];
    $last100DaysCount = $this->getEntityCount('LeaveRequest', $params);
    $counts['leaveRequestInLast100Days'] = $last100DaysCount;

    // total leave requests
    $leaveRequestCount = $this->getEntityCount('LeaveRequest');
    $counts['leaveRequest'] = $leaveRequestCount;

    return $counts;
  }

  /**
   * Fetches entity counts for the tasks and assignments entities
   *
   * @return array
   */
  private function getTaskAndAssignmentEntityCounts() {
    $taskAssignmentsKey = 'uk.co.compucorp.civicrm.tasksassignments';
    $counts = [];

    if (!ExtensionHelper::isExtensionEnabled($taskAssignmentsKey)) {
      return $counts;
    }

    $counts['assignment'] = $this->getEntityCount('Assignment');
    $counts['task'] = $this->getEntityCount('Task');
    $counts['document'] = $this->getEntityCount('Document');

    return $counts;
  }

  /**
   * Fetches entity counts for the recruitment entities
   *
   * @return array
   */
  private function getRecruitmentEntityCounts() {
    $recruitmentKey = 'org.civicrm.hrrecruitment';
    $counts = [];

    if (!ExtensionHelper::isExtensionEnabled($recruitmentKey)) {
      return $counts;
    }

    $counts['vacancy'] = $this->getEntityCount('HRVacancy');

    return $counts;
  }

  /**
   * Checks if the underlying CMS system is Drupal
   *
   * @return bool
   */
  private function isDrupal() {
    $userSystem = CRM_Core_Config::singleton()->userSystem;

    return $userSystem instanceof CRM_Utils_System_Drupal;
  }

}
