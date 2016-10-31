<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/HRHoursLocation.php';

/**
 * Class CRM_HRSampleData_Importer_HRHoursLocationTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_HRHoursLocationTest extends CRM_HRSampleData_BaseImporterTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testIterate() {
    $this->rows[] = [
      'Islington',
      38,
      'Week',
    ];

    $this->runIterator('CRM_HRSampleData_Importer_HRHoursLocation', $this->rows);

    $hoursLocation = $this->apiGet('HRHoursLocation', ['location' => 'Islington']);

    foreach($this->rows[0] as $index => $fieldName) {
      $this->assertEquals($this->rows[1][$index], $hoursLocation[$fieldName]);
    }
  }

  private function importHeadersFixture() {
    return [
      'location',
      'standard_hours',
      'periodicity',
    ];
  }

}
