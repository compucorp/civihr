<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as JobContractFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;

/**
 * Class CRM_HRSampleData_Importer_JobRolesTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_JobRolesTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  private $testContact;

  private $testJobContract;

  private $optionValues = [];

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate();

    $this->testJobContract = JobContractFabricator::fabricate(['contact_id' => $this->testContact['id']], ['period_start_date' => '2015-01-01']);

    $rolesOptionValues = [
      'cost_centres',
      'hrjc_department',
      'hrjc_level_type',
      'hrjc_location',
    ];
    foreach($rolesOptionValues as $group) {
      $this->optionValues[$group] = OptionValueFabricator::fabricate(['option_group_id' => $group]);
    }
  }

  public function testProcess() {
    $this->rows[] = [
      $this->testJobContract['id'],
      'Subject Head - Computer Basics',
      'West London',
      $this->optionValues['hrjc_department']['name'],
      $this->optionValues['hrjc_level_type']['name'],
      $this->optionValues['cost_centres']['name'],
      1,
      100,
      0,
      $this->testContact['id'],
      1,
      100,
      0,
      $this->optionValues['hrjc_location']['name'],
      '2014-01-01 18:30:00',
      '2019-06-29 18:30:00',
    ];

    $mapping = [
      ['contracts_mapping', $this->testJobContract['id']],
      ['contact_mapping', $this->testContact['id']],
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_JobRoles', $this->rows, $mapping);

    $jobRole = $this->apiGet('HrJobRoles', ['job_contract_id' => $this->testJobContract['id']]);

    $fieldsToTest = [
      'job_contract_id',
      'title',
      'cost_center_val_type',
      'percent_pay_cost_center',
      'amount_pay_cost_center',
      'funder',
      'funder_val_type',
      'percent_pay_funder',
      'amount_pay_funder',
      'start_date',
      'end_date',
    ];
    foreach($this->rows[0] as $index => $fieldName) {
      if (in_array($fieldName, $fieldsToTest)) {
        $this->assertEquals($this->rows[1][$index], $jobRole[$fieldName]);
      }
    }
  }

  private function importHeadersFixture() {
    return [
      'job_contract_id',
      'title',
      'region',
      'department',
      'level_type',
      'cost_center',
      'cost_center_val_type',
      'percent_pay_cost_center',
      'amount_pay_cost_center',
      'funder',
      'funder_val_type',
      'percent_pay_funder',
      'amount_pay_funder',
      'location',
      'start_date',
      'end_date',
    ];
  }

}
