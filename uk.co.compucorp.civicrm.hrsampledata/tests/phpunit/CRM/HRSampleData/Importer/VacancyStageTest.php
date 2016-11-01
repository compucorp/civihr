<?php

use CRM_HRRecruitment_Test_Fabricator_HRVacancy as VacancyFabricator;

/**
 * Class CRM_HRSampleData_Importer_VacancyStageTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_VacancyStageTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  private $vacancyID;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->vacancyID = VacancyFabricator::fabricate()['id'];
  }

  public function testProcess() {
    $this->rows[] = [
      'Open',
      $this->vacancyID,
      1,
    ];

    $mapping = [
      ['vacancy_mapping', $this->vacancyID],
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_VacancyStage', $this->rows, $mapping);

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
