<?php

require_once EXTENSION_ROOT_DIR . 'CRM/CiviHRSampleData/Importer/OptionValue.php';

/**
 * Class CRM_CiviHRSampleData_Importer_OptionValueTest
 *
 * @group headless
 */
class CRM_CiviHRSampleData_Importer_OptionValueTest extends CRM_CiviHRSampleData_BaseImporterTest {

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

    $this->runImporter('CRM_CiviHRSampleData_Importer_OptionValue', $this->rows);

    $this->assertEquals(
      'Compassionate_Leave',
      $this->apiQuickGet('OptionValue','name', 'Compassionate_Leave', ['option_group_id' => 'activity_type'])
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
