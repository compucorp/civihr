<?php

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
    $hourLocation = HoursLocationFabricator::fabricate();
    $testHourLocation = $this->apiGet('HRHoursLocation', ['location' => $hourLocation['location']]);
    $this->assertEquals($hourLocation['location'], $testHourLocation['location']);

    $this->rows[] = [
      $hourLocation['location'],
      40,
      "Week",
    ];

    $this->runIterator('CRM_HRSampleData_Cleaner_HRHoursLocation', $this->rows);

    $hourLocation = $this->apiGet('HRHoursLocation', ['location' => $hourLocation['location']]);
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
