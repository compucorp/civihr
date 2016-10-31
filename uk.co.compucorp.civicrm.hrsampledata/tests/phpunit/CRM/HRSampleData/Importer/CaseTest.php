<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_CaseType as CaseTypeFabricator;

/**
 * Class CRM_HRSampleData_Importer_CaseTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_CaseTest extends CRM_HRSampleData_BaseImporterTest {

  private $testContact;

  private $testCaseType;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate();
    $this->testCaseType = CaseTypeFabricator::fabricate();
  }

  public function testIterate() {
    $this->rows[] = [
      1,
      'test case',
      $this->testCaseType['name'],
      '2016-09-08',
      'Open',
      0,
      $this->testContact['id'],
      $this->testContact['id'],
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']]
    ];

    $this->runIterator('CRM_HRSampleData_Importer_Case', $this->rows, $mapping);

    $case = $this->apiGet('Case', ['subject' => 'test case']);

    $this->assertEquals('test case', $case['subject']);
    $this->assertEquals('2016-09-08', $case['start_date']);
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
