<?php

use CRM_HRCore_Model_CiviHRStatistics as CiviHRStatistics;
use CRM_HRCore_Model_ReportConfiguration as ReportConfig;
use CRM_HRCore_Model_ReportConfigurationAgeGroup as AgeGroup;

/**
 * Responsible for converting a CiviHRStatistics object to JSON
 */
class CRM_HRCore_Service_Stats_StatsJSONConvertor {

  /**
   * Takes a statistics class and converts it to a JSON string.
   *
   * @param CiviHRStatistics $stats
   *
   * @return string
   */
  public static function toJson(CiviHRStatistics $stats) {
    $statsAsArray = [
      'siteUrl' => $stats->getSiteUrl(),
      'siteName' => $stats->getSiteName(),
      'generationDate' => self::formatDate($stats->getGenerationDate()),
    ];

    self::addRoleLoginData($stats, $statsAsArray);

    foreach ($stats->getEntityCounts() as $entity => $count) {
      $statsAsArray[$entity . 'Count'] = $count;
    }

    foreach ($stats->getContactSubtypeCounts() as $subtype => $count) {
      $statsAsArray['contactPerSubTypeCount'][$subtype] = $count;
    }

    foreach ($stats->getReportConfigurations() as $config) {
      $configAsArray = self::reportConfigurationToArray($config);
      $statsAsArray['reportConfigurations'][] = $configAsArray;
    }

    foreach ($stats->getReportConfigurationAgeGroups() as $ageGroup) {
      $ageGroupAsArray = self::ageGroupToArray($ageGroup);
      $statsAsArray['reportConfigurationAgeGroups'][] = $ageGroupAsArray;
    }

    return json_encode($statsAsArray);
  }

  /**
   * Adds most recent login data for each of the system roles.
   *
   * @param CiviHRStatistics $stats
   * @param $array
   */
  private static function addRoleLoginData(CiviHRStatistics $stats, &$array) {
    foreach ($stats->getMostRecentLogins() as $role => $mostRecentLogin) {
      $formattedDate = self::formatDate($mostRecentLogin);
      $array['lastLoginByRole'][$role] = $formattedDate;
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
