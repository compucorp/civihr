<?php

require_once EXTENSION_ROOT_DIR . 'CRM/CiviHRSampleData/Importer/Vacancy.php';

use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;

/**
 * Class CRM_CiviHRSampleData_Importer_VacancyTest
 *
 * @group headless
 */
class CRM_CiviHRSampleData_Importer_VacancyTest extends CRM_CiviHRSampleData_BaseImporterTest {

  private $rows;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('org.civicrm.hrjobcontract')
      ->install('org.civicrm.hrrecruitment')
      ->apply();
  }

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    OptionValueFabricator::fabricate('hrjc_location');
  }

  public function testImport() {
    $this->rows[] = [
      2,
      25000,
      'Junior Programme Coordinator',
      'Test Desc',
      'Test Ben',
      'Test Req',
      'test option',
      0,
      'Open',
      '2016-09-01 00:00:00',
      '2016-09-30 00:00:00',
      '2016-09-07 22:32:45',
    ];

    $this->runImporter('CRM_CiviHRSampleData_Importer_Vacancy', $this->rows);

    $this->assertEquals(
      'Junior Programme Coordinator',
      $this->apiQuickGet('HRVacancy','position', 'Junior Programme Coordinator')
    );
  }

  private function importHeadersFixture() {
    return [
      'id',
      'salary',
      'position',
      'description',
      'benefits',
      'requirements',
      'location',
      'is_template',
      'status_id',
      'start_date',
      'end_date',
      'created_date',
    ];
  }

}
