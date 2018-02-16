<?php

use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;

/**
 * Class CRM_HRSampleData_Importer_PublicHolidayTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_PublicHolidayTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testProcess() {

    $publicHoliday = $this->apiGet('PublicHoliday', ['title' => 'Christmas']);
    $this->assertEmpty($publicHoliday);

    $this->rows[] = [
      'Christmas',
      '2016-12-25',
      '1'
    ];

    // Public Holidays can only be created if their date is within an Absence
    // Period, so we need to create a period for it
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $this->runProcessor('CRM_HRSampleData_Importer_PublicHoliday', $this->rows);

    $publicHoliday = $this->apiGet('PublicHoliday', ['title' => 'Christmas']);

    foreach($this->rows[0] as $index => $fieldName) {
      $this->assertEquals($this->rows[1][$index], $publicHoliday[$fieldName]);
    }
  }

  private function importHeadersFixture() {
    return [
      'title',
      'date',
      'is_active',
    ];
  }

}
