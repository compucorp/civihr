<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/VacancyStage.php';

use CRM_HRRecruitment_Test_Fabricator_HRVacancy as VacancyFabricator;

/**
 * Class CRM_HRSampleData_Importer_VacancyStageTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_VacancyStageTest extends CRM_HRSampleData_BaseImporterTest {

  private $vacancyID;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->vacancyID = VacancyFabricator::fabricate()['id'];
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

    $vacancyStage = $this->apiGet('HRVacancyStage', ['vacancy_id' => $this->vacancyID]);

    $this->assertEquals($this->vacancyID, $vacancyStage['vacancy_id']);
    $this->assertEquals(1, $vacancyStage['weight']);
  }

  private function importHeadersFixture() {
    return [
      'case_status_id',
      'vacancy_id',
      'weight',
    ];
  }

}
