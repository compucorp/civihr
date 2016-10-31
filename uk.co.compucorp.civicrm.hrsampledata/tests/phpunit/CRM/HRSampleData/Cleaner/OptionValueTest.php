<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/OptionValue.php';

use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;

/**
 * Class CRM_HRSampleData_Cleaner_OptionValueTest
 *
 * @group headless
 */
class CRM_HRSampleData_Cleaner_OptionValueTest extends CRM_HRSampleData_BaseImporterTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testIterate() {
    OptionValueFabricator::fabricate(['option_group_id' => 'activity_type']);
    $testOptionValue = $this->apiGet(
      'OptionValue',
      ['name' => 'test option', 'option_group_id' => 'activity_type']
    );
    $this->assertEquals('test option', $testOptionValue['name']);

    $this->rows[] = [
      'activity_type',
      'test option',
      'test option',
      'test option',
      '',
      '',
      0,
      0,
      0,
      '',
    ];

    $this->runIterator('CRM_HRSampleData_Cleaner_OptionValue', $this->rows);

    $optionValue = $this->apiGet(
      'OptionValue',
      ['name' => 'test option', 'option_group_id' => 'activity_type']
    );
    $this->assertEmpty($optionValue);
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
