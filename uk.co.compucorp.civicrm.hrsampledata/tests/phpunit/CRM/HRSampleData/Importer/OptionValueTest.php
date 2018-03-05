<?php

use CRM_HRCore_Test_Fabricator_OptionGroup as OptionGroupFabricator;

/**
 * Class CRM_HRSampleData_Importer_OptionValueTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_OptionValueTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  private $testOptionGroup;
  private $fieldsToIgnoreInAssertion = [
    'option_group_id',
    'component_id',
    'option_group_id_type'
  ];

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testOptionGroup = OptionGroupFabricator::fabricate();
  }

  public function testProcessWithOptionGroupName() {
    $this->rows[] = [
      'name',
      $this->testOptionGroup['name'],
      'Compassionate_Leave',
      'Compassionate_Leave',
      'Timesheet',
      1,
      0,
      0,
      0,
      ''
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_OptionValue', $this->rows);

    $optionValue = $this->apiGet(
      'OptionValue',
      ['option_group_id' => $this->testOptionGroup['name'], 'name' => 'Compassionate_Leave']
    );

    $this->assertEntityEqualsToRows($this->rows, $optionValue, $this->fieldsToIgnoreInAssertion);
  }

  public function testProcessWithOptionGroupTitle() {
    $this->rows[] = [
      'title',
      $this->testOptionGroup['title'],
      'Compassionate_Leave',
      'Compassionate_Leave',
      'Timesheet',
      1,
      0,
      0,
      0,
      ''
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_OptionValue', $this->rows);

    $optionValue = $this->apiGet(
      'OptionValue',
      ['option_group_id' => $this->testOptionGroup['name'], 'name' => 'Compassionate_Leave']
    );

    $this->assertEntityEqualsToRows($this->rows, $optionValue, $this->fieldsToIgnoreInAssertion);
  }

  private function importHeadersFixture() {
    return [
      'option_group_id_type',
      'option_group_id',
      'label',
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
