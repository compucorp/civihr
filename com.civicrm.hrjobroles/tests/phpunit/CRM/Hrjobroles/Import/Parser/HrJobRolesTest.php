<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_Hrjobroles_Test_Helper_OptionValuesHelper as OptionValuesHelper;

/**
 * Class CRM_Hrjobroles_Import_Parser_HrJobRolesTest
 *
 * @group headless
 */
class CRM_Hrjobroles_Import_Parser_HrJobRolesTest extends CRM_Hrjobroles_Test_BaseHeadlessTest {

  public function setUp() {
    $session = CRM_Core_Session::singleton();
    $session->set('dateTypes', 1);

    OptionValuesHelper::createSampleOptionGroupsAndValues();
  }

  public function testBasicImport() {
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $roleEntity = $this->findRole(['title' => $importParams['title']]);
    $this->assertEquals($importParams['title'], $roleEntity->title);
  }

  public function testImportWithoutMandatoryFields() {
    // run importer
    $importParams = [
      'title' => 'test import role'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);
  }

  public function testImportWithValidOptionValues() {
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role',
      'location' => 'amman',
      'hrjc_region' => 'south amman',
      'hrjc_role_department' => 'amman devs',
      'hrjc_level_type' => 'guru'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $roleEntity = $this->findRole(['title' => $importParams['title']]);
    $this->assertEquals($importParams['title'], $roleEntity->title);
    $this->assertEquals($importParams['location'], $roleEntity->location);
    $this->assertEquals($importParams['hrjc_region'], $roleEntity->region);
    $this->assertEquals($importParams['hrjc_role_department'], $roleEntity->department);
    $this->assertEquals($importParams['hrjc_level_type'], $roleEntity->level_type);
  }

