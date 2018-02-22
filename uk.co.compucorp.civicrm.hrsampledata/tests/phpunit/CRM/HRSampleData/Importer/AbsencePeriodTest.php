<?php

/**
 * Class CRM_HRSampleData_Importer_AbsencePeriodTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_AbsencePeriodTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testProcess() {

    $absencePeriod = $this->apiGet('AbsencePeriod', ['name' => '2016']);
    $this->assertEmpty($absencePeriod);

    $this->rows[] = [
      1,
      '2016 (Jan 1 to Dec 31)',
      '2016-01-01',
      '2017-01-31',
      '1'
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_AbsencePeriod', $this->rows);

    $absencePeriod = $this->apiGet('AbsencePeriod', ['start_date' => '2016-01-01']);

    foreach($this->rows[0] as $index => $fieldName) {
      // ID is just a placeholder and it will be changed once inserted into the
      // database, so we ignore it here
      if($fieldName == 'id') {
        continue;
      }

      $this->assertEquals($this->rows[1][$index], $absencePeriod[$fieldName]);
    }
  }

  private function importHeadersFixture() {
    return [
      'id',
      'title',
      'start_date',
      'end_date',
      'weight'
    ];
  }

}
