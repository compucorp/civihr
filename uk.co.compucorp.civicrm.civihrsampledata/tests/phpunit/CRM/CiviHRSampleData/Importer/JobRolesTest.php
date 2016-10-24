<?php

require_once EXTENSION_ROOT_DIR . 'CRM/CiviHRSampleData/Importer/JobRoles.php';

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as JobContractFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;

/**
 * Class CRM_CiviHRSampleData_Importer_JobRolesTest
 *
 * @group headless
 */
class CRM_CiviHRSampleData_Importer_JobRolesTest extends CRM_CiviHRSampleData_BaseImporterTest {

  private $rows;

  private $testContact;

  private $testJobContract;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->install('org.civicrm.hrjobcontract')
      ->install('com.civicrm.hrjobroles')
      ->apply();
  }

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate(['first_name' => 'chrollo', 'last_name' => 'lucilfer']);
    $this->testJobContract = JobContractFabricator::fabricate(['contact_id' => $this->testContact['id']], ['period_start_date' => '2015-01-01']);

    $rolesOptionValues = [
      'cost_centres',
      'hrjc_department',
      'hrjc_level_type',
      'hrjc_location',
    ];
    foreach($rolesOptionValues as $group) {
      OptionValueFabricator::fabricate($group);
    }
  }

  public function testImport() {
    $this->rows[] = [
      $this->testJobContract['id'],
      'Subject Head - Computer Basics',
      'West London',
      'test option',
      'test option',
      'test option',
      1,
      100,
      0,
      $this->testContact['id'],
      1,
      100,
      0,
      'test option',
      '2014-01-01 18:30:00',
      '2019-06-29 18:30:00',
    ];

    $mapping = [
      ['contracts_mapping', $this->testJobContract['id']],
      ['contact_mapping', $this->testContact['id']],
    ];

    $this->runImporter('CRM_CiviHRSampleData_Importer_JobRoles', $this->rows, $mapping);

    $this->assertEquals($this->testJobContract['id'], $this->apiQuickGet('HrJobRoles','job_contract_id', $this->testJobContract['id']));
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
