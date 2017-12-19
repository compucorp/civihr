<?php

use CRM_HRCore_Model_ReportConfiguration as ReportConfig;
use CRM_HRCore_Model_ReportConfigurationAgeGroup as ReportAgeGroup;

/**
 * This is the parent class that contains all scalar values that will be sent
 * as part of the statistics request. It contains links to other more complex
 * data structures, such as Report configurations.
 */
class CRM_HRCore_Model_CiviHRStatistics {

  /**
   * @var string
   *   The URL of the site
   */
  protected $siteUrl;

  /**
   * @var string
   *   The name of the site, found in the Drupal settings.php
   */
  protected $siteName;

  /**
   * @var \DateTime
   *   The date these statistics were generated
   */
  protected $generationDate;

  /**
   * @var \DateTime[]
   *   Array of most recent login dates, indexed by role name
   */
  protected $mostRecentLogins = [];

  /**
   * @var int[]
   *   Each entity count, indexed by entity type
   */
  protected $entityCounts = [];

  /**
   * @var int[]
   *   Each contact subtype count, indexed by subtype name
   */
  protected $contactSubtypeCount = [];

  /**
   * @var ReportConfig[]
   */
  protected $reportConfigurations = [];

  /**
   * @var ReportAgeGroup[]
   */
  protected $reportConfigurationAgeGroups = [];

  /**
   * @return string
   */
  public function getSiteUrl() {
    return $this->siteUrl;
  }

  /**
   * @param string $siteUrl
   *
   * @return $this
   */
  public function setSiteUrl($siteUrl) {
    $this->siteUrl = $siteUrl;

    return $this;
  }

  /**
   * @return string
   */
  public function getSiteName() {
    return $this->siteName;
  }

  /**
   * @param string $siteName
   *
   * @return $this
   */
  public function setSiteName($siteName) {
    $this->siteName = $siteName;

    return $this;
  }

  /**
   * @return DateTime
   */
  public function getGenerationDate() {
    return $this->generationDate;
  }

  /**
   * @param DateTime $generationDate
   *
   * @return $this
   */
  public function setGenerationDate($generationDate) {
    $this->generationDate = $generationDate;

    return $this;
  }

  /**
   * @param $roleName
   *   The name of the role
   * @param DateTime|null $time
   *   The most recent login time, NULL if no login exists
   *
   * @return $this
   */
  public function setMostRecentLoginForRole($roleName, $time) {
    $this->mostRecentLogins[$roleName] = $time;

    return $this;
  }

  /**
   * @return DateTime[]
   */
  public function getMostRecentLogins() {
    return $this->mostRecentLogins;
  }

  /**
   * @param $role
   * @return null|\DateTime
   */
  public function getMostRecentLoginByRole($role) {
    return CRM_Utils_Array::value($role, $this->mostRecentLogins);
  }

  /**
   * @param string $entity
   *   The name of the entity, in camel case
   * @param int $count
   *   The count for that entity
   *
   * @return $this
   */
  public function setEntityCount($entity, $count) {
    $this->entityCounts[$entity] = $count;

    return $this;
  }

  /**
   * @param string $entity
   *   The name of the entity, in camel case
   * @return int
   */
  public function getEntityCount($entity) {
    return CRM_Utils_Array::value($entity, $this->entityCounts, 0);
  }

  /**
   * @return int[]
   */
  public function getEntityCounts() {
    return $this->entityCounts;
  }

  /**
   * @param string $subtypeName
   * @param int $count
   *
   * @return $this
   */
  public function setContactSubtypeCount($subtypeName, $count) {
    $this->contactSubtypeCount[$subtypeName] = $count;

    return $this;
  }

  /**
   * @return array
   */
  public function getContactSubtypeCounts() {
    return $this->contactSubtypeCount;
  }

  /**
   * @param string $subtypeName
   *
   * @return int
   */
  public function getContactSubtypeCount($subtypeName) {
    return CRM_Utils_Array::value($subtypeName, $this->contactSubtypeCount, 0);
  }

  /**
   * @param ReportConfig $configuration
   *
   * @return $this
   */
  public function addReportConfiguration(ReportConfig $configuration) {
    $this->reportConfigurations[] = $configuration;

    return $this;
  }

  /**
   * @return ReportConfig[]
   */
  public function getReportConfigurations() {
    return $this->reportConfigurations;
  }

  /**
   * @param ReportAgeGroup $ageGroup
   *
   * @return $this
   */
  public function addReportConfigurationAgeGroup(ReportAgeGroup $ageGroup) {
    $this->reportConfigurationAgeGroups[] = $ageGroup;

    return $this;
  }

  /**
   * @return ReportAgeGroup[]
   */
  public function getReportConfigurationAgeGroups() {
    return $this->reportConfigurationAgeGroups;
  }
}
