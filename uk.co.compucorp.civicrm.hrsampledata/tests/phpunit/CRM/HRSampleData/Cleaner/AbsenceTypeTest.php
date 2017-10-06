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

  public function testProcessWithDeleteOnUninstallOn() {
    $testAbsenceType = AbsenceTypeFabricator::fabricate();

    $this->rows[] = [
      $testAbsenceType->name,
      $testAbsenceType->name,
      1,
      0,
      1,
      1,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_AbsenceType', $this->rows);

    $absenceType = $this->apiGet('HRAbsenceType', ['name' => $testAbsenceType->name]);
    $this->assertEmpty($absenceType);
  }

  public function testProcessWithDeleteOnUninstallOff() {
    $testAbsenceType = AbsenceTypeFabricator::fabricate();

    $this->rows[] = [
      $testAbsenceType->name,
      $testAbsenceType->name,
      1,
      0,
      1,
      0,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_AbsenceType', $this->rows);

    $absenceType = $this->apiGet('HRAbsenceType', ['name' => $testAbsenceType->name]);
    $this->assertEquals($testAbsenceType->name, $absenceType['name']);
  }

  private function importHeadersFixture() {
    return [
      'name',
      'title',
      'is_active',
      'allow_credits',
      'allow_debits',
      'delete_on_uninstall',
    ];
  }
}
