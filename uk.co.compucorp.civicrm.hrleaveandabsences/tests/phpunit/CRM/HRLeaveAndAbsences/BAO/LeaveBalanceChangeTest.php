<?php

require_once __DIR__."/../LeaveBalanceChangeHelpersTrait.php";

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChangeTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChangeTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface, TransactionalInterface {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;

  public function setUpHeadless() {
    return \Civi\Test::headless()
                     ->installMe(__DIR__)
                     ->install('org.civicrm.hrjobcontract')
                     ->apply();
  }

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
      'expired_balance_id' => $balanceChangeToExpire->id
    ]);

    $this->assertNotEmpty($expiryBalanceChange->id);

    // A second expiry record should not be allowed to be created
    LeaveBalanceChange::create([
      'entitlement_id' => $entitlement->id,
      'type_id' => 1,
      'amount' => -3,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'expired_balance_id' => $balanceChangeToExpire->id
    ]);
  }

  public function testGetBalanceForEntitlementCanSumAllTheBalanceChangesForAGivenEntitlement() {
    $entitlement = $this->createLeavePeriodEntitlement();

    LeaveBalanceChange::create([
      'source_id' => $entitlement->id,
      'source_type' => 'entitlement',
      'type_id' => 1,
      'amount' => 4.3
    ]);

    $this->assertEquals(4.3, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id));

    LeaveBalanceChange::create([
      'source_id' => $entitlement->id,
      'source_type' => 'entitlement',
      'type_id' => 2,
      'amount' => 2
    ]);

    $this->assertEquals(6.3, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id));

    LeaveBalanceChange::create([
      'source_id' => $entitlement->id,
      'source_type' => 'entitlement',
      'type_id' => 2,
      'amount' => -3.5
    ]);

    $this->assertEquals(2.8, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id));

    LeaveBalanceChange::create([
      'source_id' => $entitlement->id,
      'source_type' => 'entitlement',
      'type_id' => 2,
      'amount' => -2
    ]);

    $this->assertEquals(0.8, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id));
  }

  public function testBalanceForEntitlementCanSumOnlyTheBalanceChangesForLeaveRequestWithSpecificStatuses() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlement();

    // This is the initial entitlement and, since it has no
    // source_id, it will always be included in the balance SUM
    $this->createLeaveBalanceChange($entitlement->id, 10);
    $this->assertEquals(10, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id));

    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Cancelled'],
      date('Y-m-d', strtotime('-10 days'))
    );

    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Rejected'],
      date('Y-m-d', strtotime('-9 days'))
    );

    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d', strtotime('-8 days'))
    );

    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Admin Approved'],
      date('Y-m-d', strtotime('-7 days'))
    );

    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Waiting Approval'],
      date('Y-m-d', strtotime('-6 days'))
    );

    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['More Information Requested'],
      date('Y-m-d', strtotime('-6 days'))
    );

    // Including all the balance changes
    $this->assertEquals(4, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id));

    // Only Include balance changes from approved leave requests
    $statusesToInclude = [
      $leaveRequestStatuses['Approved'],
      $leaveRequestStatuses['Admin Approved'],
    ];
    $this->assertEquals(8, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id, $statusesToInclude));

    // Only Include balance changes from cancelled/rejected leave requests
    $statusesToInclude = [
      $leaveRequestStatuses['Cancelled'],
      $leaveRequestStatuses['Rejected'],
    ];
    $this->assertEquals(8, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id, $statusesToInclude));

    // Only Include balance changes from leave requests waiting approval
    $statusesToInclude = [ $leaveRequestStatuses['Waiting Approval'] ];
    $this->assertEquals(9, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id, $statusesToInclude));

    // Only Include balance changes from leave requests waiting for more information
    $statusesToInclude = [ $leaveRequestStatuses['More Information Requested'] ];
    $this->assertEquals(9, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id, $statusesToInclude));
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

    // This will deduct 6 days, but the respective balance changes
    // won't be returned as part of the breakdown
    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+5 days'))
    );

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
    $entitlement = $this->createLeavePeriodEntitlement();

    $this->createLeaveBalanceChange($entitlement->id, 23.5);
    $this->createBroughtForwardBalanceChange($entitlement->id, 4);
    $this->createPublicHolidayBalanceChange($entitlement->id, 8);

    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement->id);
    $this->assertEquals(0, $leaveRequestBalanceChange);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Admin Approved'],
      date('Y-m-d', strtotime('+11 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Cancelled'],
      date('Y-m-d', strtotime('+12 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Rejected'],
      date('Y-m-d', strtotime('+13 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Waiting Approval'],
      date('Y-m-d', strtotime('+14 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['More Information Requested'],
      date('Y-m-d', strtotime('+15 days'))
    );

    // Balance include all the leave requests
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement->id);
    $this->assertEquals(-16, $leaveRequestBalanceChange);

    // Balance including only approved leave requests
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement->id,
      [ $leaveRequestStatuses['Approved'], $leaveRequestStatuses['Admin Approved'] ]
    );
    $this->assertEquals(-12, $leaveRequestBalanceChange);

    // Balance including only cancelled or rejected leave requests
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement->id,
      [ $leaveRequestStatuses['Cancelled'], $leaveRequestStatuses['Rejected'] ]
    );
    $this->assertEquals(-2, $leaveRequestBalanceChange);

    // Balance including only leave requests waiting approval
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement->id,
      [ $leaveRequestStatuses['Waiting Approval'] ]
    );
    $this->assertEquals(-1, $leaveRequestBalanceChange);

    // Balance including only leave requests waiting for more information
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement->id,
      [ $leaveRequestStatuses['More Information Requested'] ]
    );
    $this->assertEquals(-1, $leaveRequestBalanceChange);
  }

  public function testLeaveRequestBalanceForEntitlementCanSumBalanceChangesCreatedByLeaveRequestsUpToASpecificDate() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlement();

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement->id);
    $this->assertEquals(0, $balance);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    // Balance including the whole leave request
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement->id);
    $this->assertEquals(-11, $balance);

    // Balance including only the first day of leave request
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement->id,
      [],
      new Datetime('now')
    );
    $this->assertEquals(-1, $balance);

    // Balance including only the two first days of leave request
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement->id,
      [],
      new Datetime('+1 day')
    );
    $this->assertEquals(-2, $balance);

    // Balance including only the five first days of leave request
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement->id,
      [],
      new Datetime('+4 days')
    );
    $this->assertEquals(-5, $balance);

    // The limit date is after the leave request end date, so the whole request
    // will be included
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement->id,
      [],
      new Datetime('+12 days')
    );
    $this->assertEquals(-11, $balance);
  }

  public function testLeaveRequestBalanceForEntitlementCanSumBalanceChangesCreatedByLeaveRequestsOnASpecificDateRange() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $entitlement = $this->createLeavePeriodEntitlement();

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement->id);
    $this->assertEquals(0, $balance);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->id,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    // Balance including the whole leave request
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement->id);
    $this->assertEquals(-11, $balance);

    // Balance including only the third and fourth days of leave request
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement->id,
      [],
      new Datetime('+3 days'),
      new Datetime('+2 days')
    );
    $this->assertEquals(-2, $balance);

    // The start date is before the leave request start date and the limit date
    // is exactly the same day as the request start date, so the balance will
    // include only 1 day
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement->id,
      [],
      new Datetime('now'),
      new Datetime('-1 day')
    );
    $this->assertEquals(-1, $balance);

    // The start date is exactly the same as the  leave request end date
    // and the limit date is one day after the request end date, so the balance
    // will include only 1 day
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement->id,
      [],
      new Datetime('+11 days'),
      new Datetime('+10 days')
    );
    $this->assertEquals(-1, $balance);
  }

  public function testCreateExpirationRecordsCreatesRecordsForExpiredBalanceChanges() {
    $this->createBroughtForwardBalanceChange(1, 5, date('YmdHis', strtotime('-1 day')));
    $this->createBroughtForwardBalanceChange(2, 7, date('YmdHis', strtotime('-8 days')));

    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
    $this->assertEquals(2, $numberOfCreatedRecords);

    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
    $this->assertEquals(0, $numberOfCreatedRecords);
  }

  public function testCreateExpirationRecordsCreatesRecordsEntitlementsWithMultipleExpiredBalanceChanges() {
    // The entitlement with ID 1 has 2 balance changes to expire
    $this->createBroughtForwardBalanceChange(1, 5, date('YmdHis', strtotime('-1 day')));
    $this->createBroughtForwardBalanceChange(1, 7, date('YmdHis', strtotime('-8 days')));

    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
    $this->assertEquals(2, $numberOfCreatedRecords);

    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
    $this->assertEquals(0, $numberOfCreatedRecords);
  }

  public function testCreateExpirationRecordsCalculatesTheExpiredAmountBasedOnTheApprovedLeaveRequestBalance() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $balanceChange = $this->createBroughtForwardBalanceChange(1, 5, date('YmdHis', strtotime('-1 day')));

    //This 1 day approved leave request will be counted
    $this->createLeaveRequestBalanceChange(
      1,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d', strtotime('-10 days'))
    );

    // This 2 days cancelled leave request won't counted
    $this->createLeaveRequestBalanceChange(
      1,
      $leaveRequestStatuses['Cancelled'],
      date('Y-m-d', strtotime('-20 days')),
      date('Y-m-d', strtotime('-21 days'))
    );

    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
    $this->assertEquals(1, $numberOfCreatedRecords);

    $expirationRecord = $this->getExpirationRecordForBalanceChange($balanceChange->id);
    $this->assertNotNull($expirationRecord);
    // Since only the 1 day leave request was counted, 4 days expired
    // 5 - 1 = 4 (we store expired days as a negative number)
    $this->assertEquals(-4, $expirationRecord->amount);
  }

  public function testCreateExpirationRecordsCalculatesPrioritizesAccordingToTheBalanceChangeExpiryDate() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $balanceChange1 = $this->createBroughtForwardBalanceChange(
      1,
      5,
      date('YmdHis', strtotime('-1 day'))
    );
    $balanceChange2 = $this->createBroughtForwardBalanceChange(
      1,
      5,
      date('YmdHis', strtotime('-5 days'))
    );

    // A 7 days approved leave request
    $this->createLeaveRequestBalanceChange(
      1,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d', strtotime('-7 days')),
      date('Y-m-d', strtotime('-1 day'))
    );

    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
    $this->assertEquals(2, $numberOfCreatedRecords);

    $expirationRecord2 = $this->getExpirationRecordForBalanceChange($balanceChange2->id);
    // Balance change 2 expires first, so we also handle it first
    // 3 days of leave request are deducted from it, so 2 days should expire
    $this->assertEquals(-2, $expirationRecord2->amount);

    $expirationRecord1 = $this->getExpirationRecordForBalanceChange($balanceChange1->id);
    // Now we handle the balance change 1, which expires after balance change 2
    // Since we already deducted 3 days, now we just deduct the remaining 4 days
    // meaning only 1 day will expire
    $this->assertEquals(-1, $expirationRecord1->amount);
  }

  public function testCreateExpirationRecordsCalculatesTheExpiredAmountBasedOnlyOnTheApprovedLeaveRequestBalancePriorToTheExpiryDate() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $balanceChange = $this->createBroughtForwardBalanceChange(
      1,
      5,
      date('YmdHis', strtotime('-1 day'))
    );

    // This leave request has 7 days, but only two of them
    // were taken before the brought forward expiry date
    $this->createLeaveRequestBalanceChange(
      1,
      $leaveRequestStatuses['Approved'],
      date('Y-m-d', strtotime('-2 days')),
      date('Y-m-d', strtotime('+5 days'))
    );

    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
    $this->assertEquals(1, $numberOfCreatedRecords);

    $expirationRecord = $this->getExpirationRecordForBalanceChange($balanceChange->id);
    $this->assertNotNull($expirationRecord);
    // Since only two days were taken before the brought forward
    // expiry date, the other 3 days will expire
    $this->assertEquals(-3, $expirationRecord->amount);
  }



  public function testCreateExpirationRecordsDoesNotCreateRecordsForBalanceChangesThatNeverExpire() {
    // A Brought Forward without an expiry date will never expire
    $this->createBroughtForwardBalanceChange(1, 5);

    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
    $this->assertEquals(0, $numberOfCreatedRecords);
  }

  public function testCreateExpirationRecordsDoesNotCreateRecordsForNonExpiredBalanceChanges() {
    $this->createBroughtForwardBalanceChange(1, 5, date('YmdHis', strtotime('+1 day')));

    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
    $this->assertEquals(0, $numberOfCreatedRecords);
  }

  public function testCreateExpirationRecordsDoesCreatesRecordsForExpiredBalanceChanges() {
    $this->createExpiredBroughtForwardBalanceChange(1, 5, 10);

    $numberOfCreatedRecords = LeaveBalanceChange::createExpirationRecords();
    $this->assertEquals(0, $numberOfCreatedRecords);
  }

  private function createLeavePeriodEntitlement() {
    return LeavePeriodEntitlement::create([
      'type_id' => 1,
      'period_id' => 1,
      'contract_id' => 1
    ]);
  }

  private function getExpirationRecordForBalanceChange($balanceChangeID) {
    $record = new LeaveBalanceChange();
    $record->expired_balance_id = $balanceChangeID;
    $record->find();
    if($record->N == 1) {
      $record->fetch();
      return $record;
    }

    return null;
  }
}
