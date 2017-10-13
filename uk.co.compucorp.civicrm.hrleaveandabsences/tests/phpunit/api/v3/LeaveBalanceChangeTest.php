<?php

use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;

/**
 * Class api_v3_LeaveBalanceChangeTest
 *
 * @group headless
 */
class api_v3_LeaveBalanceChangeTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;

  public function setUp() {
    // In order to make tests simpler, we disable the foreign key checks,
    // as a way to allow the creation of brought forward records related
    // to a non-existing entitlement
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");
  }

  /**
   * A very basic test, just to make sure that the API will call the right
   * BAO method
   */
  public function testCreateExpiryRecords() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-30 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 2,
    ]);

    $periodEntitlement3 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 3,
    ]);

    $result = civicrm_api3('LeaveBalanceChange', 'create_expiry_records');
    $this->assertEquals(0, $result);

    $this->createBroughtForwardBalanceChange($periodEntitlement1->id, 2, date('YmdHis', strtotime('-1 day')));
    $this->createBroughtForwardBalanceChange($periodEntitlement2->id, 5, date('YmdHis'));
    $this->createBroughtForwardBalanceChange($periodEntitlement3->id, 3.5, date('YmdHis', strtotime('-2 days')));

    // Should create two records: one for the entitlement 1 and another one
    // for entitlement 3. The brought forward for entitlement 2 has not expired
    $result = civicrm_api3('LeaveBalanceChange', 'create_expiry_records');
    $this->assertEquals(2, $result);
  }
}
