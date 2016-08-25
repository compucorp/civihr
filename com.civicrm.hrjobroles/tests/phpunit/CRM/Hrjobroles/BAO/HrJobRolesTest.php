<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_Hrjobroles_BAO_HrJobRolesTest
 *
 * @group headless
 */
class CRM_Hrjobroles_BAO_HrJobRolesTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  use HrJobRolesTestTrait;

  public function setUpHeadless() {
    // job contract is installed before job roles since job roles is depend on it
    // otherwise the installation of job roles will fail.
    return \Civi\Test::headless()
      ->install('org.civicrm.hrjobcontract')
      ->installMe(__DIR__)
      ->apply();
    $jobContractUpgrader = CRM_Hrjobcontract_Upgrader::instance();
    $jobContractUpgrader->install();
  }

  function testCreateJobRoleWithBasicData() {
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
  function testCreateJobRoleWithoutContractID() {
    $roleParams = [
      'title' => 'test role'
    ];
    $this->createJobRole($roleParams);
  }

  function testCreateJobRoleWithOptionValueFields() {
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

  function testGetContactRoles() {
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

  public function testGetDepartmentsList() {
    $contactID = $this->createContact(array("first_name" => "chrollo", "last_name" => "lucilfer"));
    $contract =  $this->createJobContract($contactID);
    $department = $this->createDepartment('special_investigation', 'Special Investigation');

    $this->createJobRole(array('job_contract_id' => $contract->id, 'department' => $department['value']));

    $departments = CRM_Hrjobroles_BAO_HrJobRoles::getDepartmentsList($contract->id);

    $this->assertContains('Special Investigation', $departments);
    $this->assertCount(1, $departments);
  }

}
