<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/VacancyStage.php';

/**
 * Class CRM_HRSampleData_Importer_VacancyStageTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_VacancyStageTest extends CRM_HRSampleData_BaseImporterTest {

  private $vacancyID;
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('org.civicrm.hrjobcontract')
      ->install('org.civicrm.hrrecruitment')
      ->apply();
  }

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $vacancy = civicrm_api3('HRVacancy', 'create', [
      'position' => "test vacany",
      'start_date' => "2016-01-01",
      'end_date' => "",
    ]);
    $this->vacancyID = $vacancy['id'];
  }

  public function testImport() {
    $this->rows[] = [
      'Open',
      $this->vacancyID,
      1,
    ];

    $mapping = [
      ['vacancy_mapping', $this->vacancyID],
    ];

    $this->runImporter('CRM_HRSampleData_Importer_VacancyStage', $this->rows, $mapping);

    $this->assertNotEmpty($this->apiQuickGet('HRVacancyStage','vacancy_id', $this->vacancyID));
  }

  private function importHeadersFixture() {
    return [
      'case_status_id',
      'vacancy_id',
      'weight',
    ];
  }

}
