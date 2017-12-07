<?php

use CRM_HRCore_Model_CiviHRStatistics as CiviHRStatistics;
use CRM_HRCore_Model_ReportConfiguration as ReportConfig;
use CRM_HRCore_Model_ReportConfigurationAgeGroup as AgeGroup;

/**
 * Responsible for converting a CiviHRStatistics object to JSON
 */
class CRM_HRCore_Service_CiviHRStatisticsJSONConvertor {

  /**
   * Takes a statistics class and converts it to a JSON string.
   *
   * @param CiviHRStatistics $stats
   *
   * @return string
   */
  public static function toJson(CiviHRStatistics $stats) {
    $array = [
      'siteUrl' => $stats->getSiteUrl(),
      'siteName' => $stats->getSiteName(),
      'generationDate' => self::formatDate($stats->getGenerationDate()),
    ];

    self::addRoleLoginData($stats, $array);

    foreach ($stats->getEntityCounts() as $entity => $count) {
      $array[$entity . 'Count'] = $count;
    }

    foreach ($stats->getContactSubtypeCounts() as $subtype => $count) {
      $array['contactPerSubTypeCount'][$subtype] = $count;
    }

    foreach ($stats->getReportConfigurations() as $config) {
      $array['reportConfigurations'][] = self::reportConfigurationToArray($config);
    }

    foreach ($stats->getReportConfigurationAgeGroups() as $ageGroup) {
      $array['reportConfigurationAgeGroups'][] = self::ageGroupToArray($ageGroup);
    }

    return json_encode($array);
  }

  /**
   * Adds most recent login data for each of the system roles.
   *
   * @param CiviHRStatistics $stats
   * @param $array
   */
  private static function addRoleLoginData(CiviHRStatistics $stats, &$array) {
    $defaultRoles = [
      'CiviHR Admin',
      'CiviHR Staff',
      'CiviHR Manager',
      'CiviHR Admin Local',
    ];

    foreach ($defaultRoles as $role) {
      $key = sprintf('last%sLogin', str_replace(' ', '', $role));
      $array[$key] = self::formatDate($stats->getMostRecentLoginByRole($role));
    }

    foreach ($stats->getMostRecentLogins() as $role => $mostRecentLogin) {
      if (!in_array($role, $defaultRoles)) {
        $array['lastLoginOtherRoles'][$role] = self::formatDate($mostRecentLogin);
      }
    }
  }

  /**
   * Converts a ReportConfiguration object to an array.
   *
   * @param ReportConfig $config
   *
   * @return array
   */
  private static function reportConfigurationToArray(ReportConfig $config) {
    return [
      'id' => $config->getId(),
      'report_name' => $config->getName(),
      'label' => $config->getLabel(),
      'json_config' => $config->getJsonConfig(),
    ];
  }

  /**
   * Converts an age group to an array
   *
   * @param AgeGroup $ageGroup
   * @return array
   */
  private static function ageGroupToArray(AgeGroup $ageGroup) {
    return [
      'id' => $ageGroup->getId(),
      'label' => $ageGroup->getLabel(),
      'age_from' => $ageGroup->getAgeFrom(),
      'age_to' => $ageGroup->getAgeTo(),
    ];
  }

  /**
   * Formats dates to ISO standard
   *
   * @param \DateTime $date
   *
   * @return string
   */
  private static function formatDate($date) {
    if (!$date instanceof \DateTime) {
      return '';
    }

    return $date->format($date::ISO8601);
  }

}
