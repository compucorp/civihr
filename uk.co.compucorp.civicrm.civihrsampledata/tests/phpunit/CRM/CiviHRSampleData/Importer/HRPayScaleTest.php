<?php

require_once EXTENSION_ROOT_DIR . 'CRM/CiviHRSampleData/Importer/HRPayScale.php';

/**
 * Class CRM_CiviHRSampleData_Importer_HRPayScaleTest
 *
 * @group headless
 */
class CRM_CiviHRSampleData_Importer_HRPayScaleTest extends CRM_CiviHRSampleData_BaseImporterTest {

  private $rows;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('org.civicrm.hrjobcontract')
      ->apply();
  }

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

    $this->runImporter('CRM_CiviHRSampleData_Importer_HRPayScale', $this->rows);

    $this->assertEquals('E2', $this->apiQuickGet('HRPayScale','pay_scale', 'E2'));
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
