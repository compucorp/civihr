<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;

/**
 * Class api_v3_LeavePeriodEntitlementTest
 *
 * @group headless
 */
class api_v3_LeavePeriodEntitlementTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;

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
  public function testGetRemainderWhenEntitlementIdAndPeriodIdArePassed() {
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
    civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['entitlement_id' => 1, 'period_id' => 1, 'contact_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetBreakdownWhenEntitlementIdAndPeriodIdArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['entitlement_id' => 1, 'contact_id' => 1]);
  }

  public function testGetBreakdownWhenContactAndPeriodIdArePassed() {
    $result = civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['period_id' => 1, 'contact_id' => 1]);
    $this->assertEmpty($result['values']);
  }

  public function testGetBreakdownWhenOnlyEntitlementIdIsPassed() {

    $absencePeriod = AbsencePeriodFabricator::fabricate();
    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id
    ]);
    $result = civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['entitlement_id' => $periodEntitlement->id]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'id' => 0,
      'values' => [
        [
          'id' => $periodEntitlement->id,
          'breakdown' => []
        ]
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetEntitlementDoesNotAcceptContactIdWithoutPeriodId() {
    civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', ['contact_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetEntitlementDoesNotAcceptPeriodIdWithoutContactId() {
    civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', ['period_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetEntitlementWhenAllParametersArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', ['entitlement_id'=> 1, 'period_id' => 1, 'contact_id'=>1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetEntitlementWhenEntitlementIdAndPeriodIdArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', ['entitlement_id'=> 1, 'contact_id'=>1]);
  }

  public function testGetEntitlementCanReturnTheEntitlementsForAContactInAPeriod() {
    $contact = ContactFabricator::fabricate();

    $type1 = AbsenceTypeFabricator::fabricate();
    $type2 = AbsenceTypeFabricator::fabricate();
    $type3 = AbsenceTypeFabricator::fabricate();

    $period1 = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $period2 = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2017-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2017-12-31')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type1->id,
      'period_id' => $period1->id
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type2->id,
      'period_id' => $period1->id
    ]);

    $periodEntitlement3 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type2->id,
      'period_id' => $period2->id
    ]);

    $periodEntitlement4 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type3->id,
      'period_id' => $period2->id
    ]);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', [
      'contact_id' => $contact['id'],
      'period_id' => $period1->id
    ]);

    //For the period 1, the contact only has entitlements for Types 1 and 2
    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 2,
      'values' => [
        [
          'id' => $periodEntitlement1->id,
          'entitlement' => 0,
        ],
        [
          'id' => $periodEntitlement2->id,
          'entitlement' => 0,
        ]
      ]
    ];

    $this->assertEquals($expected, $result);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', [
      'contact_id' => $contact['id'],
      'period_id' => $period2->id
    ]);

    //For the period 1, the contact only has entitlements for Types 2 and 3
    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 2,
      'values' => [
        [
          'id' => $periodEntitlement3->id,
          'entitlement' => 0,
        ],
        [
          'id' => $periodEntitlement4->id,
          'entitlement' => 0,
        ]
      ]
    ];

    $this->assertEquals($expected, $result);
  }

  public function testGetEntitlementCanReturnTheEntitlementsForASpecificLeavePeriodEntitlement() {
    $contact = ContactFabricator::fabricate();

    $type1 = AbsenceTypeFabricator::fabricate();
    $type2 = AbsenceTypeFabricator::fabricate();

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type1->id,
      'period_id' => $period->id
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type2->id,
      'period_id' => $period->id
    ]);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', [
      'entitlement_id' => $periodEntitlement1->id,
    ]);

    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        [
          'id' => $periodEntitlement1->id,
          'entitlement' => 0,
        ]
      ]
    ];

    $this->assertEquals($expected, $result);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', [
      'entitlement_id' => $periodEntitlement2->id,
    ]);

    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        [
          'id' => $periodEntitlement2->id,
          'entitlement' => 0,
        ]
      ]
    ];

    $this->assertEquals($expected, $result);
  }

  public function testGetEntitlementCanReturnTheEntitlementsForLeavePeriodEntitlements() {
    $contact = ContactFabricator::fabricate();

    $type1 = AbsenceTypeFabricator::fabricate();
    $type2 = AbsenceTypeFabricator::fabricate();

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type1->id,
      'period_id' => $period->id
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type2->id,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement1->id, 20);
    $this->createPublicHolidayBalanceChange($periodEntitlement1->id, 5);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', [
      'entitlement_id' => $periodEntitlement1->id,
    ]);

    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        [
          'id' => $periodEntitlement1->id,
          'entitlement' => 25,
        ]
      ]
    ];

    $this->assertEquals($expected, $result);

    $this->createOverriddenBalanceChange($periodEntitlement2->id, 50);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', [
      'entitlement_id' => $periodEntitlement2->id,
    ]);

    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        [
          'id' => $periodEntitlement2->id,
          'entitlement' => 50,
        ]
      ]
    ];

    $this->assertEquals($expected, $result);
  }
}
