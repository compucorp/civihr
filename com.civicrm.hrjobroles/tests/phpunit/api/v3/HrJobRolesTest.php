<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class api_v3_HrJobRolesTest
 *
 * @group headless
 */
class api_v3_HrJobRolesTest extends PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('org.civicrm.hrjobcontract')
      ->installMe(__DIR__)
      ->apply();

    $jobContractUpgrader = CRM_Hrjobcontract_Upgrader::instance();
    $jobContractUpgrader->install();
  }

  public function testGetCurrentDepartments() {
    $mock = $this->getMockBuilder(CRM_Hrjobroles_BAO_HrJobRoles::class)
                 ->setMethods(['getCurrentDepartmentsList'])
                 ->getMock();

    $mock->expects($this->once())
         ->method('getCurrentDepartmentsList')
         ->with($this->equalTo('34'));

    $result = civicrm_api3('HrJobRoles', 'getcurrentdepartments', array('job_contract_id' => '34'));
  }
}
