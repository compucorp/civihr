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


}
