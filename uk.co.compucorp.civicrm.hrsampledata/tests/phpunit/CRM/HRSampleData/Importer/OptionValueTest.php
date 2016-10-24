<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/OptionValue.php';

/**
 * Class CRM_HRSampleData_Importer_OptionValueTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_OptionValueTest extends CRM_HRSampleData_BaseImporterTest {

  private $rows;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testImport() {
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

    $this->runImporter('CRM_HRSampleData_Importer_OptionValue', $this->rows);

    $this->assertEquals(
      'Compassionate_Leave',
      $this->apiGet('OptionValue','name', 'Compassionate_Leave', ['option_group_id' => 'activity_type'])
    );
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
