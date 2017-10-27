<?php

/**
 * Class CRM_Hrjobroles_BAO_HrJobRolesTest
 *
 * @group headless
 */
class CRM_Hrjobroles_BAO_HrJobRolesTest extends CRM_Hrjobroles_Test_BaseHeadlessTest {

  use HrJobRolesTestTrait;

  public function testCreateJobRoleWithBasicData() {
    // create contact
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = $this->createContact($contactParams);

    // create contract
    $contract = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));

    // create role
    $roleParams = [
      'job_contract_id' => $contract->id,
      'title' => 'test role'
    ];
    $jobRole = $this->createJobRole($roleParams);

    $roleEntity = $this->findRole(['id' => $jobRole->id]);
    $this->assertEquals($roleParams['title'], $roleEntity->title);
  }

  /**
   * @expectedException PEAR_Exception
   * @expectedExceptionMessage DB Error: unknown error
   */
  public function testCreateJobRoleWithoutContractID() {
    $roleParams = [
      'title' => 'test role'
    ];
    $this->createJobRole($roleParams);
  }

  public function testCreateJobRoleWithOptionValueFields() {
    // create contact
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = $this->createContact($contactParams);

    // create contract
    $contract = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));

    // create option group and values
    $this->createSampleOptionGroupsAndValues();

    // create role
    $roleParams = [
      'job_contract_id' => $contract->id,
      'title' => 'test role',
      'location' => "amman",
      'region' => "south amman",
      'department' => "amman devs",
      'level_type' => "guru"
    ];
    $jobRole = $this->createJobRole($roleParams);

    $roleEntity = $this->findRole(['id' => $jobRole->id]);
    $this->assertEquals($roleParams['title'], $roleEntity->title);
  }

  public function testGetContactRoles() {
    // create contact
    $contactParams = ['first_name'=>'walter', 'last_name'=>'white'];
    $contactID = $this->createContact($contactParams);

    // create contracts
    $contract1 = $this->createJobContract($contactID, date('Y-m-d', strtotime('-3 years')), date('Y-m-d', strtotime('-1 years')));
    $contract2 = $this->createJobContract($contactID, date('Y-m-d', strtotime('-14 days')));

    // create roles
    $roleParams1 = [
      'job_contract_id' => $contract1->id,
      'title' => 'test role 1'
    ];
    $roleParams2 = [
      'job_contract_id' => $contract1->id,
      'title' => 'test role 2'
    ];
    $roleParams3 = [
      'job_contract_id' => $contract2->id,
      'title' => 'test role 3'
    ];

    $this->createJobRole($roleParams1);
    $this->createJobRole($roleParams2);
    $this->createJobRole($roleParams3);

    $this->assertCount(3, CRM_Hrjobroles_BAO_HrJobRoles::getContactRoles($contactID));
  }

  /**
   * If a contact has roles that have expired, the method should return only
   * the departments of the current job roles
   */
  public function testGetCurrentDepartmentsList() {
    $contactID = $this->createContact(array("first_name" => "chrollo", "last_name" => "lucilfer"));
    $contract =  $this->createJobContract($contactID);

    $departments = [
      $this->createDepartment('special_investigation', 'Special Investigation')['value'],
      $this->createDepartment('special_supervision', 'Special Supervision')['value'],
      $this->createDepartment('special_development', 'Special Development')['value']
    ];

    $jobRolesParams = array(
      array('department' => $departments[0], 'start_offset' => '-3 months', 'end_offset' => '-1 month'),
      array('department' => $departments[1], 'start_offset' => '-1 month', 'end_offset' => '+5 months'),
      array('department' => $departments[2], 'start_offset' => '-1 week')
    );

    foreach($jobRolesParams as $params) {
      $this->createJobRole(array(
        'job_contract_id' => $contract->id,
        'department' => $params['department'],
        'start_date' => date('YmdHis', strtotime($params['start_offset'])),
        'end_date' => array_key_exists('end_offset', $params) ? date('YmdHis', strtotime($params['end_offset'])) : NULL
      ));
    }

    $departments = CRM_Hrjobroles_BAO_HrJobRoles::getCurrentDepartmentsList($contract->id);

    $this->assertContains('Special Supervision', $departments);
    $this->assertContains('Special Development', $departments);
    $this->assertCount(2, $departments);
  }

  /**
   * If a contact has multiple roles in the same department, the method
   * should return a compact list to avoid duplicates
   */
  public function testGetCompactCurrentDepartmentsList() {
    $contactID = $this->createContact(array("first_name" => "chrollo", "last_name" => "lucilfer"));
    $contract =  $this->createJobContract($contactID);

    $department = $this->createDepartment('special_investigation', 'Special Investigation')['value'];

    $params = [
      'job_contract_id' => $contract->id,
      'department' => $department,
      'start_date' => date('Ymd')
    ];
    $this->createJobRole($params);
    $this->createJobRole($params);

    $departmentsList = CRM_Hrjobroles_BAO_HrJobRoles::getCurrentDepartmentsList($contract->id);

    $this->assertContains('Special Investigation', $departmentsList);
    $this->assertCount(1, $departmentsList);
  }
}
