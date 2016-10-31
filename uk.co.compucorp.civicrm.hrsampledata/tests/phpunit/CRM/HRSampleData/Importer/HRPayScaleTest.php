<?php

/**
 * Class CRM_HRSampleData_Importer_HRPayScaleTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_HRPayScaleTest extends CRM_HRSampleData_BaseImporterTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testIterate() {
    $this->rows[] = [
      'E2',
      'Head of Department',
      'USD',
      '70000',
      'Year'
    ];

    $this->runIterator('CRM_HRSampleData_Importer_HRPayScale', $this->rows);

    $payScale = $this->apiGet('HRPayScale', ['pay_scale' => 'E2']);

    foreach($this->rows[0] as $index => $fieldName) {
      $this->assertEquals($this->rows[1][$index], $payScale[$fieldName]);
    }
  }

  private function importHeadersFixture() {
    return [
      'pay_scale',
      'pay_grade',
      'currency',
      'amount',
      'periodicity',
    ];
  }

}
