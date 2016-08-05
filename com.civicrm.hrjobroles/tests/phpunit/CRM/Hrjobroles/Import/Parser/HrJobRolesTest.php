<?php

require_once 'HrJobRolesTestBase.php';

class CRM_Hrjobroles_Import_Parser_HrJobRolesTest extends HrJobRolesTestBase {


  function setUp() {
    parent::setUp();
    $session = CRM_Core_Session::singleton();
    $session->set('dateTypes', 1);
    $this->createSampleOptionGroupsAndValues();
  }

  function tearDown() {
    parent::tearDown();
  }

  function testBasicImport() {
    // create contact
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = $this->individualCreate($contactParams);

    // create contract
    $contract = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));

    // run importer
    $importParams = [
      'job_contract_id' => $contract->id,
      'title' => 'test import role'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $roleEntity = $this->findRole(['title' => $importParams['title']]);
    $this->assertEquals($importParams['title'], $roleEntity->title);
  }

  function testImportWithoutMandatoryFields() {
    // run importer
    $importParams = [
      'title' => 'test import role'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);
  }

  function testImportWithValidOptionValues() {
    // create contact
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = $this->individualCreate($contactParams);

    // create contract
    $contract = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));

    // run importer
    $importParams = [
      'job_contract_id' => $contract->id,
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

  function testImportWithInvalidOptionValues() {
    // create contact
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = $this->individualCreate($contactParams);

    // create contract
    $contract = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));

    // run importer
    $importParams = [
      'job_contract_id' => $contract->id,
      'title' => 'test import role2',
      'location' => 'amman',
      'hrjc_region' => 'southhggh ammandshhghg',
      'hrjc_role_department' => 'amman devs',
      'hrjc_level_type' => 'guru'
    ];
    $importResponse = $this->runImport($importParams);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);;
  }

  function testImportWithEmptyOptionValues() {
    // create contact
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = $this->individualCreate($contactParams);

    // create contract
    $contract = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));

    // run importer
    $importParams = [
      'job_contract_id' => $contract->id,
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

  private function runImport($params)  {
    $fields = array_keys($params);
    $values = array_values($params);
    $importObject = new CRM_Hrjobroles_Import_Parser_HrJobRoles($fields);
    $importObject->init();
    return $importObject->import(NULL, $values);
  }

}
