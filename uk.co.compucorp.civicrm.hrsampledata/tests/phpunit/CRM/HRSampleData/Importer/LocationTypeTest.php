<?php

/**
 * Class CRM_HRSampleData_Importer_LocationTypeTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_LocationTypeTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testProcess() {
    $existing = civicrm_api3('LocationType', 'get', ['name' => 'Work']);
    if (isset($existing['id'])) {
      civicrm_api3('LocationType', 'delete', ['id' => $existing['id']]);
    }

    $this->rows[] = [
      'Work',
      'Work',
      'WORK',
      'Work address',
      0,
      0,
      0
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_LocationType', $this->rows);

    $locationType = $this->apiGet('LocationType', ['name' => 'Work']);

    foreach($this->rows[0] as $index => $fieldName) {
      $this->assertEquals($this->rows[1][$index], $locationType[$fieldName]);
    }
  }

  private function importHeadersFixture() {
    return [
      'name',
      'display_name',
      'vcard_name',
      'description',
      'is_reserved',
      'is_active',
      'is_default'
    ];
  }

}
