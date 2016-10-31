<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Cleaner/AbsenceType.php';

use CRM_HRAbsence_Test_Fabricator_HRAbsenceType as AbsenceTypeFabricator;

/**
 * Class CRM_HRSampleData_Cleaner_AbsenceTypeTest
 *
 * @group headless
 */
class CRM_HRSampleData_Cleaner_AbsenceTypeTest extends CRM_HRSampleData_BaseImporterTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testIterate() {
    AbsenceTypeFabricator::fabricate();
    $testAbsenceType = $this->apiGet('HRAbsenceType', ['name' => 'test absence type']);
    $this->assertEquals('test absence type', $testAbsenceType['name']);

    $this->rows[] = [
      'test absence type',
      'test absence type',
      1,
      0,
      1,
    ];

    $this->runIterator('CRM_HRSampleData_Cleaner_AbsenceType', $this->rows);

    $absenceType = $this->apiGet('HRAbsenceType', ['name' => 'test absence type']);
    $this->assertEmpty($absenceType);
  }

  private function importHeadersFixture() {
    return [
      'name',
      'title',
      'is_active',
      'allow_credits',
      'allow_debits',
    ];
  }

}
