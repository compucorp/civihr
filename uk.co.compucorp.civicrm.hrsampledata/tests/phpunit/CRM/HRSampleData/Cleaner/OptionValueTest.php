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

  public function testProcessWithOptionGroupNameAndDeleteOnUninstallOn() {
    $testOptionValue = OptionValueFabricator::fabricate(['option_group_id' => $this->testOptionGroup['name']]);

    $this->rows[] = [
      'name',
      $this->testOptionGroup['name'],
      $testOptionValue['name'],
      $testOptionValue['name'],
      $testOptionValue['name'],
      '',
      '',
      0,
      0,
      0,
      '',
      1,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_OptionValue', $this->rows);

    $optionValue = $this->apiGet(
      'OptionValue',
      ['name' => $testOptionValue['name'], 'option_group_id' => $this->testOptionGroup['name']]
    );
    $this->assertEmpty($optionValue);
  }

  public function testProcessWithOptionGroupNameAndDeleteOnUninstallOff() {
    $testOptionValue = OptionValueFabricator::fabricate(['option_group_id' => $this->testOptionGroup['name']]);

    $this->rows[] = [
      'name',
      $this->testOptionGroup['name'],
      $testOptionValue['name'],
      $testOptionValue['name'],
      $testOptionValue['name'],
      '',
      '',
      0,
      0,
      0,
      '',
      0,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_OptionValue', $this->rows);

    $optionValue = $this->apiGet(
      'OptionValue',
      ['name' => $testOptionValue['name'], 'option_group_id' => $this->testOptionGroup['name']]
    );
    $this->assertEquals($testOptionValue['name'], $optionValue['name']);
  }

  public function testProcessWithOptionGroupTitleAndDeleteOnUninstallOn() {
    $testOptionValue = OptionValueFabricator::fabricate(['option_group_id' => $this->testOptionGroup['name']]);

    $this->rows[] = [
      'title',
      $this->testOptionGroup['title'],
      $testOptionValue['name'],
      $testOptionValue['name'],
      $testOptionValue['name'],
      '',
      '',
      0,
      0,
      0,
      '',
      1,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_OptionValue', $this->rows);

    $optionValue = $this->apiGet(
      'OptionValue',
      ['name' => $testOptionValue['name'], 'option_group_id' => $this->testOptionGroup['name']]
    );
    $this->assertEmpty($optionValue);
  }

  public function testProcessWithOptionGroupTitleAndDeleteOnUninstallOff() {
    $testOptionValue = OptionValueFabricator::fabricate(['option_group_id' => $this->testOptionGroup['name']]);

    $this->rows[] = [
      'title',
      $this->testOptionGroup['title'],
      $testOptionValue['name'],
      $testOptionValue['name'],
      $testOptionValue['name'],
      '',
      '',
      0,
      0,
      0,
      '',
      0,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_OptionValue', $this->rows);

    $optionValue = $this->apiGet(
      'OptionValue',
      ['name' => $testOptionValue['name'], 'option_group_id' => $this->testOptionGroup['name']]
    );
    $this->assertEquals($testOptionValue['name'], $optionValue['name']);
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
      'delete_on_uninstall',
    ];
  }
}
