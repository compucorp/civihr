<?php

/**
 * Class api_v3_LeavePeriodEntitlementTest
 *
 * @group headless
 */
class api_v3_LeavePeriodEntitlementTest extends BaseHeadlessTest {

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testOnGetRemainderDoesNotAcceptContactIdWithoutPeriodId() {
    civicrm_api3('LeavePeriodEntitlement', 'getremainder', ['contact_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testOnGetRemainderDoesNotAcceptPeriodIdWithoutContactId() {
    civicrm_api3('LeavePeriodEntitlement', 'getremainder', ['period_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetRemainderWhenAllParametersArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getremainder', ['entitlement_id'=> 1, 'period_id' => 1, 'contact_id'=>1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetRemainderWhenEntitlentIdAndPeriodIdArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getremainder', ['entitlement_id'=> 1, 'contact_id'=>1]);
  }

  public function testGetRemainderWhenContactAndPeriodIdArePassed() {
    $result = civicrm_api3('LeavePeriodEntitlement', 'getremainder', ['period_id'=> 1, 'contact_id'=>1]);
    $this->assertEmpty($result['values']);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testOnGetBreakdownDoesNotAcceptContactIdWithoutPeriodId() {
    civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['contact_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testOnGetBreakdownDoesNotAcceptPeriodIdWithoutContactId() {
    civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['period_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetBreakdownWhenAllParametersArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['entitlement_id'=> 1, 'period_id' => 1, 'contact_id'=>1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetBreakdownWhenEntitlentIdAndPeriodIdArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['entitlement_id'=> 1, 'contact_id'=>1]);
  }

  public function testGetBreakdownWhenContactAndPeriodIdArePassed() {
    $result = civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['period_id'=> 1, 'contact_id'=>1]);
    $this->assertEmpty($result['values']);
  }


}
