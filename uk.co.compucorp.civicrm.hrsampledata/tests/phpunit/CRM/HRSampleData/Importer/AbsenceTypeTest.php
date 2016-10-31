<?php

/**
 * Class CRM_HRSampleData_Importer_AbsenceTypeTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_AbsenceTypeTest extends CRM_HRSampleData_BaseImporterTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testIterate() {
    $this->rows[] = [
      'Compassionate_Leave',
      'Compassionate Leave',
      1,
      0,
      1,
    ];

    $this->runIterator('CRM_HRSampleData_Importer_AbsenceType', $this->rows);

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