  public function testImportWithInvalidOptionValues() {
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role2',
      'location' => 'amman',
      'hrjc_region' => 'southhggh ammandshhghg',
      'hrjc_role_department' => 'amman devs',
      'hrjc_level_type' => 'guru'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);;
  }

  public function testImportWithEmptyOptionValues() {
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role3',
      'location' => '',
      'hrjc_region' => '',
      'hrjc_role_department' => '',
      'hrjc_level_type' => ''
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $roleEntity = $this->findRole(['title' => $importParams['title']]);
    $this->assertEquals($importParams['title'], $roleEntity->title);
  }

  public function testImportFunderByIDAndPercent() {
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role',
      'location' => 'amman',
      'hrjc_region' => 'south amman',
      'hrjc_role_department' => 'amman devs',
      'hrjc_level_type' => 'guru',
      'funder' => $contactID,
      'hrjc_funder_val_type' => '%',
      'hrjc_role_percent_pay_funder' => '30'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $roleEntity = $this->findRole(['title' => $importParams['title']]);
    $this->assertEquals($importParams['title'], $roleEntity->title);
    $this->assertEquals($importParams['funder'], $roleEntity->funder);
    $this->assertEquals(1, $roleEntity->funder_val_type);
    $this->assertEquals($importParams['hrjc_role_percent_pay_funder'], $roleEntity->percent_pay_funder);
  }

  public function testImportFunderByIDAndAmount() {
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role',
      'location' => 'amman',
      'hrjc_region' => 'south amman',
      'hrjc_role_department' => 'amman devs',
      'hrjc_level_type' => 'guru',
      'funder' => $contactID,
      'hrjc_funder_val_type' => 'fixed',
      'hrjc_role_amount_pay_funder' => '30'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $roleEntity = $this->findRole(['title' => $importParams['title']]);
    $this->assertEquals($importParams['title'], $roleEntity->title);
    $this->assertEquals($importParams['funder'], $roleEntity->funder);
    $this->assertEquals(0, $roleEntity->funder_val_type);
    $this->assertEquals($importParams['hrjc_role_amount_pay_funder'], $roleEntity->amount_pay_funder);
  }

  public function testImportFunderByDisplayNameAndAmount() {
    $contactParams = [
      'first_name'=>'walter',
      'last_name'=>'white',
      'display_name' => 'walter white'
    ];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role',
      'location' => 'amman',
      'hrjc_region' => 'south amman',
      'hrjc_role_department' => 'amman devs',
      'hrjc_level_type' => 'guru',
      'funder' => $contactParams['display_name'],
      'hrjc_funder_val_type' => 'fixed',
      'hrjc_role_amount_pay_funder' => '30'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $roleEntity = $this->findRole(['title' => $importParams['title']]);
    $this->assertEquals($importParams['title'], $roleEntity->title);
    $this->assertEquals($contactID, $roleEntity->funder);
    $this->assertEquals(0, $roleEntity->funder_val_type);
    $this->assertEquals($importParams['hrjc_role_amount_pay_funder'], $roleEntity->amount_pay_funder);
  }

  public function testImportFunderWithInvalidID() {
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role',
      'location' => 'amman',
      'hrjc_region' => 'south amman',
      'hrjc_role_department' => 'amman devs',
      'hrjc_level_type' => 'guru',
      'funder' => 100000,
      'hrjc_funder_val_type' => 'fixed',
      'hrjc_role_amount_pay_funder' => '30'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);
  }

  public function testImportFunderWithInvalidDisplayName() {
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role',
      'location' => 'amman',
      'hrjc_region' => 'south amman',
      'hrjc_role_department' => 'amman devs',
      'hrjc_level_type' => 'guru',
      'funder' => 'wrong name',
      'hrjc_funder_val_type' => 'fixed',
      'hrjc_role_amount_pay_funder' => '30'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);
  }

  public function testImportFunderWithInvalidValueType() {
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role',
      'location' => 'amman',
      'hrjc_region' => 'south amman',
      'hrjc_role_department' => 'amman devs',
      'hrjc_level_type' => 'guru',
      'funder' => $contactID,
      'hrjc_funder_val_type' => 'wrong_type',
      'hrjc_role_amount_pay_funder' => '30'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);
  }

  public function testImportFunderWithInvalidPercentPay() {
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role',
      'location' => 'amman',
      'hrjc_region' => 'south amman',
      'hrjc_role_department' => 'amman devs',
      'hrjc_level_type' => 'guru',
      'funder' => $contactID,
      'hrjc_funder_val_type' => '%',
      'hrjc_role_percent_pay_funder' => 'should_be_number'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);
  }

  public function testImportFunderWithInvalidAmountPay() {
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role',
      'location' => 'amman',
      'hrjc_region' => 'south amman',
      'hrjc_role_department' => 'amman devs',
      'hrjc_level_type' => 'guru',
      'funder' => $contactID,
      'hrjc_funder_val_type' => 'fixed',
      'hrjc_role_percent_pay_funder' => 'should_be_number'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);
  }

  public function testImportFunderWithoutValueType() {
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = ContactFabricator::fabricate($contactParams)['id'];

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => date('Y-m-d', strtotime('-14 days'))]
    );

    // run importer
    $importParams = [
      'job_contract_id' => $contract['id'],
      'title' => 'test import role',
      'location' => 'amman',
      'hrjc_region' => 'south amman',
      'hrjc_role_department' => 'amman devs',
      'hrjc_level_type' => 'guru',
      'funder' => $contactID,
      'hrjc_role_percent_pay_funder' => '30'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);
  }

  private function runImport($params)  {
    $fields = array_keys($params);
    $values = array_values($params);
    $importObject = new CRM_Hrjobroles_Import_Parser_HrJobRoles($fields);
    $importObject->init();
    return $importObject->import(NULL, $values);
  }

  /**
   * Find and retrieve job role by any of its properties
   *
   * @param array $params
   *
   * @return \CRM_Hrjobroles_BAO_HrJobRoles|NULL
   */
  private function findRole($params)  {
    $default = NUll;
    return CRM_Hrjobroles_BAO_HrJobRoles::commonRetrieve(
      'CRM_Hrjobroles_BAO_HrJobRoles',
      $params,
      $default
    );
  }

}
