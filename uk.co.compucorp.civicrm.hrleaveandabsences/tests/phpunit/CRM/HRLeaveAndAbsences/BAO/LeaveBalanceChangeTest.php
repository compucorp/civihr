<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;
use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern as ContactWorkPattern;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange as LeaveBalanceChangeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestFabricator;

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

    // This is the initial entitlement and, since it has no
    // source_id, it will always be included in the balance SUM
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

  public function testTheEntitlementBreakdownReturnsThePositiveLeaveBroughtForwardAndPublicHolidayChangesWithoutASource() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
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
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-04'),
      'status_id' => 1
    ]);

    $expectedLeaveBalanceChanges = [];
    foreach($leaveRequest->getDates() as $date) {
      $expectedLeaveBalanceChanges[] = LeaveBalanceChangeFabricator::fabricateForLeaveRequestDate($date);
    }

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

  public function testCanCreateBalanceChangesForALeaveRequest() {
    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();

    $contactWorkPattern = ContactWorkPattern::create([
      'pattern_id' => $workPattern->id,
      'contact_id' => 2,
      'effective_date' => CRM_Utils_Date::processDate('2016-01-01')
    ]);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contactWorkPattern->contact_id,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-04'),
      'status_id' => 1
    ]);

    LeaveBalanceChange::createForLeaveRequest($leaveRequest);

    // One balance change must be created for each leave request date
    $this->assertCount(4, LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest));

    // Only 2 of the 4 days of the LeaveRequest are working days, so
    $this->assertEquals(-2, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));
  }

  public function testCanCreateBalanceChangesForALeaveRequestUsingTheDefaultWorkPattern() {
    // The default work pattern will be used if there's no work pattern
    // for a contact
    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);

    // To properly rotate the weeks of a work pattern, we need a base start date.
    // When using a pattern linked to a contact, this date will be the
    // pattern effective date. When using the default work pattern, the date
    // will be the start date of the contract overlapping the leave request date
    $this->createContract();
    $this->setContractDates('2016-01-01', '2016-12-31');

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $this->contract['contact_id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-08-05'), //Friday
      'to_date' => CRM_Utils_Date::processDate('2016-08-06'), //Saturday
      'status_id' => 1
    ]);

    LeaveBalanceChange::createForLeaveRequest($leaveRequest);

    // One balance change must be created for each leave request date
    $this->assertCount(2, LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest));

    // Only 1 of the 2 days of the LeaveRequest are working days, so
    $this->assertEquals(-1, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));
  }

  public function testTheBalanceChangesForALeaveRequestOfAContactWithoutAContractShouldBeZero() {
    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 2,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-05'),
      'to_date' => CRM_Utils_Date::processDate('2016-11-08'),
      'status_id' => 1
    ]);

    LeaveBalanceChange::createForLeaveRequest($leaveRequest);

    // One balance change must be created for each leave request date
    $this->assertCount(4, LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest));

    // The amount for each balance change should be 0, as well as the Total
    $this->assertEquals(0, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));
  }

  public function testTheBalanceChangesForALeaveRequestOfAContactWithoutAWorkPatternShouldBeZeroIfTheresNoDefaultWorkPattern() {
    $this->deleteDefaultWorkPattern();

    $this->createContract();
    $this->setContractDates('2016-01-01', '2016-12-31');

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 2,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-05'),
      'to_date' => CRM_Utils_Date::processDate('2016-11-08'),
      'status_id' => 1
    ]);

    LeaveBalanceChange::createForLeaveRequest($leaveRequest);

    // One balance change must be created for each leave request date
    $this->assertCount(4, LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest));

    // The amount for each balance change should be 0, as well as the Total
    $this->assertEquals(0, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));
  }

  public function testTheBalanceChangeForALeaveRequestDateOverlappingAPublicHolidayLeaveRequestShouldBeZero() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 2,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ]);

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = CRM_Utils_Date::processDate('2016-01-01');
    PublicHolidayLeaveRequestFabricator::fabricate($leaveRequest->contact_id, $publicHoliday);

    LeaveBalanceChange::createForLeaveRequest($leaveRequest);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $this->assertCount(1, $balanceChanges);
    $this->assertEquals(0, $balanceChanges[0]->amount);
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

