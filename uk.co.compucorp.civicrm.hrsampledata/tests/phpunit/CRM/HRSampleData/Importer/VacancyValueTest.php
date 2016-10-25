<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/VacancyValue.php';

use CRM_HRCore_Test_Fabricator_Case as CaseFabricator;
use CRM_HRCore_Test_Fabricator_CaseType as CaseTypeFabricator;

use CRM_HRRecruitment_Test_Fabricator_Vacancy as VacancyFabricator;

/**
 * Class CRM_HRSampleData_Importer_VacancyValueTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_VacancyValueTest extends CRM_HRSampleData_BaseImporterTest {

  private $vacancyID;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->vacancyID = VacancyFabricator::fabricate()['id'];
  }

  public function testImport() {
    CaseTypeFabricator::fabricate();
    $caseID = CaseFabricator::fabricate()['id'];

    $this->rows[] = [
      $caseID,
      $this->vacancyID,
    ];

    $mapping = [
      ['case_mapping', $caseID],
      ['vacancy_mapping', $this->vacancyID],
    ];

    $this->runImporter('CRM_HRSampleData_Importer_VacancyValue', $this->rows, $mapping);

    $vacancyValue = $this->apiGet('CustomValue', ['entity_id' => $caseID, 'entity_table' => 'Case']);

    $this->assertEquals($caseID, $vacancyValue['entity_id']);
  }

  private function importHeadersFixture() {
    return [
      'entity_id',
      'vacancy_id',
    ];
  }

}
