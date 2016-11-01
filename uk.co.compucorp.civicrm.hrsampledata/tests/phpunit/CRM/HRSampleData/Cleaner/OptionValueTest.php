<?php

use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;

/**
 * Class CRM_HRSampleData_Cleaner_OptionValueTest
 *
 * @group headless
 */
class CRM_HRSampleData_Cleaner_OptionValueTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testProcess() {
    $optionValue = OptionValueFabricator::fabricate(['option_group_id' => 'activity_type']);
    $testOptionValue = $this->apiGet(
      'OptionValue',
      ['name' => $optionValue['name'], 'option_group_id' => 'activity_type']
    );
    $this->assertEquals($optionValue['name'], $testOptionValue['name']);

    $this->rows[] = [
      'activity_type',
      $optionValue['name'],
      $optionValue['name'],
      $optionValue['name'],
      '',
      '',
      0,
      0,
      0,
      '',
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_OptionValue', $this->rows);

    $optionValue = $this->apiGet(
      'OptionValue',
      ['name' => $optionValue['name'], 'option_group_id' => 'activity_type']
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
