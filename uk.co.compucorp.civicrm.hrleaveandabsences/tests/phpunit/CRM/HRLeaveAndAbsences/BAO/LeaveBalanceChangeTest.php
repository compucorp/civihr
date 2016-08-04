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
      'entitlement_id' => $entitlement->id,
      'type_id' => 1,
      'amount' => 4.3
    ]);

    $this->assertEquals(4.3, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id));

    LeaveBalanceChange::create([
      'entitlement_id' => $entitlement->id,
      'type_id' => 2,
      'amount' => 2
    ]);

    $this->assertEquals(6.3, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id));

    LeaveBalanceChange::create([
      'entitlement_id' => $entitlement->id,
      'type_id' => 2,
      'amount' => -3.5
    ]);

    $this->assertEquals(2.8, LeaveBalanceChange::getBalanceForEntitlement($entitlement->id));

    LeaveBalanceChange::create([
      'entitlement_id' => $entitlement->id,
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
    // part of the breakdown
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

  private function createLeavePeriodEntitlement() {
    return LeavePeriodEntitlement::create([
      'type_id' => 1,
      'period_id' => 1,
      'contract_id' => 1
    ]);
  }
}
