<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/Vacancy.php';

use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;

/**
 * Class CRM_HRSampleData_Importer_VacancyTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_VacancyTest extends CRM_HRSampleData_BaseImporterTest {

  private $rows;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    OptionValueFabricator::fabricate(['option_group_id' => 'hrjc_location']);
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

    $this->runImporter('CRM_HRSampleData_Importer_Vacancy', $this->rows);

    $this->assertEquals(
      'Junior Programme Coordinator',
      $this->apiGet('HRVacancy','position', 'Junior Programme Coordinator')
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
