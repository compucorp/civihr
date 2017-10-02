<?php

use CRM_Hrjobcontract_Test_Fabricator_HRHoursLocation as HoursLocationFabricator;

/**
 * Class CRM_HRSampleData_Cleaner_HRHoursLocationTest
 *
 * @group headless
 */
class CRM_HRSampleData_Cleaner_HRHoursLocationTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testProcessWithDeleteOnUninstallOn() {
    $testHourLocation = HoursLocationFabricator::fabricate();

    $this->rows[] = [
      $testHourLocation['location'],
      40,
      "Week",
      1,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_HRHoursLocation', $this->rows);

    $hourLocation = $this->apiGet('HRHoursLocation', ['location' => $testHourLocation['location']]);
    $this->assertEmpty($hourLocation);
  }

  public function testProcessWithDeleteOnUninstallOff() {
    $testHourLocation = HoursLocationFabricator::fabricate();

    $this->rows[] = [
      $testHourLocation['location'],
      40,
      "Week",
      0,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_HRHoursLocation', $this->rows);

    $hourLocation = $this->apiGet('HRHoursLocation', ['location' => $testHourLocation['location']]);
    $this->assertEquals($testHourLocation['location'], $hourLocation['location']);
  }

  private function importHeadersFixture() {
    return [
      'location',
      'standard_hours',
      'periodicity',
      'delete_on_uninstall',
    ];
  }
}
