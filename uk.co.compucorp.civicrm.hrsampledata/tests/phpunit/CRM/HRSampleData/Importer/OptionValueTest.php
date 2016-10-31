<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/OptionValue.php';

/**
 * Class CRM_HRSampleData_Importer_OptionValueTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_OptionValueTest extends CRM_HRSampleData_BaseImporterTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testIterate() {
    $this->rows[] = [
      'activity_type',
      'Compassionate_Leave',
      128,
      'Compassionate_Leave',
      'Timesheet',
      1,
      0,
      0,
      0,
      ''
    ];

    $this->runIterator('CRM_HRSampleData_Importer_OptionValue', $this->rows);

    $optionValue = $this->apiGet(
      'OptionValue',
      ['option_group_id' => 'activity_type', 'name' => 'Compassionate_Leave']
    );

    foreach($this->rows[0] as $index => $fieldName) {
      if (!in_array($fieldName, ['option_group_id', 'component_id'])) {
        $this->assertEquals($this->rows[1][$index], $optionValue[$fieldName]);
      }
    }
  }

  private function importHeadersFixture() {
    return [
      'option_group_id',
      'label',
      'value',
      'name',
      'grouping',
      'filter',
      'is_default',
      'is_optgroup',
      'is_reserved',
      'component_id',
    ];
  }

}
