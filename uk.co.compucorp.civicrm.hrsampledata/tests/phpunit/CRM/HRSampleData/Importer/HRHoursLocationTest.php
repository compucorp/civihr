<?php

/**
 * Class CRM_HRSampleData_Importer_HRHoursLocationTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_HRHoursLocationTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testProcess() {
    $this->rows[] = [
      'Islington',
      38,
      'Week',
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_HRHoursLocation', $this->rows);

    $hoursLocation = $this->apiGet('HRHoursLocation', ['location' => 'Islington']);

    $this->assertEntityEqualsToRows($this->rows, $hoursLocation);
  }

  private function importHeadersFixture() {
    return [
      'location',
      'standard_hours',
      'periodicity',
    ];
  }

}
