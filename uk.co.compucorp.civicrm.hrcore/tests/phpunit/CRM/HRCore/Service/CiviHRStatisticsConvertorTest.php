<?php

use CRM_HRCore_Service_CiviHRStatisticsJSONConvertor as CiviHRStatisticsConvertor;
use CRM_HRCore_Model_CiviHRStatistics as CiviHRStatistics;
use CRM_HRCore_Model_ReportConfigurationAgeGroup as AgeGroup;
use CRM_HRCore_Model_ReportConfiguration as ReportConfiguration;

class CiviHRStatisticsConvertorTest extends PHPUnit_Framework_TestCase {

  public function testConversionOfEmptyStatsClass() {
    $stats = new CiviHRStatistics();
    $json = CiviHRStatisticsConvertor::toJson($stats);
    $array = json_decode($json, TRUE);

    $this->assertEquals(JSON_ERROR_NONE, json_last_error());
    $this->assertArrayHasKey('siteName', $array);
    $this->assertNull($array['siteName']);
  }

  public function testConversionWillMatchExpectedJSONFile() {
    $dateForAll = new \DateTime('2012-04-23T18:25:43+0000');

    $reportConfig = new ReportConfiguration();
    $reportConfig
      ->setId(1)
      ->setName('people')
      ->setLabel('Test')
      ->setJsonConfig(json_encode([
        'menuLimit' => '200',
        'unusedAttrsVertical' => 'false',
        'autoSortUnusedAttrs' => 'false',
        'rendererName' => 'Table',
        'aggregatorName' => 'Count',
      ]));

    $ageGroup = new AgeGroup();
    $ageGroup
      ->setId(1)
      ->setAgeFrom(0)
      ->setAgeTo(25)
      ->setLabel('Young');

    $stats = new CiviHRStatistics();
    $stats
      ->setSiteUrl('compucorp.civihrhosting.co.uk')
      ->setSiteName('Compucorp')
      ->setGenerationDate($dateForAll)
      ->setMostRecentLoginForRole('CiviHR Admin', $dateForAll)
      ->setMostRecentLoginForRole('CiviHR Staff', $dateForAll)
      ->setMostRecentLoginForRole('CiviHR Manager', $dateForAll)
      ->setMostRecentLoginForRole('CiviHR Admin Local', $dateForAll)
      ->setMostRecentLoginForRole('Administrator', $dateForAll)
      ->setMostRecentLoginForRole('CustomRole', $dateForAll)
      ->setEntityCount('drupalUser', 21)
      ->setEntityCount('assignment', 10)
      ->setEntityCount('task', 14)
      ->setEntityCount('document', 3)
      ->setEntityCount('leaveRequest', 2)
      ->setEntityCount('leaveRequestInLast100Days', 2)
      ->setEntityCount('vacancy', 1)
      ->setEntityCount('reportConfiguration', 1)
      ->setContactSubtypeCount('Individual', 35)
      ->setContactSubtypeCount('Organization', 1)
      ->addReportConfiguration($reportConfig)
      ->addReportConfigurationAgeGroup($ageGroup);

    $json = CiviHRStatisticsConvertor::toJson($stats);
    $testFile = __DIR__ . '/../Files/statistics_sample_request.json';
    $expected = file_get_contents($testFile);
    $asArray = json_decode($json, TRUE);
    $expectedAsArray = json_decode($expected, TRUE);
    asort($asArray);
    asort($expectedAsArray);

    $this->assertEquals($expectedAsArray, $asArray);
  }

}
