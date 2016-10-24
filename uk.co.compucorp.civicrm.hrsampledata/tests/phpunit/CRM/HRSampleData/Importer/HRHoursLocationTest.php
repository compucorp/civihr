<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/HRHoursLocation.php';

/**
 * Class CRM_HRSampleData_Importer_HRHoursLocationTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_HRHoursLocationTest extends CRM_HRSampleData_BaseImporterTest {

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
      'Islington',
      38,
      'Week',
    ];

    $this->runImporter('CRM_HRSampleData_Importer_HRHoursLocation', $this->rows);

    $this->assertEquals('Islington', $this->apiQuickGet('HRHoursLocation','location', 'Islington'));
  }

  private function importHeadersFixture() {
    return [
      'location',
      'standard_hours',
      'periodicity',
    ];
  }

}
