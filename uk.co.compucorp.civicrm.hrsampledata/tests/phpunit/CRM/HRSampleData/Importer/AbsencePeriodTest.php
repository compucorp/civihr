<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/AbsencePeriod.php';

/**
 * Class CRM_HRSampleData_Importer_AbsencePeriodTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_AbsencePeriodTest extends CRM_HRSampleData_BaseImporterTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testIterate() {
    $this->rows[] = [
      '2016',
      '2016 (Jan 1 to Dec 31)',
      '2016-01-01 02:00:00',
      '2017-01-01 01:59:59',
    ];

    $this->runIterator('CRM_HRSampleData_Importer_AbsencePeriod', $this->rows);

    $absencePeriod = $this->apiGet('HRAbsencePeriod', ['name' => '2016']);

    foreach($this->rows[0] as $index => $fieldName) {
      $this->assertEquals($this->rows[1][$index], $absencePeriod[$fieldName]);
    }
  }

  private function importHeadersFixture() {
    return [
      'name',
      'title',
      'start_date',
      'end_date',
    ];
  }

}
