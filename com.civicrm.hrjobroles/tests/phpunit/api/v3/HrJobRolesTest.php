<?php

/**
 * Class api_v3_HrJobRolesTest
 *
 * @group headless
 */
class api_v3_HrJobRolesTest extends CRM_Hrjobroles_Test_BaseHeadlessTest {

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
