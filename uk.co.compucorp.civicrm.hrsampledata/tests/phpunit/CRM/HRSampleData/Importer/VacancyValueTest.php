<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/VacancyValue.php';

use CRM_HRCore_Test_Fabricator_Case as CaseFabricator;

/**
 * Class CRM_HRSampleData_Importer_VacancyValueTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_VacancyValueTest extends CRM_HRSampleData_BaseImporterTest {

  private $rows;

  private $vacancyID;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
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
    CaseFabricator::fabricateCaseType();
    $caseID = CaseFabricator::fabricateCase()['id'];

    $this->rows[] = [
      $caseID,
      $this->vacancyID,
    ];

    $mapping = [
      ['case_mapping', $caseID],
      ['vacancy_mapping', $this->vacancyID],
    ];

    $this->runImporter('CRM_HRSampleData_Importer_VacancyValue', $this->rows, $mapping);

    $this->assertNotEmpty($this->apiQuickGet('CustomValue','entity_id', $caseID, ['entity_table' => 'Case']));
  }

  private function importHeadersFixture() {
    return [
      'entity_id',
      'vacancy_id',
    ];
  }

}
