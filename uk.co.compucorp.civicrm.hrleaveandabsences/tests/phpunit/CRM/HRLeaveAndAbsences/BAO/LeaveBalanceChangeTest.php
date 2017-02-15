<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;
use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern as ContactWorkPattern;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange as LeaveBalanceChangeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_TOILRequest as TOILRequestFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChangeTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChangeTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_ContractHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeavePeriodEntitlementHelpersTrait;

  public function setUp() {
    // In order to make tests simpler, we disable the foreign key checks,
    // as a way to allow the creation of leave request records related
    // to a non-existing leave period entitlement
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");
  }

  /**
   * @expectedException PEAR_Exception
   * @expectedExceptionMessage DB Error: already exists
   */
  public function testThereCannotBeMoreThanOneExpiredRecordForEachBalanceChange() {
    $entitlement = $this->createLeavePeriodEntitlement();

    $balanceChangeToExpire = LeaveBalanceChange::create([
      'entitlement_id' => $entitlement->id,
      'type_id' => 1,
      'amount' => 3,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01')
    ]);

    $this->assertNotEmpty($balanceChangeToExpire->id);

    $expiryBalanceChange = LeaveBalanceChange::create([
      'entitlement_id' => $entitlement->id,
      'type_id' => 1,
      'amount' => -3,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'expired_balance_change_id' => $balanceChangeToExpire->id
    ]);

    $this->assertNotEmpty($expiryBalanceChange->id);

    // A second expiry record should not be allowed to be created
    LeaveBalanceChange::create([
      'entitlement_id' => $entitlement->id,
      'type_id' => 1,
      'amount' => -3,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'expired_balance_change_id' => $balanceChangeToExpire->id
    ]);
  }

  public function testGetBalanceForEntitlementCanSumAllTheBalanceChangesForAGivenEntitlement() {
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();

    LeaveBalanceChange::create([
      'source_id' => $entitlement->id,
      'source_type' => 'entitlement',
      'type_id' => 1,
      'amount' => 4.3
    ]);

    $this->assertEquals(4.3, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    LeaveBalanceChange::create([
      'source_id' => $entitlement->id,
      'source_type' => 'entitlement',
      'type_id' => 2,
      'amount' => 2
    ]);

    $this->assertEquals(6.3, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    LeaveBalanceChange::create([
      'source_id' => $entitlement->id,
      'source_type' => 'entitlement',
      'type_id' => 2,
      'amount' => -3.5
    ]);

    $this->assertEquals(2.8, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    LeaveBalanceChange::create([
      'source_id' => $entitlement->id,
      'source_type' => 'entitlement',
      'type_id' => 2,
      'amount' => -2
    ]);

    $this->assertEquals(0.8, LeaveBalanceChange::getBalanceForEntitlement($entitlement));
  }

  public function testBalanceForEntitlementCanSumOnlyTheBalanceChangesForLeaveRequestWithSpecificStatuses() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+10 days')
    );

    $this->createLeaveBalanceChange($entitlement->id, 10);
    $this->assertEquals(10, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Cancelled'],
      date('Y-m-d', strtotime('-10 days'))
    );

    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Rejected'],
      date('Y-m-d', strtotime('-9 days'))
    );

    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d', strtotime('-8 days'))
    );

    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Admin Approved'],
      date('Y-m-d', strtotime('-7 days'))
    );

    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Waiting Approval'],
      date('Y-m-d', strtotime('-6 days'))
    );

    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['More Information Requested'],
      date('Y-m-d', strtotime('-6 days'))
    );

    // Including all the balance changes
    $this->assertEquals(4, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    // Only Include balance changes from approved leave requests
    $statusesToInclude = [
      $leaveRequestStatuses['Approved'],
      $leaveRequestStatuses['Admin Approved'],
    ];
    $this->assertEquals(8, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statusesToInclude));

    // Only Include balance changes from cancelled/rejected leave requests
    $statusesToInclude = [
      $leaveRequestStatuses['Cancelled'],
      $leaveRequestStatuses['Rejected'],
    ];
    $this->assertEquals(8, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statusesToInclude));

    // Only Include balance changes from leave requests waiting approval
    $statusesToInclude = [ $leaveRequestStatuses['Waiting Approval'] ];
    $this->assertEquals(9, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statusesToInclude));

    // Only Include balance changes from leave requests waiting for more information
    $statusesToInclude = [ $leaveRequestStatuses['More Information Requested'] ];
    $this->assertEquals(9, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statusesToInclude));
  }

  public function testBalanceForEntitlementIncludesExpiredBroughtForwardAndTOIL() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+10 days')
    );

    $this->createLeaveBalanceChange($entitlement->id, 10);
    $this->assertEquals(10, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    $this->createExpiredBroughtForwardBalanceChange($entitlement->id, 10, 5);

    // Including all the balance changes
    // 10 (Leave) + 10 (Brought Forward) - 5 (Expired Brought Forward)
    $this->assertEquals(15, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-5 days'),
      CRM_Utils_Date::processDate('-5 days'),
      3,
      CRM_Utils_Date::processDate('-1 days'),
      1
    );

    // 10 (Leave) + 10 (Brought Forward) - 5 (Expired Brought Forward) + 3 (Accrued TOIL) - 1 (Expired TOIL)
    $this->assertEquals(17, LeaveBalanceChange::getBalanceForEntitlement($entitlement));
  }

  public function testBalanceForEntitlementCanIncludeOnlyTheExpiredBalanceChanges() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+10 days')
    );

    $expiredOnly = true;
    $this->createLeaveBalanceChange($entitlement->id, 10);
    $this->assertEquals(0, LeaveBalanceChange::getBalanceForEntitlement($entitlement, [], $expiredOnly));

    $this->createExpiredBroughtForwardBalanceChange($entitlement->id, 10, 5);

    // - 5 (Only the expired Brought Forward)
    $this->assertEquals(-5, LeaveBalanceChange::getBalanceForEntitlement($entitlement, [], $expiredOnly));

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-5 days'),
      CRM_Utils_Date::processDate('-5 days'),
      3,
      CRM_Utils_Date::processDate('-1 days'),
      1
    );

    // -5 (Expired Brought Forward) - 1 (Expired TOIL)
    $this->assertEquals(-6, LeaveBalanceChange::getBalanceForEntitlement($entitlement, [], $expiredOnly));
  }

  public function testBalanceForEntitlementCanIncludeOnlyTheExpiredBalanceChangesForTOILRequestsWithSpecificStatuses() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+10 days')
    );

    $expiredOnly = true;
    $this->createLeaveBalanceChange($entitlement->id, 10);
    $this->assertEquals(0, LeaveBalanceChange::getBalanceForEntitlement($entitlement, [], $expiredOnly));

    $this->createExpiredBroughtForwardBalanceChange($entitlement->id, 10, 5);

    // - 5 (Only the expired Brought Forward)
    $this->assertEquals(-5, LeaveBalanceChange::getBalanceForEntitlement($entitlement, [], $expiredOnly));

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-5 days'),
      CRM_Utils_Date::processDate('-5 days'),
      3,
      CRM_Utils_Date::processDate('-1 day'),
      1
    );

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Cancelled'],
      CRM_Utils_Date::processDate('-3 days'),
      CRM_Utils_Date::processDate('-3 days'),
      3,
      CRM_Utils_Date::processDate('-1 day'),
      3
    );

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Waiting Approval'],
      CRM_Utils_Date::processDate('-1 days'),
      CRM_Utils_Date::processDate('-1 days'),
      5,
      CRM_Utils_Date::processDate('-1 day'),
      2
    );

    $statuses = [$leaveRequestStatuses['Approved']];
    // -5 (Expired Brought Forward) - 1 (Expired Approved TOIL)
    $this->assertEquals(-6, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statuses, $expiredOnly));

    $statuses = [$leaveRequestStatuses['Approved'], $leaveRequestStatuses['Waiting Approval']];
    // -5 (Expired Brought Forward) - 1 (Expired Approved TOIL) - 2 (Expired Waiting Approval TOIL)
    $this->assertEquals(-8, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statuses, $expiredOnly));

    $statuses = [$leaveRequestStatuses['Cancelled'], $leaveRequestStatuses['Waiting Approval']];
    // -5 (Expired Brought Forward) - 3 (Expired Cancelled TOIL) -2 (Expired Waiting Approval TOIL)
    $this->assertEquals(-10, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statuses, $expiredOnly));

    $statuses = [$leaveRequestStatuses['Waiting Approval']];
    // -5 (Expired Brought Forward) - 2 (Expired Waiting Approval TOIL)
    $this->assertEquals(-7, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statuses, $expiredOnly));

    $statuses = [$leaveRequestStatuses['Cancelled']];
    // -5 (Expired Brought Forward) - 3 (Expired Cancelled TOIL)
    $this->assertEquals(-8, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statuses, $expiredOnly));

    $statuses = [$leaveRequestStatuses['Cancelled'], $leaveRequestStatuses['Approved']];
    // -5 (Expired Brought Forward) - 3 (Expired Cancelled TOIL) - 1 (Expired Approved TOIL)
    $this->assertEquals(-9, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statuses, $expiredOnly));
  }

  public function testTheEntitlementBreakdownReturnsThePositiveLeaveBroughtForwardAndPublicHolidayChangesWithoutASource() {
    $entitlement = $this->createLeavePeriodEntitlement();

    $this->createLeaveBalanceChange($entitlement->id, 10);
    $balanceChanges = LeaveBalanceChange::getBreakdownBalanceChangesForEntitlement($entitlement->id);
    $this->assertCount(1, $balanceChanges);
    $this->assertEquals(10, $balanceChanges[0]->amount);

    // Even if the days brought forward have expired, they're still
    // part of the breakdown (the expiry record is not returned though)
    $this->createExpiredBroughtForwardBalanceChange($entitlement->id, 4, 2);
    $balanceChanges = LeaveBalanceChange::getBreakdownBalanceChangesForEntitlement($entitlement->id);
    $this->assertCount(2, $balanceChanges);
    $this->assertEquals(10, $balanceChanges[0]->amount);
    $this->assertEquals(4, $balanceChanges[1]->amount);

    $this->createPublicHolidayBalanceChange($entitlement->id, 8);
    $balanceChanges = LeaveBalanceChange::getBreakdownBalanceChangesForEntitlement($entitlement->id);
    $this->assertCount(3, $balanceChanges);
    $this->assertEquals(10, $balanceChanges[0]->amount);
    $this->assertEquals(4, $balanceChanges[1]->amount);
    $this->assertEquals(8, $balanceChanges[2]->amount);
  }

  public function testTheEntitlementBreakdownSumsOnlyThePositiveLeaveBroughtForwardAndPublicHolidayChangesWithoutASource() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlement();

    $this->createLeaveBalanceChange($entitlement->id, 23.5);
    $breakdownBalance = LeaveBalanceChange::getBreakdownBalanceForEntitlement($entitlement->id);
    $this->assertEquals(23.5, $breakdownBalance);

    // Even if the days brought forward have expired, they're still
    // part of the breakdown. The expired 2 days won't be included in
    // the sum
    $this->createExpiredBroughtForwardBalanceChange($entitlement->id, 4, 2);
    $breakdownBalance = LeaveBalanceChange::getBreakdownBalanceForEntitlement($entitlement->id);
    $this->assertEquals(27.5, $breakdownBalance);

    $this->createPublicHolidayBalanceChange($entitlement->id, 8);
    $breakdownBalance = LeaveBalanceChange::getBreakdownBalanceForEntitlement($entitlement->id);
    $this->assertEquals(35.5, $breakdownBalance);

    // This will deduct 11 days, but the respective balance changes
    // won't be returned as part of the breakdown
    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    $breakdownBalance = LeaveBalanceChange::getBreakdownBalanceForEntitlement($entitlement->id);
    $this->assertEquals(35.5, $breakdownBalance);
  }

  public function testLeaveRequestBalanceForEntitlementOnlySumBalanceChangesCreatedByLeaveRequestsWithSpecificStatus() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime(),
      new DateTime('+20 days')
    );

    $this->createLeaveBalanceChange($entitlement->id, 23.5);
    $this->createBroughtForwardBalanceChange($entitlement->id, 4);
    $this->createPublicHolidayBalanceChange($entitlement->id, 8);

    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $leaveRequestBalanceChange);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Admin Approved'],
      date('Y-m-d', strtotime('+11 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Cancelled'],
      date('Y-m-d', strtotime('+12 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Rejected'],
      date('Y-m-d', strtotime('+13 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Waiting Approval'],
      date('Y-m-d', strtotime('+14 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['More Information Requested'],
      date('Y-m-d', strtotime('+15 days'))
    );

    // Balance include all the leave requests
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(-16, $leaveRequestBalanceChange);

    // Balance including only approved leave requests
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [ $leaveRequestStatuses['Approved'], $leaveRequestStatuses['Admin Approved'] ]
    );
    $this->assertEquals(-12, $leaveRequestBalanceChange);

    // Balance including only cancelled or rejected leave requests
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [ $leaveRequestStatuses['Cancelled'], $leaveRequestStatuses['Rejected'] ]
    );
    $this->assertEquals(-2, $leaveRequestBalanceChange);

    // Balance including only leave requests waiting approval
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [ $leaveRequestStatuses['Waiting Approval'] ]
    );
    $this->assertEquals(-1, $leaveRequestBalanceChange);

    // Balance including only leave requests waiting for more information
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [ $leaveRequestStatuses['More Information Requested'] ]
    );
    $this->assertEquals(-1, $leaveRequestBalanceChange);
  }

  public function testLeaveRequestBalanceForEntitlementCanSumBalanceChangesCreatedByLeaveRequestsUpToASpecificDate() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime(),
      new DateTime('+20 days')
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    // Balance including the whole leave request
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(-11, $balance);

    // Balance including only the first day of leave request
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [],
      new Datetime('now')
    );
    $this->assertEquals(-1, $balance);

    // Balance including only the two first days of leave request
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [],
      new Datetime('+1 day')
    );
    $this->assertEquals(-2, $balance);

    // Balance including only the five first days of leave request
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [],
      new Datetime('+4 days')
    );
    $this->assertEquals(-5, $balance);

    // The limit date is after the leave request end date, so the whole request
    // will be included
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [],
      new Datetime('+12 days')
    );
    $this->assertEquals(-11, $balance);
  }

  public function testLeaveRequestBalanceForEntitlementCanSumBalanceChangesCreatedByLeaveRequestsOnASpecificDateRange() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime(),
      new DateTime('+20 days')
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    // Balance including the whole leave request
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(-11, $balance);

    // Balance including only the third and fourth days of leave request
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [],
      new Datetime('+3 days'),
      new Datetime('+2 days')
    );
    $this->assertEquals(-2, $balance);

    // The start date is before the leave request start date and the limit date
    // is exactly the same day as the request start date, so the balance will
    // include only 1 day
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [],
      new Datetime('now'),
      new Datetime('-1 day')
    );
    $this->assertEquals(-1, $balance);

    // The start date is exactly the same as the  leave request end date
    // and the limit date is one day after the request end date, so the balance
    // will include only 1 day
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [],
      new Datetime('+11 days'),
      new Datetime('+10 days')
    );
    $this->assertEquals(-1, $balance);
  }

  public function testLeaveRequestBalanceForEntitlementCanExcludeBalanceChangesForPublicHolidayLeaveRequests() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('today'),
      new DateTime('+100 days')
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = date('Y-m-d', strtotime('+30 days'));
    PublicHolidayLeaveRequestFabricator::fabricate($entitlement->contact_id, $publicHoliday);

    // Balance excluding the days deducted from the leave request
    $excludePublicHolidays = true;
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement, [], null, null, $excludePublicHolidays);
    $this->assertEquals(-11, $balance);
  }

  public function testLeaveRequestBalanceForEntitlementCanIncludeOnlyBalanceChangesForPublicHolidayLeaveRequests() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('today'),
      new DateTime('+100 days')
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    $publicHoliday1 = new PublicHoliday();
    $publicHoliday1->date = date('Y-m-d', strtotime('+30 days'));
    PublicHolidayLeaveRequestFabricator::fabricate($entitlement->contact_id, $publicHoliday1);

    $publicHoliday2 = new PublicHoliday();
    $publicHoliday2->date = date('Y-m-d', strtotime('+47 days'));
    PublicHolidayLeaveRequestFabricator::fabricate($entitlement->contact_id, $publicHoliday2);

    // Balance excluding the days deducted from the leave request
    $includePublicHolidaysOnly = true;
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [],
      null,
      null,
      false,
      $includePublicHolidaysOnly
    );
    $this->assertEquals(-2, $balance);
  }

  public function testLeaveRequestBalanceForEntitlementWithParamsToBothExcludeAndIncludePublicHolidaysShouldReturnZero() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('today'),
      new DateTime('+100 days')
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    $publicHoliday1 = new PublicHoliday();
    $publicHoliday1->date = date('Y-m-d', strtotime('+30 days'));
    PublicHolidayLeaveRequestFabricator::fabricate($entitlement->contact_id, $publicHoliday1);

    // Balance excluding the days deducted from the leave request
    $excludePublicHolidays = true;
    $includePublicHolidaysOnly = true;
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [],
      null,
      null,
      $excludePublicHolidays,
      $includePublicHolidaysOnly
    );
    $this->assertEquals(0, $balance);
  }

  public function testLeaveRequestBalanceForEntitlementShouldIncludeTOILBalanceChanges() {
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('today'),
      new DateTime('+100 days')
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $entitlement->contact_id,
      'type_id' => $entitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('today'),
      'to_date' => CRM_Utils_Date::processDate('today'),
      'duration' => 360,
      'toil_to_accrue' => 2,
      'expiry_date' => CRM_Utils_Date::processDate('+30 days'),
    ], true);

    // This will deduct 1 day from the 2 accrued by the toil request
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $entitlement->contact_id,
      'type_id' => $entitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('+10 days'),
      'to_date' => CRM_Utils_Date::processDate('+10 days')
    ], true);


    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(1, $balance);
  }

  public function testLeaveRequestBalanceForEntitlementDoesNotIncludeExpiredTOILBalanceChangeByDefault() {
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('today'),
      new DateTime('+100 days')
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    // Creates a 2 days TOIL Request with 1 day expired
    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      '',
      CRM_Utils_Date::processDate('today'),
      CRM_Utils_Date::processDate('today'),
      2,
      CRM_Utils_Date::processDate('+30 days'),
      1
    );

    // This will deduct 1 day from the 2 accrued by the toil request
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $entitlement->contact_id,
      'type_id' => $entitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('+10 days'),
      'to_date' => CRM_Utils_Date::processDate('+10 days')
    ], true);

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    // This is 1 because the number of days accrued is 2 and only 1 day was
    // deducted (from the leave request). The other day is expired and should
    // not be counted
    $this->assertEquals(1, $balance);
  }


  public function testTheLeaveRequestBreakdownReturnsOnlyTheLeaveBalanceChangesOfTheLeaveRequestDates() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-02'),
      'status_id' => 1
    ]);

    $expectedLeaveBalanceChanges = [];
    foreach($leaveRequest->getDates() as $date) {
      $expectedLeaveBalanceChanges[] = LeaveBalanceChangeFabricator::fabricateForLeaveRequestDate($date);
    }

    // This balance change will not be returned because it's not linked to
    // the leave request
    LeaveBalanceChangeFabricator::fabricate([
      'source_id' => 100,
      'source_type' => LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY,
    ]);

    $breakdownBalanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);

    $this->assertCount(2, $breakdownBalanceChanges);
    foreach($expectedLeaveBalanceChanges as $i => $balanceChange) {
      $this->assertEquals($balanceChange->id, $breakdownBalanceChanges[$i]->id);
      $this->assertEquals($balanceChange->source_id, $breakdownBalanceChanges[$i]->source_id);
      $this->assertEquals($balanceChange->source_type, $breakdownBalanceChanges[$i]->source_type);
      $this->assertEquals($balanceChange->amount, $breakdownBalanceChanges[$i]->amount);
    }
  }

  public function testTheLeaveRequestBreakdownReturnsAnEmptyArrayIfThereAreNoBalanceChangesForLeaveRequestDates() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-02'),
      'status_id' => 1
    ]);

    $this->assertCount(0, LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest));
  }

  public function testTheTotalBalanceChangeForALeaveRequestShouldBeTheSumOfAllItsLeaveBalanceChanges() {
    $withBalanceChanges = true;
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-04'),
      'status_id' => 1
    ], $withBalanceChanges);

    // The balance changes created by the fabricator deduct 1 day for each date,
    // so the total for the 4 days should be 4
    $this->assertEquals(-4, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));
  }

  public function testTheTotalBalanceChangeForALeaveRequestWithoutBalanceChangesShouldBeZero() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-04'),
      'status_id' => 1
    ]);

    $this->assertEquals(0, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));
  }

  public function testGetExistingBalanceChangeForALeaveRequestDateShouldReturnNullIfThereIsNoRecordLinkedToALeaveRequestDateWithTheGivenDate() {
    $leaveRequest = new LeaveRequest();
    $leaveRequest->contact_id = 2;
    $leaveRequest->type_id = 1;

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $leaveRequest->contact_id,
      'type_id' => $leaveRequest->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    // Now we check that there's already a LeaveBalanceChange linked to a
    // a leave request with the same contact_id and type_id of the given leave
    // request but with a different date, so it should return null
    $leaveBalanceChange = LeaveBalanceChange::getExistingBalanceChangeForALeaveRequestDate(
      $leaveRequest,
      new DateTime('2016-01-02')
    );

    $this->assertNull($leaveBalanceChange);
  }

  public function testGetExistingBalanceChangeForALeaveRequestDateShouldReturnNullIfThereIsNoRecordLinkedToALeaveRequestWithTheGivenContact() {
    $date = new DateTime('2017-01-01');
    $leaveDate = $date->format('YmdHis');

    $leaveRequest = new LeaveRequest();
    $leaveRequest->contact_id = 2;
    $leaveRequest->type_id = 1;

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => $leaveRequest->type_id,
      'from_date' => $leaveDate,
      'to_date' => $leaveDate,
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    // Now we since the contact_id on $leaveRequest is different than the one
    // used by the fabricator, no LeaveBalanceChange will be returned
    $leaveBalanceChange = LeaveBalanceChange::getExistingBalanceChangeForALeaveRequestDate(
      $leaveRequest,
      $date
    );

    $this->assertNull($leaveBalanceChange);
  }

  public function testGetExistingBalanceChangeForALeaveRequestDateShouldReturnARecordIfItIsLinkedToALeaveRequestForTheSameContactTypeAndDate() {
    $date = new DateTime('2017-01-01');
    $leaveDate = $date->format('YmdHis');

    $leaveRequest = new LeaveRequest();
    $leaveRequest->contact_id = 2;
    $leaveRequest->type_id = 1;

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $leaveRequest->contact_id,
      'type_id' => $leaveRequest->type_id,
      'from_date' => $leaveDate,
      'to_date' => $leaveDate,
      'from_date_type' => 1,
      'to_date_type' =>1,
      'status_id' => 1
    ], true);

    // Now we check that there's already a LeaveBalanceChange linked to a
    // a leave request with the same contact_id and type_id of the given leave
    // request and also linked to a leave request date with the given date
    $leaveBalanceChange = LeaveBalanceChange::getExistingBalanceChangeForALeaveRequestDate($leaveRequest, $date);

    $this->assertNotNull($leaveBalanceChange);
  }

  public function testCanDeleteTheBalanceChangeForALeaveRequestDate() {
    $leaveRequestDate = LeaveRequestDate::create([
      'date' => CRM_Utils_Date::processDate('2016-01-01'),
      'leave_request_id' => 1
    ]);

    $balanceChange = LeaveBalanceChangeFabricator::fabricateForLeaveRequestDate($leaveRequestDate);

    LeaveBalanceChange::deleteForLeaveRequestDate($leaveRequestDate);

    try {
      $balanceChange = LeaveBalanceChange::findById($balanceChange->id);
      $this->fail('Expected the balance change to be deleted, but it was find');
    } catch(Exception $e) {
      $exceptionMessage = "Unable to find a CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange with id {$balanceChange->id}.";
      $this->assertEquals($exceptionMessage, $e->getMessage());
    }
  }

  public function testCanDeleteAllTheBalanceChangesForALeaveRequest() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-01-07'),
      'to_date_type' => 1,
    ], true);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $this->assertCount(7, $balanceChanges);

    LeaveBalanceChange::deleteAllForLeaveRequest($leaveRequest);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $this->assertCount(0, $balanceChanges);
  }

  public function testCreateExpiryRecordsCreatesRecordsForExpiredBalanceChanges() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
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
      'type_id' => 2
    ]);

    $this->createBroughtForwardBalanceChange(
      $periodEntitlement1->id,
      5,
      CRM_Utils_Date::processDate('-1 day')
    );

    $this->createBroughtForwardBalanceChange(
      $periodEntitlement2->id,
      7,
      CRM_Utils_Date::processDate('-8 days')
    );

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(2, $numberOfCreatedRecords);

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(0, $numberOfCreatedRecords);
  }

  public function testCreateExpiryRecordsCreatesRecordsEntitlementsWithMultipleExpiredBalanceChanges() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
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
      'type_id' => 2
    ]);

    // The entitlement with ID 1 has 2 balance changes to expire
    $this->createBroughtForwardBalanceChange(
      $periodEntitlement1->id,
      5,
      CRM_Utils_Date::processDate('-1 day')
    );

    $this->createBroughtForwardBalanceChange(
      $periodEntitlement2->id,
      7,
      CRM_Utils_Date::processDate('-8 days')
    );

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(2, $numberOfCreatedRecords);

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(0, $numberOfCreatedRecords);
  }

  public function testCreateExpiryRecordsCalculatesTheExpiredAmountBasedOnTheApprovedLeaveRequestBalance() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-30 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $balanceChange = $this->createBroughtForwardBalanceChange(
      $periodEntitlement->id,
      5,
      CRM_Utils_Date::processDate('-1 day')
    );

    //This 1 day approved leave request will be counted
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-10 days')
    );

    // This 2 days cancelled leave request won't counted
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $leaveRequestStatuses['Cancelled'],
      CRM_Utils_Date::processDate('-20 days'),
      CRM_Utils_Date::processDate('-21 days')
    );

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(1, $numberOfCreatedRecords);

    $expiryRecord = $this->getExpiryRecordForBalanceChange($balanceChange->id);
    $this->assertNotNull($expiryRecord);
    // Since only the 1 day leave request was counted, 4 days expired
    // 5 - 1 = 4 (we store expired days as a negative number)
    $this->assertEquals(-4, $expiryRecord->amount);
  }

  public function testCreateExpiryRecordsPrioritizesAccordingToTheBalanceChangeExpiryDate() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-30 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $balanceChange1 = $this->createBroughtForwardBalanceChange(
      $periodEntitlement->id,
      5,
      CRM_Utils_Date::processDate('-1 day')
    );

    $balanceChange2 = $this->createBroughtForwardBalanceChange(
      $periodEntitlement->id,
      5,
      CRM_Utils_Date::processDate('-5 days')
    );

    // A 7 days approved leave request
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-7 days'),
      CRM_Utils_Date::processDate('-1 day')
    );

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(2, $numberOfCreatedRecords);

    $expiryRecord2 = $this->getExpiryRecordForBalanceChange($balanceChange2->id);
    // Balance change 2 expires first, so we also handle it first
    // 3 days of leave request are deducted from it, so 2 days should expire
    $this->assertEquals(-2, $expiryRecord2->amount);

    $expiryRecord1 = $this->getExpiryRecordForBalanceChange($balanceChange1->id);
    // Now we handle the balance change 1, which expires after balance change 2
    // Since we already deducted 3 days, now we just deduct the remaining 4 days
    // meaning only 1 day will expire
    $this->assertEquals(-1, $expiryRecord1->amount);
  }

  public function testCreateExpiryRecordsCalculatesTheExpiredAmountBasedOnlyOnTheApprovedLeaveRequestBalancePriorToTheExpiryDate() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-30 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $balanceChange = $this->createBroughtForwardBalanceChange(
      $periodEntitlement->id,
      5,
      date('YmdHis', strtotime('-1 day'))
    );

    // This leave request has 7 days, but only two of them
    // were taken before the brought forward expiry date
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-2 days'),
      CRM_Utils_Date::processDate('+5 days')
    );

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(1, $numberOfCreatedRecords);

    $expiryRecord = $this->getExpiryRecordForBalanceChange($balanceChange->id);
    $this->assertNotNull($expiryRecord);
    // Since only two days were taken before the brought forward
    // expiry date, the other 3 days will expire
    $this->assertEquals(-3, $expiryRecord->amount);
  }

  public function testCreateExpiryRecordsCalculatesTheExpiredAmountCorrectlyForMoreThanOneContact() {
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
      'contact_id' => 2,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $balanceChange1 = $this->createBroughtForwardBalanceChange(
      $periodEntitlement1->id,
      5,
      date('YmdHis', strtotime('-1 day'))
    );

    $balanceChange2 = $this->createBroughtForwardBalanceChange(
      $periodEntitlement2->id,
      5,
      date('YmdHis', strtotime('-1 day'))
    );

    // This leave request has 7 days, but only two of them
    // were taken before the brought forward expiry date
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement1->type_id,
      $periodEntitlement1->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-2 days'),
      CRM_Utils_Date::processDate('+5 days')
    );

    // This leave request has 7 days, but only four of them
    // were taken before the brought forward expiry date
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement2->type_id,
      $periodEntitlement2->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-4 days'),
      CRM_Utils_Date::processDate('+3 days')
    );

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(2, $numberOfCreatedRecords);

    $expiryRecord1 = $this->getExpiryRecordForBalanceChange($balanceChange1->id);
    $this->assertNotNull($expiryRecord1);
    // Since only two days were taken before the brought forward
    // expiry date, the other 3 days will expire
    $this->assertEquals(-3, $expiryRecord1->amount);

    $expiryRecord2 = $this->getExpiryRecordForBalanceChange($balanceChange2->id);
    $this->assertNotNull($expiryRecord2);
    // Since only four days were taken before the brought forward
    // expiry date, the remaining one expire
    $this->assertEquals(-1, $expiryRecord2->amount);
  }

  public function testGetLeavePeriodEntitlementCanReturnThePeriodEntitlementWhenTheSourceTypeIsEntitlement() {
    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => 1,
      'type_id' => 1,
    ]);

    $balanceChange = LeaveBalanceChangeFabricator::fabricate([
      'source_id' => $periodEntitlement->id,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'amount' => 10
    ]);

    $balanceChangePeriodEntitlement = $balanceChange->getLeavePeriodEntitlement();

    $this->assertInstanceOf(LeavePeriodEntitlement::class, $balanceChangePeriodEntitlement);
    $this->assertEquals($balanceChangePeriodEntitlement->id, $periodEntitlement->id);
  }

  public function testGetLeavePeriodEntitlementCanReturnThePeriodEntitlementWhenTheSourceTypeIsToilRequest() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $period->id,
      'type_id' => 1,
    ]);

    $toilRequest = TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'toil_to_accrue' => 1,
      'duration' => 200,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-30'),
    ], true);

    $balanceChange = $this->findToilRequestBalanceChange($toilRequest->id);

    $balanceChangePeriodEntitlement = $balanceChange->getLeavePeriodEntitlement();

    $this->assertInstanceOf(LeavePeriodEntitlement::class, $balanceChangePeriodEntitlement);
    $this->assertEquals($balanceChangePeriodEntitlement->id, $periodEntitlement->id);
  }

  public function testGetLeavePeriodEntitlementCanReturnThePeriodEntitlementWhenTheSourceTypeLeaveRequestDay() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $period->id,
      'type_id' => 1,
    ]);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
    ], true);

    $balanceChange = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest)[0];

    $balanceChangePeriodEntitlement = $balanceChange->getLeavePeriodEntitlement();

    $this->assertInstanceOf(LeavePeriodEntitlement::class, $balanceChangePeriodEntitlement);
    $this->assertEquals($balanceChangePeriodEntitlement->id, $periodEntitlement->id);
  }

  public function testGetLeavePeriodEntitlementThrowsAnErrorIfTheSourceTypeIsUnknown() {
    $balanceChange = new LeaveBalanceChange();
    $sourceType = uniqid('bla', true);
    $balanceChange->source_type = $sourceType;

    $this->setExpectedException(RuntimeException::class, "'{$sourceType}' is not a valid Balance Change source type");

    $balanceChange->getLeavePeriodEntitlement();
  }

  public function testCreateExpiryRecordsCanExpireTOILRequests() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-30 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'status_id' => $leaveRequestStatuses['Approved'],
      'from_date' => CRM_Utils_Date::processDate('-20 days'),
      'to_date' => CRM_Utils_Date::processDate('-20 days'),
      'toil_to_accrue' => 1,
      'duration' => 200,
      'expiry_date' => CRM_Utils_Date::processDate('-10 days'),
    ], true);

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(1, $numberOfCreatedRecords);
  }

  public function testCreateExpiryRecordsCanExpireTOILRequestOverlappingBroughtForward() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $broughtForwardBalanceChange = LeaveBalanceChangeFabricator::fabricate([
      'source_id' => $periodEntitlement->id,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'amount' => 5,
      'expiry_date' => CRM_Utils_Date::processDate('2016-02-27'),
      'type_id' => $this->getBalanceChangeTypeValue('Brought Forward')
    ]);

    $toilRequest1 = TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'expiry_date' => CRM_Utils_Date::processDate('2016-02-17'),
      'toil_to_accrue' => 1,
      'duration' => 100
    ], true);

    $toilRequest2 = TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-02-25'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-25'),
      'expiry_date' => CRM_Utils_Date::processDate('2016-03-01'),
      'toil_to_accrue' => 2,
      'duration' => 100
    ], true);

    // This leave request overlaps only the brought forward period
    // so it will deduct 1 day from it
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-05'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-05'),
    ], true);

    // This leave request overlaps both the first toil request
    // period and the brought forward, but since the toil will
    // expire first, the 1 day will be deducted from it
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-02-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-01'),
    ], true);

    // This is a 3 days leave request. The first 2 days
    // overlap both the brought forward and the second toil request.
    // The last day, overlaps only the toil request.
    // Since the brought forward expires first than the toil request,
    // the 2 days overlapping both should be deducted from it.
    // The remaining 1 day should be deducted from the toil request.
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-02-26'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-28'),
    ], true);

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(3, $numberOfCreatedRecords);

    $expiredBroughtForward = $this->getExpiryRecordForBalanceChange($broughtForwardBalanceChange->id);
    // Original 5 days. 3 days used (1 from the first leave request and 2 from the last one). 2 expired
    $this->assertEquals(-2, $expiredBroughtForward->amount);

    $expiredToilRequest1 = $this->getExpiryRecordForToilRequest($toilRequest1->id);
    // Original 1 day. 1 day used from the second leave request. None expired.
    $this->assertEquals(0, $expiredToilRequest1->amount);

    // Original 2 days. 1 day used from the third leave request. 1 day expired
    $expiredToilRequest2 = $this->getExpiryRecordForToilRequest($toilRequest2->id);
    $this->assertEquals(-1, $expiredToilRequest2->amount);
  }

  public function testCreateExpiryRecordsCanCalculateTheExpiryAmounWhenTheNumberOfDaysTakenBeforeTheExpiryDateIsBiggerThanTheBalanceChangeAmount() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $broughtForwardBalanceChange = LeaveBalanceChangeFabricator::fabricate([
      'source_id' => $periodEntitlement->id,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'amount' => 5,
      'expiry_date' => CRM_Utils_Date::processDate('2016-02-27'),
      'type_id' => $this->getBalanceChangeTypeValue('Brought Forward')
    ]);

    $toilRequest1 = TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'expiry_date' => CRM_Utils_Date::processDate('2016-02-26'),
      'toil_to_accrue' => 1,
      'duration' => 100
    ], true);

    // A 7 days Leave Request, overlapping both the TOIL request period and
    // the brought forward
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-26'),
    ], true);

    $numberOfRecords = LeaveBalanceChange::createExpiryRecords();

    $expiredBroughtForward = $this->getExpiryRecordForBalanceChange($broughtForwardBalanceChange->id);
    $expiredTOILBalanceChange = $this->getExpiryRecordForToilRequest($toilRequest1->id);

    $this->assertEquals(2, $numberOfRecords);

    // TOIL Expires first, so we first deduct from it. Its amount is only 1,
    // and since the leave request is 7 days, the whole 1 day will be user and
    // nothing will expire.
    $this->assertEquals(0, $expiredTOILBalanceChange->amount);

    // 1 days of the Leave Request was deducted from TOIL. This leave us with
    // 6 more days to deducted. The brought forward amount is 5, so this means
    // that all the 5 days will be used and nothing will expired
    $this->assertEquals(0, $expiredBroughtForward->amount);

    // 1 day was deducted from TOIL and 5 were deducted from Brought Forward,
    // so the remaining 1 day will be deducted from the entitlement. Since
    // there's none, the balance will be -1:
    $periodBalance = LeaveBalanceChange::getBalanceForEntitlement($periodEntitlement);
    $this->assertEquals(-1, $periodBalance);
  }

  public function testCreateExpiryRecordsCanExpireFractionalDays() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $broughtForwardBalanceChange = LeaveBalanceChangeFabricator::fabricate([
      'source_id' => $periodEntitlement->id,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'amount' => 5,
      'expiry_date' => CRM_Utils_Date::processDate('2016-02-27'),
      'type_id' => $this->getBalanceChangeTypeValue('Brought Forward')
    ]);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-21'),
    ]);

    // Because the fabricator creates all the balance changes with -1 as the
    // amount, we need to create them manually in order to be able to have
    // fractional values
    $dates = $leaveRequest->getDates();
    LeaveBalanceChangeFabricator::fabricate([
      'amount' => -0.3,
      'source_id' => $dates[0]->id,
      'source_type' => LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY,
    ]);

    LeaveBalanceChangeFabricator::fabricate([
      'amount' => -0.4,
      'source_id' => $dates[1]->id,
      'source_type' => LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY,
    ]);

    $numberOfRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(1, $numberOfRecords);

    $expiredBroughtForward = $this->getExpiryRecordForBalanceChange($broughtForwardBalanceChange->id);
    $this->assertEquals(-4.3, $expiredBroughtForward->amount);
  }

  public function testCreateExpiryRecordsOnlyDeductsFromOneBalanceChangeWhenBothTOILAndBroughtForwardExpireOnTheSameDay() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $broughtForwardBalanceChange = LeaveBalanceChangeFabricator::fabricate([
      'source_id' => $periodEntitlement->id,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'amount' => 5,
      'expiry_date' => CRM_Utils_Date::processDate('2016-02-27'),
      'type_id' => $this->getBalanceChangeTypeValue('Brought Forward')
    ]);

    $toilRequest1 = TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'expiry_date' => CRM_Utils_Date::processDate('2016-02-27'),
      'toil_to_accrue' => 1,
      'duration' => 100
    ], true);

    // This Leave Request overlaps both toil and brought forward, but will be
    // deduct only from one of them
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-02-26'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-26'),
    ], true);

    $numberOfRecords = LeaveBalanceChange::createExpiryRecords();

    $expiredBroughtForward = $this->getExpiryRecordForBalanceChange($broughtForwardBalanceChange->id);
    $expiredTOILBalanceChange = $this->getExpiryRecordForToilRequest($toilRequest1->id);

    $this->assertEquals(2, $numberOfRecords);

    // Since, internally, createExpirationRecords uses the balance_change.id as
    // a tiebreaker for when both expiry dates are the same, we can know for sure
    // that the date will always be deducted from the Brought Forward instead of
    // TOIL
    $this->assertEquals(-4, $expiredBroughtForward->amount);
    $this->assertEquals(-1, $expiredTOILBalanceChange->amount);
  }

  public function testCreateExpiryRecordsConsidersOnlyTheApprovedLeaveRequestsBetweenTheTOILRequestDateAndTOILExpiryDateToExpireTOILRequests() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-30 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $toilRequest = TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'status_id' => $leaveRequestStatuses['Approved'],
      'from_date' => CRM_Utils_Date::processDate('-20 days'),
      'to_date' => CRM_Utils_Date::processDate('-20 days'),
      'toil_to_accrue' => 2,
      'duration' => 200,
      'expiry_date' => CRM_Utils_Date::processDate('-10 days'),
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'status_id' => $leaveRequestStatuses['Cancelled'],
      'from_date' => CRM_Utils_Date::processDate('-15 days'),
      'to_date' => CRM_Utils_Date::processDate('-15 days'),
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'status_id' => $leaveRequestStatuses['Approved'],
      'from_date' => CRM_Utils_Date::processDate('-13 days'),
      'to_date' => CRM_Utils_Date::processDate('-13 days'),
    ], true);

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(1, $numberOfCreatedRecords);

    $expiredBalanceChange = $this->getExpiryRecordForToilRequest($toilRequest->id);
    $this->assertEquals(-1, $expiredBalanceChange->amount);
  }

  public function testCreateExpiryRecordsDoesNotCreateRecordsForBalanceChangesThatNeverExpire() {
    // A Brought Forward without an expiry date will never expire
    $this->createBroughtForwardBalanceChange(1, 5);

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(0, $numberOfCreatedRecords);
  }

  public function testCreateExpiryRecordsDoesNotCreateRecordsForNonExpiredBalanceChanges() {
    $this->createBroughtForwardBalanceChange(1, 5, date('YmdHis', strtotime('+1 day')));

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(0, $numberOfCreatedRecords);
  }

  public function testCreateExpiryRecordsDoesCreatesRecordsForAlreadyExpiredBalanceChanges() {
    $this->createExpiredBroughtForwardBalanceChange(1, 5, 10);

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(0, $numberOfCreatedRecords);
  }

  private function createLeavePeriodEntitlement() {
    return LeavePeriodEntitlement::create([
      'type_id' => 1,
      'period_id' => 1,
      'contact_id' => 1
    ]);
  }

  private function getExpiryRecordForBalanceChange($balanceChangeID) {
    $record = new LeaveBalanceChange();
    $record->expired_balance_change_id = $balanceChangeID;
    $record->find();
    if($record->N == 1) {
      $record->fetch();
      return $record;
    }

    return null;
  }

  private function getExpiryRecordForToilRequest($toilRequestID) {
    $record = new LeaveBalanceChange();
    $record->source_id = $toilRequestID;
    $record->source_type = LeaveBalanceChange::SOURCE_TOIL_REQUEST;
    $record->whereAdd('expired_balance_change_id IS NOT NULL');
    $record->find();
    if($record->N == 1) {
      $record->fetch();
      return $record;
    }

    return null;
  }

  private function deleteDefaultWorkPattern() {
    $workPatternTable = WorkPattern::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$workPatternTable} WHERE is_default = 1");
  }

  public function testGetBalanceChangeForToilLeaveRequest() {

    $toilRequest = TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+1 days'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ], true);

    $this->assertEquals(2, LeaveBalanceChange::getAmountForTOILRequest($toilRequest->id));
  }

  public function testGetTotalTOILBalanceChangeForContactWithinAGivenPeriod() {
    $contactID = 1;
    $absenceTypeID = 1;
    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+1 days'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 1,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ], true);

    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+3 days'),
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ], true);

    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+5 days'),
      'to_date' => CRM_Utils_Date::processDate('+6 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 3,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ], true);

    $startDate = new DateTime('+3 days');
    $endDate = new DateTime('+6 days');

    //only the last two TOILs fall within the given start and end date period
    $totalBalanceChange = LeaveBalanceChange::getTotalTOILBalanceChangeForContact($contactID, $absenceTypeID, $startDate, $endDate);
    $this->assertEquals(5, $totalBalanceChange);
  }

  public function testGetTotalTOILBalanceChangeForContactWithinAGivenPeriodAndWithSpecificStatusesAndAbsenceType() {
    $contactID = 1;
    $absenceTypeID = 1;
    $absenceTypeID2 = 2;
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Approved'],
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 1,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ], true);

    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Admin Approved'],
      'from_date' => CRM_Utils_Date::processDate('+3 days'),
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ], true);

    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID2,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Admin Approved'],
      'from_date' => CRM_Utils_Date::processDate('+3 days'),
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ], true);

    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Waiting Approval'],
      'from_date' => CRM_Utils_Date::processDate('+5 days'),
      'to_date' => CRM_Utils_Date::processDate('+6 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 3,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ], true);

    $startDate = new DateTime('+1 day');
    $endDate = new DateTime('+6 days');

    //only the first two TOILs have  Approved and Admin Approved status and also have type_id = 1
    $totalBalanceChange = LeaveBalanceChange::getTotalTOILBalanceChangeForContact(
      $contactID,
      $absenceTypeID,
      $startDate,
      $endDate,
      [$leaveRequestStatuses['Admin Approved'], $leaveRequestStatuses['Approved']]
    );
    $this->assertEquals(3, $totalBalanceChange);
  }
}

