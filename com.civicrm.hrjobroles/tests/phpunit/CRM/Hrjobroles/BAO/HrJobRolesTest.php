<?php

require_once 'HrJobRolesTestBase.php';

class CRM_Hrjobroles_BAO_HrJobRolesTest extends HrJobRolesTestBase {


  function setUp() {
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
  }

  function testCreateJobRoleWithBasicData() {
    // create contact
    $contactParams = array('first_name'=>'walter', 'last_name'=>'white');
    $contactID = $this->individualCreate($contactParams);
    // create contract
    $contract = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));
    // create role
    $roleParams = array(
      'job_contract_id' => $contract->id,
      'title' => 'test role'
    );
    $jobRole = $this->createJobRole($roleParams);
    $this->assertNotEquals(NULL, $jobRole);
  }

  /**
   * @expectedException PEAR_Exception
   */
  function testCreateJobRoleWithoutContractID() {
    $roleParams = array(
      'title'
    );
    $this->createJobRole($roleParams);
  }

  function testCreateJobRoleWithOptionValueFields() {
    // create contact
    $contactParams = array('first_name'=>'walter', 'last_name'=>'white');
    $contactID = $this->individualCreate($contactParams);
    // create contract
    $contract = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));
    // create option group and values
    $this->createSampleOptionGroupsAndValues();
    $roleParams = array(
      'job_contract_id' => $contract->id,
      'title' => 'test role',
      'location' => "amman",
      'region' => "south amman",
      'department' => "amman devs",
      'level_type' => "guru",
      'cost_center' => "abdali"
    );
    // create role
    $jobRole = $this->createJobRole($roleParams);
    $this->assertNotEquals(NULL, $jobRole);
  }

  function testGetContactRoles() {
    // create contact
    $contactParams = array('first_name'=>'walter', 'last_name'=>'white');
    $contactID = $this->individualCreate($contactParams);
    // create contracts
    $contract1 = $this->createJobContract($contactID, date('Y-m-d', strtotime('-3 years')), date('Y-m-d', strtotime('-1 years')));
    $contract2 = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));
    // create roles
    $roleParams1 = array(
      'job_contract_id' => $contract1->id,
      'title' => 'test role 1'
    );
    $roleParams2 = array(
      'job_contract_id' => $contract1->id,
      'title' => 'test role 2'
    );
    $roleParams3 = array(
      'job_contract_id' => $contract2->id,
      'title' => 'test role 3'
    );
    $this->createJobRole($roleParams1);
    $this->createJobRole($roleParams2);
    $this->createJobRole($roleParams3);

    $this->assertCount(3, CRM_Hrjobroles_BAO_HrJobRoles::getContactRoles($contactID));
  }

  function testBuildDbOptions() {
    $params = ['name' => 'test_group', 'is_active' => 1];
    $optionGroup = CRM_Core_BAO_OptionGroup::add($params);
    for($i=1; $i<=3; $i++)  {
      $params = ['option_group_id' => $optionGroup->id, 'name' => "option_{$i}", 'value' => "option{$i}", 'label' => "option {$i}" ];
      CRM_Core_BAO_OptionValue::create($params);
    }
    $options = CRM_Hrjobroles_BAO_HrJobRoles::buildDbOptions('test_group');
    $this->assertCount(3, $options);
    $this->assertEquals('option 2', $options[1]['label']);
    $this->assertEquals('option2', $options[1]['value']);
  }

}
