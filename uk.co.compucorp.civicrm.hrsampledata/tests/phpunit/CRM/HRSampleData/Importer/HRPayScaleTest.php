<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/HRPayScale.php';

/**
 * Class CRM_HRSampleData_Importer_HRPayScaleTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_HRPayScaleTest extends CRM_HRSampleData_BaseImporterTest {

  private $rows;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testImport() {
    $this->rows[] = [
      'E2',
      'Head of Department',
      'USD',
      '70000',
      'Year'
    ];

    $this->runImporter('CRM_HRSampleData_Importer_HRPayScale', $this->rows);

    $this->assertEquals('E2', $this->apiGet('HRPayScale','pay_scale', 'E2'));
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
