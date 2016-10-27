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

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Please specify 'job_contract_id'
   */
  public function testGetCurrentDepartmentsExpectsJobContractId() {
    civicrm_api3('HrJobRoles', 'getcurrentdepartments');
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Please specify 'job_contract_id'
   */
  public function testGetCurrentDepartmentsExpectsJobContractIdToBeNotNull() {
    civicrm_api3('HrJobRoles', 'getcurrentdepartments', array('job_contract_id' => NULL));
  }
}