//  public function testCreateExpirationRecordsCreatesRecordsForExpiredBalanceChanges() {
//    $this->createBroughtForwardBalanceChange(1, 5, date('YmdHis', strtotime('-1 day')));
//    $this->createBroughtForwardBalanceChange(2, 7, date('YmdHis', strtotime('-8 days')));
//
//    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
//    $this->assertEquals(2, $numberOfCreatedRecords);
//
//    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
//    $this->assertEquals(0, $numberOfCreatedRecords);
//  }
//
//  public function testCreateExpirationRecordsCreatesRecordsEntitlementsWithMultipleExpiredBalanceChanges() {
//    // The entitlement with ID 1 has 2 balance changes to expire
//    $this->createBroughtForwardBalanceChange(1, 5, date('YmdHis', strtotime('-1 day')));
//    $this->createBroughtForwardBalanceChange(1, 7, date('YmdHis', strtotime('-8 days')));
//
//    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
//    $this->assertEquals(2, $numberOfCreatedRecords);
//
//    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
//    $this->assertEquals(0, $numberOfCreatedRecords);
//  }
//
//  public function testCreateExpirationRecordsCalculatesTheExpiredAmountBasedOnTheApprovedLeaveRequestBalance() {
//    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
//
//    $balanceChange = $this->createBroughtForwardBalanceChange(1, 5, date('YmdHis', strtotime('-1 day')));
//
//    //This 1 day approved leave request will be counted
//    $this->createLeaveRequestBalanceChange(
//      1,
//      $leaveRequestStatuses['Approved'],
//      date('Y-m-d', strtotime('-10 days'))
//    );
//
//    // This 2 days cancelled leave request won't counted
//    $this->createLeaveRequestBalanceChange(
//      1,
//      $leaveRequestStatuses['Cancelled'],
//      date('Y-m-d', strtotime('-20 days')),
//      date('Y-m-d', strtotime('-21 days'))
//    );
//
//    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
//    $this->assertEquals(1, $numberOfCreatedRecords);
//
//    $expirationRecord = $this->getExpirationRecordForBalanceChange($balanceChange->id);
//    $this->assertNotNull($expirationRecord);
//    // Since only the 1 day leave request was counted, 4 days expired
//    // 5 - 1 = 4 (we store expired days as a negative number)
//    $this->assertEquals(-4, $expirationRecord->amount);
//  }
//
//  public function testCreateExpirationRecordsCalculatesPrioritizesAccordingToTheBalanceChangeExpiryDate() {
//    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
//
//    $balanceChange1 = $this->createBroughtForwardBalanceChange(
//      1,
//      5,
//      date('YmdHis', strtotime('-1 day'))
//    );
//    $balanceChange2 = $this->createBroughtForwardBalanceChange(
//      1,
//      5,
//      date('YmdHis', strtotime('-5 days'))
//    );
//
//    // A 7 days approved leave request
//    $this->createLeaveRequestBalanceChange(
//      1,
//      $leaveRequestStatuses['Approved'],
//      date('Y-m-d', strtotime('-7 days')),
//      date('Y-m-d', strtotime('-1 day'))
//    );
//
//    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
//    $this->assertEquals(2, $numberOfCreatedRecords);
//
//    $expirationRecord2 = $this->getExpirationRecordForBalanceChange($balanceChange2->id);
//    // Balance change 2 expires first, so we also handle it first
//    // 3 days of leave request are deducted from it, so 2 days should expire
//    $this->assertEquals(-2, $expirationRecord2->amount);
//
//    $expirationRecord1 = $this->getExpirationRecordForBalanceChange($balanceChange1->id);
//    // Now we handle the balance change 1, which expires after balance change 2
//    // Since we already deducted 3 days, now we just deduct the remaining 4 days
//    // meaning only 1 day will expire
//    $this->assertEquals(-1, $expirationRecord1->amount);
//  }
//
//  public function testCreateExpirationRecordsCalculatesTheExpiredAmountBasedOnlyOnTheApprovedLeaveRequestBalancePriorToTheExpiryDate() {
//    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
//    $balanceChange = $this->createBroughtForwardBalanceChange(
//      1,
//      5,
//      date('YmdHis', strtotime('-1 day'))
//    );
//
//    // This leave request has 7 days, but only two of them
//    // were taken before the brought forward expiry date
//    $this->createLeaveRequestBalanceChange(
//      1,
//      $leaveRequestStatuses['Approved'],
//      date('Y-m-d', strtotime('-2 days')),
//      date('Y-m-d', strtotime('+5 days'))
//    );
//
//    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
//    $this->assertEquals(1, $numberOfCreatedRecords);
//
//    $expirationRecord = $this->getExpirationRecordForBalanceChange($balanceChange->id);
//    $this->assertNotNull($expirationRecord);
//    // Since only two days were taken before the brought forward
//    // expiry date, the other 3 days will expire
//    $this->assertEquals(-3, $expirationRecord->amount);
//  }
//
//  public function testCreateExpirationRecordsDoesNotCreateRecordsForBalanceChangesThatNeverExpire() {
//    // A Brought Forward without an expiry date will never expire
//    $this->createBroughtForwardBalanceChange(1, 5);
//
//    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
//    $this->assertEquals(0, $numberOfCreatedRecords);
//  }
//
//  public function testCreateExpirationRecordsDoesNotCreateRecordsForNonExpiredBalanceChanges() {
//    $this->createBroughtForwardBalanceChange(1, 5, date('YmdHis', strtotime('+1 day')));
//
//    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
//    $this->assertEquals(0, $numberOfCreatedRecords);
//  }
//
//  public function testCreateExpirationRecordsDoesCreatesRecordsForExpiredBalanceChanges() {
//    $this->createExpiredBroughtForwardBalanceChange(1, 5, 10);
//
//    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
//    $this->assertEquals(0, $numberOfCreatedRecords);
//  }

  private function createLeavePeriodEntitlement() {
    return LeavePeriodEntitlement::create([
      'type_id' => 1,
      'period_id' => 1,
      'contact_id' => 1
    ]);
  }

  private function getExpirationRecordForBalanceChange($balanceChangeID) {
    $record = new LeaveBalanceChange();
    $record->expired_balance_change_id = $balanceChangeID;
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
}

