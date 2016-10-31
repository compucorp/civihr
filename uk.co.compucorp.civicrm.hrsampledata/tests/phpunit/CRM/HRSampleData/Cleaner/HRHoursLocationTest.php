<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/HRHoursLocation.php';

use CRM_Hrjobcontract_Test_Fabricator_HRHoursLocation as HoursLocationFabricator;

/**
 * Class CRM_HRSampleData_Cleaner_HRHoursLocationTest
 *
 * @group headless
 */
class CRM_HRSampleData_Cleaner_HRHoursLocationTest extends CRM_HRSampleData_BaseImporterTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testIterator() {
    HoursLocationFabricator::fabricate();
    $testHourLocation = $this->apiGet('HRHoursLocation', ['location' => 'test location']);
    $this->assertEquals('test location', $testHourLocation['location']);

    $this->rows[] = [
      'test location',
      40,
      "Week",
    ];

    $this->runIterator('CRM_HRSampleData_Cleaner_HRHoursLocation', $this->rows);

    $hourLocation = $this->apiGet('HRHoursLocation', ['location' => 'test location']);
    $this->assertEmpty($hourLocation);
  }

  private function importHeadersFixture() {
    return [
      'location',
      'standard_hours',
      'periodicity',
    ];
  }

}
