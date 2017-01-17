<?php

use CRM_HRCore_Test_Fabricator_OptionGroup as OptionGroupFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;

/**
 * Class CRM_HRSampleData_Cleaner_OptionValueTest
 *
 * @group headless
 */
class CRM_HRSampleData_Cleaner_OptionValueTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  private $testOptionGroup;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testOptionGroup = OptionGroupFabricator::fabricate();
  }

  public function testProcessWithOptionGroupName() {
    $optionValue = OptionValueFabricator::fabricate(['option_group_id' => $this->testOptionGroup['name']]);

    $testOptionValue = $this->apiGet(
      'OptionValue',
      ['name' => $optionValue['name'], 'option_group_id' => $this->testOptionGroup['name']]
    );

    $this->assertEquals($optionValue['name'], $testOptionValue['name']);

    $this->rows[] = [
      'name',
      $this->testOptionGroup['name'],
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
      ['name' => $optionValue['name'], 'option_group_id' => $this->testOptionGroup['name']]
    );
    $this->assertEmpty($optionValue);
  }

  public function testProcessWithOptionGroupTitle() {
    $optionValue = OptionValueFabricator::fabricate(['option_group_id' => $this->testOptionGroup['name']]);

    $testOptionValue = $this->apiGet(
      'OptionValue',
      ['name' => $optionValue['name'], 'option_group_id' => $this->testOptionGroup['name']]
    );

    $this->assertEquals($optionValue['name'], $testOptionValue['name']);

    $this->rows[] = [
      'title',
      $this->testOptionGroup['title'],
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
      ['name' => $optionValue['name'], 'option_group_id' => $this->testOptionGroup['name']]
    );
    $this->assertEmpty($optionValue);
  }

  private function importHeadersFixture() {
    return [
      'option_group_id_type',
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
