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
    $absencePeriod = $this->apiGet('AbsencePeriod', ['start_date' => '2016-01-01']);
    $this->assertEmpty($absencePeriod);

    $this->rows[] = [
      1,
      '2016',
      '2016-01-01',
      '2016-12-31',
      '1'
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_AbsencePeriod', $this->rows);

    $absencePeriod = $this->apiGet('AbsencePeriod', ['start_date' => '2016-01-01']);

    $fieldsToIgnore = ['id'];
    $this->assertEntityEqualsToRows($this->rows, $absencePeriod, $fieldsToIgnore);
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
