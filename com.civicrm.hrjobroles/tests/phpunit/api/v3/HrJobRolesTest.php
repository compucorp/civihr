<?php

require_once 'HrJobRolesTestBase.php';

class api_v3_HrJobRolesTest extends HrJobRolesTestBase {


  function setUp() {
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
  }

  /**
   * check with empty array
   */
  function testJobRoleCreateEmpty() {
    $this->callAPIFailure('HrJobRoles', 'create', array());
  }

  /**
   * check if required fields are not passed
   */
  function testJobRoleCreateWithoutJobContract() {
    $params = array(
      'title' => 'test role',
    );
    $this->callAPIFailure('HrJobRoles', 'create', $params);
  }

  /**
   *  Test creating new role with valid parameters
   */
  function testJobRoleCreate() {
    // create contact
    $contactParams = array('first_name'=>'walter', 'last_name'=>'white');
    $contactID = $this->individualCreate($contactParams);
    // create contract
    $contract = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));

    $params = ['job_contract_id' => $contract->id, 'title' => 'test role', 'sequential' => 1];
    $result = $this->callAPISuccess('HrJobRoles', 'create', $params);
    $this->assertEquals($result['values'][0]['job_contract_id'], $contract->id);
    $this->assertEquals($result['values'][0]['title'], 'test role');
  }

  /**
   *  Test creating new role with valid
   * (location, region, department, level, cost center ) fields
   *
   */
  function testJobRoleCreateWithValidOptionValues() {
    // create contact
    $contactParams = array('first_name'=>'walter', 'last_name'=>'white');
    $contactID = $this->individualCreate($contactParams);
    // create contract
    $contract = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));

    // create option group and values
    $this->createSampleOptionGroupsAndValues();

    $params = [
      'job_contract_id' => $contract->id,
      'title' => 'test role',
      'location' => 'amman',
      'region' => 'south amman',
      'department' => 'amman devs',
      'level_type' => 'guru',
      'cost_center' => 'abdali',
      'sequential' => 1
    ];
    $result = $this->callAPISuccess('HrJobRoles', 'create', $params);
    $this->assertEquals($result['values'][0]['location'], 'amman');
    $this->assertEquals($result['values'][0]['region'], 'south amman');
    $this->assertEquals($result['values'][0]['department'], 'amman devs');
    $this->assertEquals($result['values'][0]['level_type'], 'guru');
    $this->assertEquals($result['values'][0]['cost_center'], 'abdali');
  }

  /**
   *  Test creating new role with Invalid
   * (location, region, department, level, cost center ) fields
   *
   */
  function testJobRoleCreateWithInvalidOptionValues() {
    // create contact
    $contactParams = array('first_name'=>'walter', 'last_name'=>'white');
    $contactID = $this->individualCreate($contactParams);
    // create contract
    $contract = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));

    // create option group and values
    $this->createSampleOptionGroupsAndValues();

    // test invalid location
    $params = [
      'job_contract_id' => $contract->id,
      'title' => 'test role1',
      'location' => 'ammandadas',
    ];
    $this->callAPIFailure('HrJobRoles', 'create', $params);
    // test invalid region
    $params = [
      'job_contract_id' => $contract->id,
      'title' => 'test role2',
      'region' => 'soutsh ammansasdad',
    ];
    $this->callAPIFailure('HrJobRoles', 'create', $params);
    // test invalid department
    $params = [
      'job_contract_id' => $contract->id,
      'title' => 'test role3',
      'department' => 'ammadan devsdadada',
    ];
    $this->callAPIFailure('HrJobRoles', 'create', $params);
    // test invalid level type
    $params = [
      'job_contract_id' => $contract->id,
      'title' => 'test role4',
      'level_type' => 'gurfdsfsdfu',
    ];
    $this->callAPIFailure('HrJobRoles', 'create', $params);
    // test cost center
    $params = [
      'job_contract_id' => $contract->id,
      'title' => 'test role5',
      'cost_center' => 'abdfdsfsdali',
    ];
    $this->callAPIFailure('HrJobRoles', 'create', $params);
  }

}
