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

  public function testGetDepartmentsList() {
    $contactParams = array("first_name" => "chrollo", "last_name" => "lucilfer");
    $contactID =  $this->createContact($contactParams);
    $contract =  $this->createJobContract($contactID);

    $departmentID1 =  $this->createDepartment('special_investigation', 'Special Investigation');

    $params = array('job_contract_id' => $contract->id, 'department' => $departmentID1);
    $this->createJobRole($params);

    $departments = CRM_Hrjobroles_BAO_HrJobRoles::getDepartmentsList($contract->id);
    $this->assertContains('Special Investigation', $departments);
    $this->assertCount(1, $departments);
  }

}
