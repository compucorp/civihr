<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/Case.php';

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_CaseType as CaseTypeFabricator;

/**
 * Class CRM_HRSampleData_Importer_CaseTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_CaseTest extends CRM_HRSampleData_BaseImporterTest {

  private $rows;

  private $testContact;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate();
    CaseTypeFabricator::fabricate();
  }

  public function testImport() {
    $this->rows[] = [
      1,
      'test case',
      'test case',
      '2016-09-08',
      'Open',
      0,
      $this->testContact['id'],
      $this->testContact['id'],
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']]
    ];

    $this->runImporter('CRM_HRSampleData_Importer_Case', $this->rows, $mapping);

    $this->assertEquals('test case', $this->apiGet('Case', 'subject', 'test case'));
  }

  private function importHeadersFixture() {
    return [
      'id',
      'subject',
      'case_type_id',
      'start_date',
      'status_id',
      'is_deleted',
      'contact_id',
      'creator_id',
    ];
  }

}
