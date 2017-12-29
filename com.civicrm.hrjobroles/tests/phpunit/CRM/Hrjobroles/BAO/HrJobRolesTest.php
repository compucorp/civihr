<?php

use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_Hrjobroles_BAO_HrJobRoles as HrJobRoles;
use CRM_Hrjobroles_Test_Fabricator_HrJobRoles as HrJobRolesFabricator;

/**
 * Class CRM_Hrjobroles_BAO_HrJobRolesTest
 *
 * @group headless
 */
class CRM_Hrjobroles_BAO_HrJobRolesTest extends CRM_Hrjobroles_Test_BaseHeadlessTest {

  use HrJobRolesTestTrait;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 1;');
  }

  public function testCreateJobRoleWithBasicData() {
    // create role
    $roleParams = [
      'job_contract_id' => 1,
      'title' => 'test role'
    ];
    $jobRole = HrJobRolesFabricator::fabricate($roleParams);

    $roleEntity = HrJobRoles::findById($jobRole->id);
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
    HrJobRolesFabricator::fabricate($roleParams);
  }

  public function testCreateJobRoleWithOptionValueFields() {
    // create option group and values
    $this->createSampleOptionGroupsAndValues();

    // create role
    $roleParams = [
      'job_contract_id' => 1,
      'title' => 'test role',
      'location' => "amman",
      'region' => "south amman",
      'department' => "amman devs",
      'level_type' => "guru"
    ];
    $jobRole = HrJobRolesFabricator::fabricate($roleParams);

    $roleEntity = HrJobRoles::findById($jobRole->id);
    $this->assertEquals($roleParams['title'], $roleEntity->title);
  }

  public function testGetContactRoles() {
    $contactID = 1;

    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      [
        'period_start_date' => date('Y-m-d', strtotime('-3 years')),
        'period_end_date' => date('Y-m-d', strtotime('-1 years'))
      ]
    );

    $contract2 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      [
        'period_start_date' => date('Y-m-d', strtotime('-14 days')),
      ]
    );

    // create roles
    $roleParams1 = [
      'job_contract_id' => $contract1['id'],
      'title' => 'test role 1'
    ];
    $roleParams2 = [
      'job_contract_id' => $contract1['id'],
      'title' => 'test role 2'
    ];
    $roleParams3 = [
      'job_contract_id' => $contract2['id'],
      'title' => 'test role 3'
    ];

    HrJobRolesFabricator::fabricate($roleParams1);
    HrJobRolesFabricator::fabricate($roleParams2);
    HrJobRolesFabricator::fabricate($roleParams3);

    $this->assertCount(3, CRM_Hrjobroles_BAO_HrJobRoles::getContactRoles($contactID));
  }

  /**
   * If a contact has roles that have expired, the method should return only
   * the departments of the current job roles
   */
  public function testGetCurrentDepartmentsList() {
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

    $contractID = 1;

    foreach($jobRolesParams as $params) {
      HrJobRolesFabricator::fabricate(array(
        'job_contract_id' => $contractID,
        'department' => $params['department'],
        'start_date' => date('YmdHis', strtotime($params['start_offset'])),
        'end_date' => array_key_exists('end_offset', $params) ? date('YmdHis', strtotime($params['end_offset'])) : NULL
      ));
    }

    $departments = CRM_Hrjobroles_BAO_HrJobRoles::getCurrentDepartmentsList($contractID);

    $this->assertContains('Special Supervision', $departments);
    $this->assertContains('Special Development', $departments);
    $this->assertCount(2, $departments);
  }

  /**
   * If a contact has multiple roles in the same department, the method
   * should return a compact list to avoid duplicates
   */
  public function testGetCompactCurrentDepartmentsList() {
    $department = $this->createDepartment('special_investigation', 'Special Investigation')['value'];

    $contractID = 1;
    $params = [
      'job_contract_id' => $contractID,
      'department' => $department,
      'start_date' => date('Ymd')
    ];
    HrJobRolesFabricator::fabricate($params);
    HrJobRolesFabricator::fabricate($params);

    $departmentsList = CRM_Hrjobroles_BAO_HrJobRoles::getCurrentDepartmentsList($contractID);

    $this->assertContains('Special Investigation', $departmentsList);
    $this->assertCount(1, $departmentsList);
  }

  /**
   * Creates a new department option value
   *
   * @param string $name
   * @param string $label
   * @return Array $newDepartment
   * @throws \CiviCRM_API3_Exception
   */
  private function createDepartment($name, $label) {
    $newDepartment = civicrm_api3('OptionValue', 'create', array(
      'sequential' => '1',
      'option_group_id' => 'hrjc_department',
      'name' => $name,
      'label'=> $label,
    ))['values'][0];

    return $newDepartment;
  }
}
