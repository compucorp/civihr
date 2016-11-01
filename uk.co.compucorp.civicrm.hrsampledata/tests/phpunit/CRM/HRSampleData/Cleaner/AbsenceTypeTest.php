<?php

use CRM_HRAbsence_Test_Fabricator_HRAbsenceType as AbsenceTypeFabricator;

/**
 * Class CRM_HRSampleData_Cleaner_AbsenceTypeTest
 *
 * @group headless
 */
class CRM_HRSampleData_Cleaner_AbsenceTypeTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testProcess() {
    $absenceType = AbsenceTypeFabricator::fabricate();
    $testAbsenceType = $this->apiGet('HRAbsenceType', ['name' => $absenceType->name]);
    $this->assertEquals($absenceType->name, $testAbsenceType['name']);

    $this->rows[] = [
      $absenceType->name,
      $absenceType->name,
      1,
      0,
      1,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_AbsenceType', $this->rows);

    $absenceType = $this->apiGet('HRAbsenceType', ['name' => $absenceType->name]);
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
