<?php

/**
 * Class CRM_HRSampleData_Importer_AbsenceTypeTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_AbsenceTypeTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testProcess() {
    $this->rows[] = [
      'Compassionate_Leave',
      'Compassionate Leave',
      1,
      0,
      1,
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_AbsenceType', $this->rows);

    $absenceType = $this->apiGet('HRAbsenceType', ['name' => 'Compassionate_Leave']);

    foreach($this->rows[0] as $index => $fieldName) {
      $this->assertEquals($this->rows[1][$index], $absenceType[$fieldName]);
    }
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
