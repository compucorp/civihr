<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange as LeaveBalanceChangeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Factory_LeaveDateAmountDeduction as LeaveDateAmountDeductionFactory;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChangeTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChangeTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_ContractHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeavePeriodEntitlementHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  private $dateAmountDeductionService;

  private $hoursAmountDeductionService;

  public function setUp() {
    // In order to make tests simpler, we disable the foreign key checks,
    // as a way to allow the creation of leave request records related
    // to a non-existing leave period entitlement
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $absenceTypeInDays = AbsenceTypeFabricator::fabricate();
    $absenceTypeInHours = AbsenceTypeFabricator::fabricate(['calculation_unit' => 2]);
    $this->dateAmountDeductionService = LeaveDateAmountDeductionFactory::createForAbsenceType($absenceTypeInDays->id);
    $this->hoursAmountDeductionService = LeaveDateAmountDeductionFactory::createForAbsenceType($absenceTypeInHours->id);
    $this->leaveRequestDayTypes = $this->getLeaveRequestDayTypes();
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 1;');
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
    $leaveRequestStatuses = LeaveRequest::getStatuses();
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+10 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('-10 days')]
    );

    $this->createLeaveBalanceChange($entitlement->id, 10);
    $this->assertEquals(10, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['cancelled'],
      'from_date' => date('YmdHis', strtotime('-10 days')),
      'to_date' => date('YmdHis', strtotime('-10 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['rejected'],
      'from_date' => date('YmdHis', strtotime('-9 days')),
      'to_date' => date('YmdHis', strtotime('-9 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => date('YmdHis', strtotime('-8 days')),
      'to_date' => date('YmdHis', strtotime('-8 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['admin_approved'],
      'from_date' => date('YmdHis', strtotime('-7 days')),
      'to_date' => date('YmdHis', strtotime('-7 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => date('YmdHis', strtotime('-6 days')),
      'to_date' => date('YmdHis', strtotime('-6 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('-6 days')),
      'to_date' => date('YmdHis', strtotime('-6 days'))
    ], true);

    // Including all the balance changes
    $this->assertEquals(4, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    // Only Include balance changes from approved leave requests
    $statusesToInclude = LeaveRequest::getApprovedStatuses();
    $this->assertEquals(8, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statusesToInclude));

    // Only Include balance changes from cancelled/rejected leave requests
    $statusesToInclude = [
      $leaveRequestStatuses['cancelled'],
      $leaveRequestStatuses['rejected'],
    ];
    $this->assertEquals(8, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statusesToInclude));

    // Only Include balance changes from leave requests Awaiting approval
    $statusesToInclude = [ $leaveRequestStatuses['awaiting_approval'] ];
    $this->assertEquals(9, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statusesToInclude));

    // Only Include balance changes from leave requests waiting for more information
    $statusesToInclude = [ $leaveRequestStatuses['more_information_required'] ];
    $this->assertEquals(9, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statusesToInclude));
  }

  public function testBalanceForEntitlementDoesNotSumForSoftDeletedLeaveRequests() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+10 days')
    );

    $this->createLeaveBalanceChange($entitlement->id, 10);
    $this->assertEquals(10, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('-6 days')),
      'to_date' => date('YmdHis', strtotime('-6 days'))
    ], true);

    LeaveRequest::softDelete($leaveRequest->id);

    // The soft deleted leave request is not counted
    $this->assertEquals(10, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    // Only Include balance changes from leave requests waiting for more information
    // but the leave request has been soft deleted and is not accounted for.
    $statusesToInclude = [ $leaveRequestStatuses['more_information_required'] ];
    $this->assertEquals(10, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statusesToInclude));
  }

  public function testBalanceForEntitlementDoesNotSumForExcludedLeaveRequests() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+10 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('-10 days')]
    );

    $this->createLeaveBalanceChange($entitlement->id, 10);

    //Balance change -1
    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => date('YmdHis', strtotime('-8 days')),
      'to_date' => date('YmdHis', strtotime('-8 days'))
    ], true);

    //Balance change -2
    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => date('YmdHis', strtotime('-7 days')),
      'to_date' => date('YmdHis', strtotime('-6 days'))
    ], true);

    //Balance change -1
    $leaveRequest3 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('-5 days')),
      'to_date' => date('YmdHis', strtotime('-5 days'))
    ], true);

    $leaveStatuses = array_values($leaveRequestStatuses);
    //Balance change after leave balances has been deducted
    $this->assertEquals(6, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    //Exclude the balance for leaveRequest1
    //Balances for leaveRequest2 and leaveRequest3 will be deducted
    //10 - (2+1) = 7
    $this->assertEquals(7, LeaveBalanceChange::getBalanceForEntitlement(
      $entitlement,
      $leaveStatuses,
      false,
      [$leaveRequest1->id])
    );

    //Exclude the balance for leaveRequest1 and leaveRequest2
    //Only balance to be deducted is $leaveRequest3 (10 - (1) = 9)
    $this->assertEquals(9, LeaveBalanceChange::getBalanceForEntitlement(
      $entitlement,
      $leaveStatuses,
      false,
      [$leaveRequest2->id, $leaveRequest1->id]
    ));
  }

  public function testBalanceForEntitlementDoesNotSumForLeaveRequestsNotOverlappingAContract() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+10 days')
    );

    $this->createLeaveBalanceChange($entitlement->id, 10);
    $this->assertEquals(10, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      [
        'period_start_date' => date('YmdHis', strtotime('-6 days')),
        'period_end_date' => date('YmdHis', strtotime('-5 days'))
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => date('YmdHis', strtotime('-2 days'))]
    );

    // Leave Request before the first contract
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => date('YmdHis', strtotime('-10 days')),
      'to_date' => date('YmdHis', strtotime('-9 days'))
    ], true);

    // Leave Request between the 2 contracts
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('-4 days')),
      'to_date' => date('YmdHis', strtotime('-3 days'))
    ], true);

    // None of the Leave Requests overlap the contracts, so they won't be included
    // in the sum and the balance will still be 0
    $this->assertEquals(10, LeaveBalanceChange::getBalanceForEntitlement($entitlement));
  }

  public function testBalanceForEntitlementIncludesExpiredBroughtForwardAndTOIL() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+10 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('-10 days')]
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
      $leaveRequestStatuses['approved'],
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
    $leaveRequestStatuses = LeaveRequest::getStatuses();
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+10 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('-10 days')]
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
      $leaveRequestStatuses['approved'],
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
    $leaveRequestStatuses = LeaveRequest::getStatuses();
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+10 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('-10 days')]
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
      $leaveRequestStatuses['approved'],
      CRM_Utils_Date::processDate('-5 days'),
      CRM_Utils_Date::processDate('-5 days'),
      3,
      CRM_Utils_Date::processDate('-1 day'),
      1
    );

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['cancelled'],
      CRM_Utils_Date::processDate('-3 days'),
      CRM_Utils_Date::processDate('-3 days'),
      3,
      CRM_Utils_Date::processDate('-1 day'),
      3
    );

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['awaiting_approval'],
      CRM_Utils_Date::processDate('-1 days'),
      CRM_Utils_Date::processDate('-1 days'),
      5,
      CRM_Utils_Date::processDate('-1 day'),
      2
    );

    $statuses = [$leaveRequestStatuses['approved']];
    // -5 (Expired Brought Forward) - 1 (Expired Approved TOIL)
    $this->assertEquals(-6, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statuses, $expiredOnly));

    $statuses = [$leaveRequestStatuses['approved'], $leaveRequestStatuses['awaiting_approval']];
    // -5 (Expired Brought Forward) - 1 (Expired Approved TOIL) - 2 (Expired Awaiting Approval TOIL)
    $this->assertEquals(-8, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statuses, $expiredOnly));

    $statuses = [$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['awaiting_approval']];
    // -5 (Expired Brought Forward) - 3 (Expired Cancelled TOIL) -2 (Expired Awaiting_Approval TOIL)
    $this->assertEquals(-10, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statuses, $expiredOnly));

    $statuses = [$leaveRequestStatuses['awaiting_approval']];
    // -5 (Expired Brought Forward) - 2 (Expired awaiting_approval TOIL)
    $this->assertEquals(-7, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statuses, $expiredOnly));

    $statuses = [$leaveRequestStatuses['cancelled']];
    // -5 (Expired Brought Forward) - 3 (Expired Cancelled TOIL)
    $this->assertEquals(-8, LeaveBalanceChange::getBalanceForEntitlement($entitlement, $statuses, $expiredOnly));

    $statuses = [$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['approved']];
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
    $leaveRequestStatuses = LeaveRequest::getStatuses();
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
      $leaveRequestStatuses['approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    $breakdownBalance = LeaveBalanceChange::getBreakdownBalanceForEntitlement($entitlement->id);
    $this->assertEquals(35.5, $breakdownBalance);
  }

  public function testLeaveRequestBalanceForEntitlementOnlySumBalanceChangesCreatedByLeaveRequestsWithSpecificStatus() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime(),
      new DateTime('+20 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
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
      $leaveRequestStatuses['approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['admin_approved'],
      date('Y-m-d', strtotime('+11 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['cancelled'],
      date('Y-m-d', strtotime('+12 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['rejected'],
      date('Y-m-d', strtotime('+13 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['awaiting_approval'],
      date('Y-m-d', strtotime('+14 days'))
    );

    // 1 day deducted
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['more_information_required'],
      date('Y-m-d', strtotime('+15 days'))
    );

    // Balance include all the leave requests
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(-16, $leaveRequestBalanceChange);

    // Balance including only approved leave requests
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [ $leaveRequestStatuses['approved'], $leaveRequestStatuses['admin_approved'] ]
    );
    $this->assertEquals(-12, $leaveRequestBalanceChange);

    // Balance including only cancelled or rejected leave requests
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [ $leaveRequestStatuses['cancelled'], $leaveRequestStatuses['rejected'] ]
    );
    $this->assertEquals(-2, $leaveRequestBalanceChange);

    // Balance including only leave requests Awaiting approval
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [ $leaveRequestStatuses['awaiting_approval'] ]
    );
    $this->assertEquals(-1, $leaveRequestBalanceChange);

    // Balance including only leave requests waiting for more information
    $leaveRequestBalanceChange = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      [ $leaveRequestStatuses['more_information_required'] ]
    );
    $this->assertEquals(-1, $leaveRequestBalanceChange);
  }

  public function testLeaveRequestBalanceForEntitlementCanSumBalanceChangesCreatedByLeaveRequestsUpToASpecificDate() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime(),
      new DateTime('+20 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['approved'],
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
    $leaveRequestStatuses = LeaveRequest::getStatuses();
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime(),
      new DateTime('+20 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['approved'],
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
    $leaveRequestStatuses = LeaveRequest::getStatuses();
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('today'),
      new DateTime('+100 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = date('Y-m-d', strtotime('+30 days'));
    $this->fabricatePublicHolidayLeaveRequestWithMockBalanceChange($entitlement->contact_id, $publicHoliday);

    // Balance excluding the days deducted from the leave request
    $excludePublicHolidays = true;
    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement, [], null, null, $excludePublicHolidays);
    $this->assertEquals(-11, $balance);
  }

  public function testLeaveRequestBalanceForEntitlementCanIncludeOnlyBalanceChangesForPublicHolidayLeaveRequests() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('today'),
      new DateTime('+100 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    // This will deduct 11 days
    $this->createLeaveRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    $publicHoliday1 = new PublicHoliday();
    $publicHoliday1->date = date('Y-m-d', strtotime('+30 days'));
    $this->fabricatePublicHolidayLeaveRequestWithMockBalanceChange($entitlement->contact_id, $publicHoliday1);

    $publicHoliday2 = new PublicHoliday();
    $publicHoliday2->date = date('Y-m-d', strtotime('+47 days'));
    $this->fabricatePublicHolidayLeaveRequestWithMockBalanceChange($entitlement->contact_id, $publicHoliday2);

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
    $leaveRequestStatuses = LeaveRequest::getStatuses();
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
      $leaveRequestStatuses['approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    $publicHoliday1 = new PublicHoliday();
    $publicHoliday1->date = date('Y-m-d', strtotime('+30 days'));
    $this->fabricatePublicHolidayLeaveRequestWithMockBalanceChange($entitlement->contact_id, $publicHoliday1);

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

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $entitlement->contact_id,
      'type_id' => $entitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('today'),
      'to_date' => CRM_Utils_Date::processDate('today'),
      'toil_duration' => 360,
      'toil_to_accrue' => 2,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+30 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
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

  public function testLeaveRequestBalanceForEntitlementDoesNotAccountForSoftDeletedLeaveRequests() {
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('today'),
      new DateTime('+100 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(0, $balance);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $entitlement->contact_id,
      'type_id' => $entitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('today'),
      'to_date' => CRM_Utils_Date::processDate('today'),
      'toil_duration' => 360,
      'toil_to_accrue' => 2,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+30 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    // This should have deducted 1 day from the 2 accrued by the toil request
    //but since it will be soft deleted, it will not be accounted for
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $entitlement->contact_id,
      'type_id' => $entitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('+11 days'),
      'to_date' => CRM_Utils_Date::processDate('+11 days')
    ], true);

    //The leave request is soft deleted and therefore will not be accounted for in the calculation
    LeaveRequest::softDelete($leaveRequest->id);

    $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement);
    $this->assertEquals(2, $balance);
  }

  public function testLeaveRequestBalanceForEntitlementDoesNotIncludeExpiredTOILBalanceChangeByDefault() {
    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('today'),
      new DateTime('+100 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
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

  public function testLeaveRequestBalanceForEntitlementDoesNotIncludeBalanceFromLeaveRequestsNotOverlappingContracts() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $entitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+10 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      [
        'period_start_date' => date('YmdHis', strtotime('-6 days')),
        'period_end_date' => date('YmdHis', strtotime('-5 days'))
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $entitlement->contact_id],
      ['period_start_date' => date('YmdHis', strtotime('-2 days'))]
    );

    $this->assertEquals(0, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement));

    // Leave Request before the first contract
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => date('YmdHis', strtotime('-10 days')),
      'to_date' => date('YmdHis', strtotime('-9 days'))
    ], true);

    // Leave Request between the 2 contracts
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('-4 days')),
      'to_date' => date('YmdHis', strtotime('-3 days'))
    ], true);

    // None of the Leave Requests overlap the contracts, so they won't be included
    // in the sum and the balance will still be 0
    $this->assertEquals(0, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($entitlement));
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

  public function testTheLeaveRequestBreakdownShouldNotReturnSoftDeletedLeaveRequests() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-02'),
      'status_id' => 1
    ], true);

    LeaveRequest::softDelete($leaveRequest->id);
    $breakdownBalanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);

    $this->assertCount(0, $breakdownBalanceChanges);
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

  public function testTheTotalBalanceChangeForALeaveRequestShouldBeOnlyTheExpiredAmountWhenExpiredOnlyIsTrue() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-04-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-04-02'),
      'toil_duration' => 10,
      'toil_expiry_date' => CRM_Utils_Date::processDate('2016-06-10'),
      'toil_to_accrue' => 5,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], TRUE);

    $numberOfExpiredDays = 3;
    $this->createExpiryBalanceChangeForTOILRequest($leaveRequest->id, $numberOfExpiredDays);

    $expiredOnly = true;
    $this->assertEquals(-3, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest, $expiredOnly));
  }

  public function testTheTotalBalanceChangeForALeaveRequestShouldBeTheOriginalAmountWhenExpiredOnlyIsFalse() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-04-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-04-02'),
      'toil_duration' => 10,
      'toil_expiry_date' => CRM_Utils_Date::processDate('2016-06-10'),
      'toil_to_accrue' => 5,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], TRUE);

    $numberOfExpiredDays = 3;
    $this->createExpiryBalanceChangeForTOILRequest($leaveRequest->id, $numberOfExpiredDays);

    $expiredOnly = false;
    $this->assertEquals($leaveRequest->toil_to_accrue, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest, $expiredOnly));
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

  public function testGetExistingBalanceChangeForALeaveRequestDateShouldReturnNullIfTheDateIsLinkedToASoftDeletedLeaveRequest() {
    $leaveRequest = new LeaveRequest();
    $leaveRequest->contact_id = 2;
    $leaveRequest->type_id = 1;

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $leaveRequest->contact_id,
      'type_id' => $leaveRequest->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    LeaveRequest::softDelete($leaveRequest->id);

    $leaveBalanceChange = LeaveBalanceChange::getExistingBalanceChangeForALeaveRequestDate(
      $leaveRequest,
      new DateTime('2016-01-01')
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

  public function testCreateExpiryRecordsCalculatesTheExpiredAmountBasedOnTheApprovedLeaveRequestBalanceAndDoesNotAccountForSoftDeletedLeaveRequests() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-30 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $balanceChange = $this->createBroughtForwardBalanceChange(
      $periodEntitlement->id,
      5,
      CRM_Utils_Date::processDate('-1 day')
    );

    //This 1 day approved leave request will be counted
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('-10 days'),
      'to_date' => CRM_Utils_Date::processDate('-10 days')
    ], true);

    //This 1 day approved leave request will not be counted
    //because it got soft deleted after it was created
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('-15 days'),
      'to_date' => CRM_Utils_Date::processDate('-15 days')
    ], true);
    LeaveRequest::softDelete($leaveRequest->id);

    // This 2 days cancelled leave request won't counted
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => $leaveRequestStatuses['cancelled'],
      'from_date' => CRM_Utils_Date::processDate('-20 days'),
      'to_date' => CRM_Utils_Date::processDate('-21 days')
    ], true);

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(1, $numberOfCreatedRecords);

    $expiryRecord = $this->getExpiryRecordForBalanceChange($balanceChange->id);
    $this->assertNotNull($expiryRecord);
    // Since only the 1 day leave request was counted, 4 days expired
    // 5 - 1 = 4 (we store expired days as a negative number)
    $this->assertEquals(-4, $expiryRecord->amount);

    //assert expiry date is same
    $expiryRecordDate = new DateTime($expiryRecord->expiry_date);
    $balanceChangeExpiryDate = new DateTime($balanceChange->expiry_date);
    $this->assertEquals($expiryRecordDate->format('Y-m-d'), $balanceChangeExpiryDate->format('Y-m-d'));

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

    $leaveRequestStatuses = LeaveRequest::getStatuses();

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
      $leaveRequestStatuses['approved'],
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

    //assert expiry date is same
    $expiryRecordDate = new DateTime($expiryRecord1->expiry_date);
    $balanceChangeExpiryDate = new DateTime($balanceChange1->expiry_date);
    $this->assertEquals($expiryRecordDate->format('Y-m-d'), $balanceChangeExpiryDate->format('Y-m-d'));

    $expiryRecordDate2 = new DateTime($expiryRecord2->expiry_date);
    $balanceChangeExpiryDate2 = new DateTime($balanceChange2->expiry_date);
    $this->assertEquals($expiryRecordDate2->format('Y-m-d'), $balanceChangeExpiryDate2->format('Y-m-d'));
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

    $leaveRequestStatuses = LeaveRequest::getStatuses();

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
      $leaveRequestStatuses['approved'],
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

    //assert expiry date is same
    $expiryRecordDate = new DateTime($expiryRecord->expiry_date);
    $balanceChangeExpiryDate = new DateTime($balanceChange->expiry_date);
    $this->assertEquals($expiryRecordDate->format('Y-m-d'), $balanceChangeExpiryDate->format('Y-m-d'));
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

    $leaveRequestStatuses = LeaveRequest::getStatuses();

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
      $leaveRequestStatuses['approved'],
      CRM_Utils_Date::processDate('-2 days'),
      CRM_Utils_Date::processDate('+5 days')
    );

    // This leave request has 7 days, but only four of them
    // were taken before the brought forward expiry date
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement2->type_id,
      $periodEntitlement2->contact_id,
      $leaveRequestStatuses['approved'],
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

    //assert expiry date is same
    $expiryRecordDate = new DateTime($expiryRecord1->expiry_date);
    $balanceChangeExpiryDate = new DateTime($balanceChange1->expiry_date);
    $this->assertEquals($expiryRecordDate->format('Y-m-d'), $balanceChangeExpiryDate->format('Y-m-d'));

    $expiryRecordDate2 = new DateTime($expiryRecord2->expiry_date);
    $balanceChangeExpiryDate2 = new DateTime($balanceChange2->expiry_date);
    $this->assertEquals($expiryRecordDate2->format('Y-m-d'), $balanceChangeExpiryDate2->format('Y-m-d'));
  }

  public function testCreateExpiryRecordsWhenExpiredAmountAbsenceTypeIsDifferentFromTheAbsenceTypeOfTheLeaveRequestDates() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-30 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 2,
    ]);

    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $balanceChange = $this->createBroughtForwardBalanceChange(
      $periodEntitlement2->id,
      5,
      date('YmdHis', strtotime('-1 day'))
    );

    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $leaveRequestStatuses['approved'],
      CRM_Utils_Date::processDate('-2 days'),
      CRM_Utils_Date::processDate('+5 days')
    );

    $numberOfCreatedRecords = LeaveBalanceChange::createExpiryRecords();
    $this->assertEquals(1, $numberOfCreatedRecords);

    $expiryRecord = $this->getExpiryRecordForBalanceChange($balanceChange->id);
    $this->assertNotNull($expiryRecord);

    // The absence type linked to the balance change that expired is different from
    // the absence type on the leave request, therefore the amount remains intact
    $this->assertEquals(-5, $expiryRecord->amount);

    //assert expiry date is same
    $expiryRecordDate = new DateTime($expiryRecord->expiry_date);
    $balanceChangeExpiryDate = new DateTime($balanceChange->expiry_date);
    $this->assertEquals($expiryRecordDate->format('Y-m-d'), $balanceChangeExpiryDate->format('Y-m-d'));
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

    $toilRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'toil_to_accrue' => 1,
      'toil_duration' => 200,
      'toil_expiry_date' => CRM_Utils_Date::processDate('2016-01-30'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    $balanceChange = $this->findToilRequestMainBalanceChange($toilRequest->id);

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
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-30 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('-20 days'),
      'to_date' => CRM_Utils_Date::processDate('-20 days'),
      'toil_to_accrue' => 1,
      'toil_duration' => 200,
      'toil_expiry_date' => CRM_Utils_Date::processDate('-10 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
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
      'type_id' => $this->getBalanceChangeTypeValue('brought_forward')
    ]);

    $toilRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'toil_expiry_date' => CRM_Utils_Date::processDate('2016-02-17'),
      'toil_to_accrue' => 1,
      'toil_duration' => 100,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    $toilRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-02-25'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-25'),
      'toil_expiry_date' => CRM_Utils_Date::processDate('2016-03-01'),
      'toil_to_accrue' => 2,
      'toil_duration' => 100,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
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

  public function testCreateExpiryRecordsCanCalculateTheExpiryAmountWhenTheNumberOfDaysTakenBeforeTheExpiryDateIsBiggerThanTheBalanceChangeAmount() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('2016-01-01')]
    );

    $broughtForwardBalanceChange = LeaveBalanceChangeFabricator::fabricate([
      'source_id' => $periodEntitlement->id,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'amount' => 5,
      'expiry_date' => CRM_Utils_Date::processDate('2016-02-27'),
      'type_id' => $this->getBalanceChangeTypeValue('brought_forward')
    ]);

    $toilRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'toil_expiry_date' => CRM_Utils_Date::processDate('2016-02-26'),
      'toil_to_accrue' => 1,
      'toil_duration' => 100,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
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
      'type_id' => $this->getBalanceChangeTypeValue('brought_forward')
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
      'type_id' => $this->getBalanceChangeTypeValue('brought_forward')
    ]);

    $toilRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'toil_expiry_date' => CRM_Utils_Date::processDate('2016-02-27'),
      'toil_to_accrue' => 1,
      'toil_duration' => 100,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
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
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-30 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('-30 days')]
    );

    $toilRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('-20 days'),
      'to_date' => CRM_Utils_Date::processDate('-20 days'),
      'toil_to_accrue' => 2,
      'toil_duration' => 200,
      'toil_expiry_date' => CRM_Utils_Date::processDate('-10 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'status_id' => $leaveRequestStatuses['cancelled'],
      'from_date' => CRM_Utils_Date::processDate('-15 days'),
      'to_date' => CRM_Utils_Date::processDate('-15 days'),
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'status_id' => $leaveRequestStatuses['approved'],
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

  private function getBalanceChangeRecord($balanceChangeID) {
    $record = new LeaveBalanceChange();
    $record->id = $balanceChangeID;
    $record->find();
    if($record->N == 1) {
      $record->fetch();
      return $record;
    }

    return null;
  }

  private function getExpiryRecordForToilRequest($toilRequestID) {
    $toilBalanceChange = $this->findToilRequestMainBalanceChange($toilRequestID);

    $record = new LeaveBalanceChange();
    $record->expired_balance_change_id = $toilBalanceChange->id;
    $record->find(TRUE);
    if ($record->N == 1) {
      return $record;
    }

    return NULL;
  }

  public function testGetTotalTOILBalanceChangeForContactWithinAGivenPeriodDoesNotAccountForSoftDeletedLeaveRequests() {
    $contactID = 1;
    $absenceTypeID = 1;
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+1 days'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 1,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+100 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+3 days'),
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+100 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+5 days'),
      'to_date' => CRM_Utils_Date::processDate('+6 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 3,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+100 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
    ], true);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+7 days'),
      'to_date' => CRM_Utils_Date::processDate('+7 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+100 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
    ], true);

    LeaveRequest::softDelete($leaveRequest->id);

    $startDate = new DateTime('+3 days');
    $endDate = new DateTime('+7 days');

    //only the last three TOILs fall within the given start and end date period but the last TOIL has been soft deleted
    //and will not be accounted for in the calculation.
    $totalBalanceChange = LeaveBalanceChange::getTotalTOILBalanceChangeForContact($contactID, $absenceTypeID, $startDate, $endDate);
    $this->assertEquals(5, $totalBalanceChange);
  }

  public function testGetTotalTOILBalanceChangeForContactWithinAGivenPeriodAndWithSpecificStatusesAndAbsenceType() {
    $contactID = 1;
    $absenceTypeID = 1;
    $absenceTypeID2 = 2;
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 1,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+100 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['admin_approved'],
      'from_date' => CRM_Utils_Date::processDate('+3 days'),
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+100 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID2,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['admin_approved'],
      'from_date' => CRM_Utils_Date::processDate('+3 days'),
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+100 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => CRM_Utils_Date::processDate('+5 days'),
      'to_date' => CRM_Utils_Date::processDate('+6 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 3,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+100 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    $startDate = new DateTime('+1 day');
    $endDate = new DateTime('+6 days');

    //only the first two TOILs have  Approved and Admin Approved status and also have type_id = 1
    $totalBalanceChange = LeaveBalanceChange::getTotalTOILBalanceChangeForContact(
      $contactID,
      $absenceTypeID,
      $startDate,
      $endDate,
      [$leaveRequestStatuses['admin_approved'], $leaveRequestStatuses['approved']]
    );
    $this->assertEquals(3, $totalBalanceChange);
  }

  public function testGetTotalApprovedToilForPeriodShouldOnlyAccountForApprovedRequests() {
    $contactID = 1;
    $absenceTypeID = 1;
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-06-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-06-30')
    ]);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('2016-06-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-06-03'),
      'toil_to_accrue' => 1,
      'toil_duration' => 120,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['admin_approved'],
      'from_date' => CRM_Utils_Date::processDate('2016-06-04'),
      'to_date' => CRM_Utils_Date::processDate('2016-06-05'),
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => CRM_Utils_Date::processDate('2016-06-07'),
      'to_date' => CRM_Utils_Date::processDate('2016-06-08'),
      'toil_to_accrue' => 3,
      'toil_duration' => 120,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    //only the first two TOILs have  Approved and Admin Approved status
    $totalBalanceChange = LeaveBalanceChange::getTotalApprovedToilForPeriod(
      $period,
      $contactID,
      $absenceTypeID
    );
    $this->assertEquals(3, $totalBalanceChange);
  }

  public function testRecalculateExpiredBalanceChangesForLeaveRequestPastDates() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $broughtForwardPeriod1 = $this->createExpiredBroughtForwardBalanceChange(
      $periodEntitlement1->id,
      1,
      1,
      new DateTime('2016-03-11')
    );

    //This balance change expired before the leave from_date so it will not be affected
    $broughtForward2Period1 = $this->createExpiredBroughtForwardBalanceChange(
      $periodEntitlement1->id,
      5,
      5,
      new DateTime('2016-03-09')
    );

    $accruedToilPeriod1 = $this->createExpiredTOILRequestBalanceChange(
      $periodEntitlement1->type_id,
      $periodEntitlement1->contact_id,
      $leaveRequestStatuses['approved'],
      CRM_Utils_Date::processDate('2016-01-09'),
      CRM_Utils_Date::processDate('2016-01-10'),
      5,
      CRM_Utils_Date::processDate('2016-03-12'),
      5
    );

    //A leave request with past dates with leave request date 2016-03-10 before the date $broughtForwardPeriod1 expired
    //and 2016-03-11 and 2016-03-12 just before $accruedToilPeriod1  expired on 2016-03-12.
    //One day will be deducted for 2016-03-10 from $broughtForwardPeriod1 because it expired before $accruedToilPeriod1
    //making $broughtForwardPeriod1 to be 0 left.
    //Two days will be deducted for 2016-03-11 and 2016-03-12 from $accruedToilPeriod1 i.e (5-2) leaving 3 left
    //$broughtForward2Period1 will not be affected because it expired on 2016-03-09 a day before the leave request date
    //So it will still be 5 left after the recalculation.
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $periodEntitlement1->type_id,
      'contact_id' => $periodEntitlement1->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('2016-03-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-12')
    ], true);

    //Just two records were affected $accruedToilPeriod1 and $broughtForwardPeriod1
    $numberOfUpdatedRecords = LeaveBalanceChange::recalculateExpiredBalanceChangesForLeaveRequestPastDates($leaveRequest);
    $this->assertEquals(2, $numberOfUpdatedRecords);

    $expiredBroughtForwardPeriod1 = $this->getBalanceChangeRecord($broughtForwardPeriod1->id);
    $expiredBroughtForward2Period1 = $this->getBalanceChangeRecord($broughtForward2Period1->id);
    $expiredAccruedToilPeriod1 = $this->getBalanceChangeRecord($accruedToilPeriod1->id);

    $this->assertEquals(0, $expiredBroughtForwardPeriod1->amount);
    $this->assertEquals(-5, $expiredBroughtForward2Period1->amount);
    $this->assertEquals(-3, $expiredAccruedToilPeriod1->amount);


    //assert expiry date are same for expired balance change and the record it expired
    $balanceChangeExpiredByBroughtForwardPeriod1 = $this->getBalanceChangeRecord($expiredBroughtForwardPeriod1->expired_balance_change_id);
    $this->assertEquals($balanceChangeExpiredByBroughtForwardPeriod1->expiry_date, $expiredBroughtForwardPeriod1->expiry_date);

    $balanceChangeExpiredByBroughtForward2Period1 = $this->getBalanceChangeRecord($expiredBroughtForward2Period1->expired_balance_change_id);
    $this->assertEquals($balanceChangeExpiredByBroughtForward2Period1->expiry_date, $expiredBroughtForward2Period1->expiry_date);

    $balanceChangeExpiredByAccruedToilPeriod1 = $this->getBalanceChangeRecord($expiredAccruedToilPeriod1->expired_balance_change_id);
    $this->assertEquals($balanceChangeExpiredByAccruedToilPeriod1->expiry_date, $expiredAccruedToilPeriod1->expiry_date);
  }

  public function testRecalculateExpiredBalanceChangesForLeaveRequestPastDatesWhenSomeLeaveRequestDatesArePastAndOthersAreFuture() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+5 days')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $balanceChangePeriod1 = $this->createExpiredBroughtForwardBalanceChange(
      $periodEntitlement1->id,
      5,
      5,
      2
    );

    // A leave request with past dates with the first day on the day
    // the brought forward balance change expired
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $periodEntitlement1->type_id,
      'contact_id' => $periodEntitlement1->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('-2 days'),
      'to_date' => CRM_Utils_Date::processDate('+1 day')
    ], true);

    $numberOfUpdatedRecords = LeaveBalanceChange::recalculateExpiredBalanceChangesForLeaveRequestPastDates($leaveRequest);
    $this->assertEquals(1, $numberOfUpdatedRecords);

    $expiredBalanceChangePeriod1 = $this->getBalanceChangeRecord($balanceChangePeriod1->id);

    //The first day of the leave request falls on the day $balanceChangePeriod1 expired so only that day is deducted
    //from $balanceChangePeriod1 remaining 4 left after recalculation.
    $this->assertEquals(-4, $expiredBalanceChangePeriod1->amount);

    //assert that both balance changes carry same expiry date
    $balanceChangeExpiredByBalanceChangePeriod1 = $this->getBalanceChangeRecord($expiredBalanceChangePeriod1->expired_balance_change_id);
    $this->assertEquals($balanceChangeExpiredByBalanceChangePeriod1->expiry_date, $expiredBalanceChangePeriod1->expiry_date);
  }

  public function testRecalculateExpiredBalanceChangesForUnApprovedLeaveRequestPastDates() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+5 days')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $balanceChangePeriod1 = $this->createExpiredBroughtForwardBalanceChange(
      $periodEntitlement1->id,
      5,
      5,
      2
    );

    // A leave request with past dates with the first day on the day
    // the brought forward balance change expired but with a Awaiting approval status
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $periodEntitlement1->type_id,
      'contact_id' => $periodEntitlement1->contact_id,
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => CRM_Utils_Date::processDate('-2 days'),
      'to_date' => CRM_Utils_Date::processDate('+1 day')
    ], true);

    $numberOfUpdatedRecords = LeaveBalanceChange::recalculateExpiredBalanceChangesForLeaveRequestPastDates($leaveRequest);
    $this->assertEquals(0, $numberOfUpdatedRecords);

    $expiredBalanceChangePeriod1 = $this->getBalanceChangeRecord($balanceChangePeriod1->id);

    //no recalculation is done because the leave request is not yet approved
    $this->assertEquals(-5, $expiredBalanceChangePeriod1->amount);
  }

  public function testRecalculateExpiredBalanceChangesDoesNotAccountForSoftDeletedLeaveRequests() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+5 days')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $balanceChangePeriod1 = $this->createExpiredBroughtForwardBalanceChange(
      $periodEntitlement1->id,
      5,
      5,
      2
    );

    // A leave request with past dates with the first day on the day
    // the brought forward balance change expired.
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $periodEntitlement1->type_id,
      'contact_id' => $periodEntitlement1->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('-2 days'),
      'to_date' => CRM_Utils_Date::processDate('+1 day')
    ], true);

    LeaveRequest::softDelete($leaveRequest->id);

    //Leave request has been deleted therefore no record was updated.
    $numberOfUpdatedRecords = LeaveBalanceChange::recalculateExpiredBalanceChangesForLeaveRequestPastDates($leaveRequest);
    $this->assertEquals(0, $numberOfUpdatedRecords);

    $expiredBalanceChangePeriod1 = $this->getBalanceChangeRecord($balanceChangePeriod1->id);

    //no recalculation is done because the leave request was deleted and is therefore not accounted for
    $this->assertEquals(-5, $expiredBalanceChangePeriod1->amount);
  }

  public function testRecalculateExpiredBalanceChangesForLeaveRequestWithoutPastDates() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+5 days')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1,
    ]);

    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $balanceChangePeriod1 = $this->createExpiredBroughtForwardBalanceChange(
      $periodEntitlement1->id,
      5,
      5,
      2
    );

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $periodEntitlement1->type_id,
      'contact_id' => $periodEntitlement1->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('today'),
      'to_date' => CRM_Utils_Date::processDate('+2 days')
    ], true);

    $numberOfUpdatedRecords = LeaveBalanceChange::recalculateExpiredBalanceChangesForLeaveRequestPastDates($leaveRequest);
    $this->assertEquals(0, $numberOfUpdatedRecords);

    $expiredBalanceChangePeriod1 = $this->getBalanceChangeRecord($balanceChangePeriod1->id);

    //no recalculation is done because the leave request has no past days that falls on days
    //before the brought forward expired
    $this->assertEquals(-5, $expiredBalanceChangePeriod1->amount);
  }

  public function testRecalculateExpiredBalanceChangesWhenAbsenceTypeOfLeaveRequestIsDifferentFromThatOfExpiredBalanceChange() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+5 days')
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

    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $balanceChangePeriod1 = $this->createExpiredBroughtForwardBalanceChange(
      $periodEntitlement1->id,
      5,
      5,
      2
    );

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $periodEntitlement2->type_id,
      'contact_id' => $periodEntitlement2->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('-2 days'),
      'to_date' => CRM_Utils_Date::processDate('+1 day')
    ], true);

    $numberOfUpdatedRecords = LeaveBalanceChange::recalculateExpiredBalanceChangesForLeaveRequestPastDates($leaveRequest);
    $this->assertEquals(0, $numberOfUpdatedRecords);


    $expiredBalanceChangePeriod1 = $this->getBalanceChangeRecord($balanceChangePeriod1->id);

    //no recalculation is done because the the absence type on the expired brought forward balance change is
    //different from that on the leave request
    $this->assertEquals(-5, $expiredBalanceChangePeriod1->amount);
  }

  public function testCanDeleteTheBalanceChangesForALeavePeriodEntitlement() {
    $leavePeriodEntitlement = $this->createLeavePeriodEntitlement();

    $params = ['source_id' => $leavePeriodEntitlement->id, 'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT];
    LeaveBalanceChangeFabricator::fabricate($params);
    LeaveBalanceChangeFabricator::fabricate($params);
    LeaveBalanceChangeFabricator::fabricate($params);

    $record = $this->getBalanceChangesForPeriodEntitlement($leavePeriodEntitlement);
    $this->assertEquals(3, $record->N);

    LeaveBalanceChange::deleteForLeavePeriodEntitlement($leavePeriodEntitlement);

    $record = $this->getBalanceChangesForPeriodEntitlement($leavePeriodEntitlement);
    $this->assertEquals(0, $record->N);
  }

  public function testGetBalanceForContactsCanIncludeBalanceChangesFromDifferentAbsenceTypes() {
    $absenceType1ID = 1;
    $absenceType2ID = 2;

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $contract2 = HRJobContractFabricator::fabricate(
      ['contact_id' => 2],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract1['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' =>  $absenceType1ID
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract1['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' =>  $absenceType2ID
    ]);

    $periodEntitlement3 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract2['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' =>  $absenceType2ID
    ]);

    $this->createLeaveBalanceChange($periodEntitlement1->id, 10);
    $this->createLeaveBalanceChange($periodEntitlement2->id, 1);
    $this->createLeaveBalanceChange($periodEntitlement3->id, 5);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $periodEntitlement1->type_id,
      'contact_id' => $periodEntitlement1->contact_id,
      'from_date' => date('YmdHis', strtotime('-6 days')),
      'to_date' => date('YmdHis', strtotime('-6 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $periodEntitlement2->type_id,
      'contact_id' => $periodEntitlement2->contact_id,
      'from_date' => date('YmdHis', strtotime('-3 days')),
      'to_date' => date('YmdHis', strtotime('-1 day'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $periodEntitlement3->type_id,
      'contact_id' => $periodEntitlement3->contact_id,
      'from_date' => date('YmdHis', strtotime('-3 days')),
      'to_date' => date('YmdHis', strtotime('-1 day'))
    ], true);

    $result = LeaveBalanceChange::getBalanceForContacts(
      [$periodEntitlement1->contact_id, $periodEntitlement3->contact_id],
      $absencePeriod->id
    );

    $this->assertCount(2, $result);
    $this->assertEquals(9, $result[$periodEntitlement1->contact_id][$periodEntitlement1->type_id]);
    $this->assertEquals(-2, $result[$periodEntitlement2->contact_id][$periodEntitlement2->type_id]);
    $this->assertEquals(2, $result[$periodEntitlement3->contact_id][$periodEntitlement3->type_id]);
  }

  public function testGetBalanceForContactsDoesNotIncludeBalanceChangesFromSoftDeletedLeaveRequests() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $entitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' => 1
    ]);

    $this->createLeaveBalanceChange($entitlement->id, 10);
    $this->assertEquals(10, LeaveBalanceChange::getBalanceForEntitlement($entitlement));

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'from_date' => date('YmdHis', strtotime('-6 days')),
      'to_date' => date('YmdHis', strtotime('-6 days'))
    ], true);

    LeaveRequest::softDelete($leaveRequest->id);

    // The soft deleted leave request is not counted
    $expectedResult = [
      $entitlement->contact_id => [
        $entitlement->type_id => 10
      ]
    ];
    $this->assertEquals($expectedResult, LeaveBalanceChange::getBalanceForContacts(
      [$entitlement->contact_id],
      $entitlement->period_id,
      $entitlement->type_id
    ));
  }

  public function testGetBalanceForContactsIncludesBalanceChangesFromExpiredBroughtForward() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $entitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1
    ]);

    $this->createLeaveBalanceChange($entitlement->id, 10);
    $this->createExpiredBroughtForwardBalanceChange($entitlement->id, 5, 2.5);

    // 10 (Entitlement) + 5 (Brought Forward) + -2.5 (Expired brought forward)
    $expectedResult = [
      $entitlement->contact_id => [
        $entitlement->type_id => 12.5
      ]
    ];
    $this->assertEquals($expectedResult, LeaveBalanceChange::getBalanceForContacts(
      [$entitlement->contact_id],
      $entitlement->period_id,
      $entitlement->type_id
    ));
  }

  public function testGetBalanceForContactsIncludesBalanceChangesFromExpiredToil() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $entitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' => 1
    ]);

    $this->createLeaveBalanceChange($entitlement->id, 10);

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['approved'],
      CRM_Utils_Date::processDate('-5 days'),
      CRM_Utils_Date::processDate('-5 days'),
      1.25,
      CRM_Utils_Date::processDate('-4 days'),
      0.5
    );

    // 10 (Entitlement) + 1.25 (Accrued TOIL) + -0.5 (Expired TOIL)
    $expectedResult = [
      $entitlement->contact_id => [
        $entitlement->type_id => 10.75
      ]
    ];
    $this->assertEquals($expectedResult, LeaveBalanceChange::getBalanceForContacts(
      [$entitlement->contact_id],
      $entitlement->period_id,
      $entitlement->type_id
    ));
  }

  public function testGetBalanceForContactsDoesNotIncludeBalanceChangesFromLeaveRequestsNotOverlappingAContract() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      [
        'period_start_date' => CRM_Utils_Date::processDate('-8 days'),
        'period_end_date' => CRM_Utils_Date::processDate('-5 days')
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contract1['contact_id']],
      [
        'period_start_date' => CRM_Utils_Date::processDate('+2 days'),
        'period_end_date' => CRM_Utils_Date::processDate('+4 days'),
      ]
    );

    $entitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract1['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' => 1
    ]);

    $this->createLeaveBalanceChange($entitlement->id, 5.25);

    // Within the Absence Period but before the first contract
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'from_date' => date('YmdHis', strtotime('-10 days')),
      'to_date' => date('YmdHis', strtotime('-9 days'))
    ], true);

    // Within the first contract. 1 day deducted
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'from_date' => date('YmdHis', strtotime('-8 days')),
      'to_date' => date('YmdHis', strtotime('-8 days'))
    ], true);

    // Within the first contract. 2 days deducted
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'from_date' => date('YmdHis', strtotime('-6 days')),
      'to_date' => date('YmdHis', strtotime('-5 days'))
    ], true);

    // Within the gap period between the two contracts
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'from_date' => date('YmdHis', strtotime('-3 days')),
      'to_date' => date('YmdHis', strtotime('-2 days'))
    ], true);

    // Within the second contract period
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'from_date' => date('YmdHis', strtotime('+2 days')),
      'to_date' => date('YmdHis', strtotime('+2 days'))
    ], true);

    // Within the absence period, but after the end date of the second contract
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'from_date' => date('YmdHis', strtotime('+6 days')),
      'to_date' => date('YmdHis', strtotime('+6 days'))
    ], true);

    // 5.25 (Entitlement) + -1 (2nd leave request) + -2 (3rd leave request) + -1 (5th leave request)
    $expectedResult = [
      $entitlement->contact_id => [
        $entitlement->type_id => 1.25
      ]
    ];

    $this->assertEquals($expectedResult, LeaveBalanceChange::getBalanceForContacts(
      [$entitlement->contact_id],
      $entitlement->period_id,
      $entitlement->type_id
    ));
  }

  public function testGetBalanceForContactsDoesNotIncludeBalanceChangesFromLeaveRequestsOutsideTheGivenAbsencePeriod() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    // Contract starting before the period and ending after it
    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      [
        'period_start_date' => CRM_Utils_Date::processDate('-50 days'),
        'period_end_date' => CRM_Utils_Date::processDate('+50 days')
      ]
    );

    $entitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' => 1
    ]);

    $this->createLeaveBalanceChange($entitlement->id, 5.25);

    // Within the contract, but before the Absence Period. Won't be included
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'from_date' => date('YmdHis', strtotime('-11 days')),
      'to_date' => date('YmdHis', strtotime('-11 days'))
    ], true);

    // Within the contract, but after the Absence Period. Won't be included
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'from_date' => date('YmdHis', strtotime('+11 days')),
      'to_date' => date('YmdHis', strtotime('+11 days'))
    ], true);

    $expectedResult = [
      $entitlement->contact_id => [
        //The original entitlement, with nothing deducted
        $entitlement->type_id => 5.25
      ]
    ];

    $this->assertEquals($expectedResult, LeaveBalanceChange::getBalanceForContacts(
      [$entitlement->contact_id],
      $entitlement->period_id,
      $entitlement->type_id
    ));
  }

  public function testGetBalanceForContactsIncludesPublicHolidays() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $entitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' => 1
    ]);

    $this->createLeaveBalanceChange($entitlement->id, 10);
    $this->createPublicHolidayBalanceChange($entitlement->id, 1);

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = CRM_Utils_Date::processDate('-9 days');
    $this->fabricatePublicHolidayLeaveRequestWithMockBalanceChange(
      $entitlement->contact_id,
      $publicHoliday
    );

    // 10 (Entitlement) + 1 (Public Holiday added to the entitlement) + -1 (Public Holiday Leave Request)
    $expectedResult = [
      $entitlement->contact_id => [
        $entitlement->type_id => 10
      ]
    ];
    $this->assertEquals($expectedResult, LeaveBalanceChange::getBalanceForContacts(
      [$entitlement->contact_id],
      $entitlement->period_id,
      $entitlement->type_id
    ));
  }

  public function testGetBalanceForContactsIncludesOverriddenEntitlement() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $entitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1
    ]);

    $this->createOverriddenBalanceChange($entitlement->id, 20);

    $expectedResult = [
      $entitlement->contact_id => [
        $entitlement->type_id => 20
      ]
    ];
    $this->assertEquals($expectedResult, LeaveBalanceChange::getBalanceForContacts(
      [$entitlement->contact_id],
      $entitlement->period_id,
      $entitlement->type_id
    ));
  }

  public function testGetBalanceForContactsIncludesOnlyTheBalanceChangesOfApprovedRequests() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $entitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract1['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' => 1
    ]);

    $this->createLeaveBalanceChange($entitlement->id, 5.25);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => date('YmdHis', strtotime('-10 days')),
      'to_date' => date('YmdHis', strtotime('-9 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['admin_approved'],
      'from_date' => date('YmdHis', strtotime('-8 days')),
      'to_date' => date('YmdHis', strtotime('-8 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['cancelled'],
      'from_date' => date('YmdHis', strtotime('-7 days')),
      'to_date' => date('YmdHis', strtotime('-7 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['rejected'],
      'from_date' => date('YmdHis', strtotime('-6 days')),
      'to_date' => date('YmdHis', strtotime('-6 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => date('YmdHis', strtotime('-5 days')),
      'to_date' => date('YmdHis', strtotime('-5 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement->type_id,
      'contact_id' => $entitlement->contact_id,
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('-4 days')),
      'to_date' => date('YmdHis', strtotime('-4 days'))
    ], true);

    // 5.25 (Entitlement) + -2 (1st leave request) + -1 (2nd leave request)
    // Neither of the other leave requests are approved, so their balance changes
    // won't be included
    $expectedResult = [
      $entitlement->contact_id => [
        $entitlement->type_id => 2.25
      ]
    ];
    $this->assertEquals($expectedResult, LeaveBalanceChange::getBalanceForContacts(
      [$entitlement->contact_id],
      $entitlement->period_id,
      $entitlement->type_id
    ));
  }

  public function testGetBalanceForContactsCanReturnTheBalanceForMultipleContacts() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => $absencePeriod->start_date]
    );

    // contract ending before the period end date
    $contract2 = HRJobContractFabricator::fabricate(
      ['contact_id' => 2],
      [
        'period_start_date' => $absencePeriod->start_date,
        'period_end_date' => CRM_Utils_Date::processDate('-1 day')
      ]
    );

    $entitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract1['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' => 1
    ]);

    $entitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract2['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' => 1
    ]);

    $this->createLeaveBalanceChange($entitlement1->id, 5.25);
    $this->createExpiredBroughtForwardBalanceChange($entitlement1->id, 5, 4.3);
    $this->createOverriddenBalanceChange($entitlement2->id, 14.75);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement1->type_id,
      'contact_id' => $entitlement1->contact_id,
      'from_date' => date('YmdHis', strtotime('-10 days')),
      'to_date' => date('YmdHis', strtotime('-9 days'))
    ], true);

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement2->type_id,
      $entitlement2->contact_id,
      $leaveRequestStatuses['approved'],
      CRM_Utils_Date::processDate('-5 days'),
      CRM_Utils_Date::processDate('-5 days'),
      1.25,
      CRM_Utils_Date::processDate('-3 days'),
      1
    );

    // This request is after the end of contact2's contract
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $entitlement2->type_id,
      'contact_id' => $entitlement2->contact_id,
      'from_date' => date('YmdHis', strtotime('+2 days')),
      'to_date' => date('YmdHis', strtotime('+2 days'))
    ], true);

    $balances = LeaveBalanceChange::getBalanceForContacts(
      [$entitlement1->contact_id, $entitlement2->contact_id],
      $entitlement1->period_id,
      $entitlement1->type_id
    );

    // Contact 1: 5.25 (Entitlement) + 5 (Brought Forward) - 4.3 (Expired Brought Forward) - 2 (Leave Request)
    $this->assertEquals(3.95, $balances[$entitlement1->contact_id][$entitlement1->type_id]);

    // Contact 2: 14.75 (Overridden Entitlement) + 1.25 (Accrued Toil) - 1 (Expired Toil)
    $this->assertEquals(15, $balances[$entitlement2->contact_id][$entitlement2->type_id]);
  }

  public function testGetOpenLeaveRequestBalanceForContactsCanReturnBalancesForMultipleAbsenceTypes() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $contract2 = HRJobContractFabricator::fabricate(
      ['contact_id' => 2],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $absenceType1ID = 1;
    $absenceType2ID = 2;

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType1ID,
      'contact_id' => $contract1['contact_id'],
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => date('YmdHis', strtotime('-10 days')),
      'to_date' => date('YmdHis', strtotime('-9 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType2ID,
      'contact_id' => $contract1['contact_id'],
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('-8 days')),
      'to_date' => date('YmdHis', strtotime('-6 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType1ID,
      'contact_id' => $contract2['contact_id'],
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => date('YmdHis', strtotime('-6 days')),
      'to_date' => date('YmdHis', strtotime('-3 days'))
    ], true);

    $result = LeaveBalanceChange::getOpenLeaveRequestBalanceForContacts(
      [$contract1['contact_id'], $contract2['contact_id']],
      $absencePeriod->id
    );

    // Both contacts have leave requests, to the results should have two items,
    // one for each contact
    $this->assertCount(2, $result);
    $this->assertEquals(-2, $result[$contract1['contact_id']][$absenceType1ID]);
    $this->assertEquals(-3, $result[$contract1['contact_id']][$absenceType2ID]);
    $this->assertEquals(-4, $result[$contract2['contact_id']][$absenceType1ID]);
  }

  public function testGetOpenLeaveRequestBalanceForContactsShouldIncludeOnlyTheBalanceChangesFromOpenLeaveRequests() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $absenceTypeID = 1;

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => date('YmdHis', strtotime('-10 days')),
      'to_date' => date('YmdHis', strtotime('-9 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['admin_approved'],
      'from_date' => date('YmdHis', strtotime('-8 days')),
      'to_date' => date('YmdHis', strtotime('-7 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => date('YmdHis', strtotime('-6 days')),
      'to_date' => date('YmdHis', strtotime('-5 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('-4 days')),
      'to_date' => date('YmdHis', strtotime('-3 days'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['cancelled'],
      'from_date' => date('YmdHis', strtotime('-2 days')),
      'to_date' => date('YmdHis', strtotime('-1 day'))
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['rejected'],
      'from_date' => date('YmdHis', strtotime('+1 day')),
      'to_date' => date('YmdHis', strtotime('+2 days'))
    ], true);

    // -2 From the awaiting approval request and -2 from the more information
    // required request
    $expectedResult = [
      $contract['contact_id'] => [
        $absenceTypeID => -4
      ]
    ];

    $this->assertEquals($expectedResult, LeaveBalanceChange::getOpenLeaveRequestBalanceForContacts(
      [$contract['contact_id']],
      $absencePeriod->id,
      $absenceTypeID
    ));
  }

  public function testGetOpenLeaveRequestBalanceForContactsShouldIncludeOnlyTheBalanceChangesRequestsOverlappingAContract() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      [
        'period_start_date' => CRM_Utils_Date::processDate('-5 days'),
        'period_end_date' => CRM_Utils_Date::processDate('-1 day')
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => CRM_Utils_Date::processDate('+5 days')]
    );

    $absenceTypeID = 1;

    // before the first contract, won't be included
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => date('YmdHis', strtotime('-10 days')),
      'to_date' => date('YmdHis', strtotime('-9 days'))
    ], true);

    // within first contract, will be included
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('-4 days')),
      'to_date' => date('YmdHis', strtotime('-3 days'))
    ], true);

    // within the gap between contracts, won't be included
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('+2 days')),
      'to_date' => date('YmdHis', strtotime('+4 days'))
    ], true);

    // within the second contract, will be included
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => date('YmdHis', strtotime('+7 days')),
      'to_date' => date('YmdHis', strtotime('+8 days'))
    ], true);

    $expectedResult = [
      $contract['contact_id'] => [
        $absenceTypeID => -4
      ]
    ];

    $this->assertEquals($expectedResult, LeaveBalanceChange::getOpenLeaveRequestBalanceForContacts(
      [$contract['contact_id']],
      $absencePeriod->id,
      $absenceTypeID
    ));
  }

  public function testGetOpenLeaveRequestBalanceForContactsShouldIncludeOnlyTheBalanceChangesRequestsWithinTheGivenAbsencePeriod() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $absencePeriod1 = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $absencePeriod2 = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('+11 days'),
      'end_date' => CRM_Utils_Date::processDate('+21 days')
    ]);

    //Contract spanning the two absence periods
    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => $absencePeriod1->start_date]
    );

    $absenceTypeID = 1;

    //Within the first Absence Period
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => date('YmdHis', strtotime('-10 days')),
      'to_date' => date('YmdHis', strtotime('-5 days')),
    ], true);

    //Within the second Absence Period
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('+15 days')),
      'to_date' => date('YmdHis', strtotime('+17 days'))
    ], true);

    // Not within any of the absence periods and it will not be included
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract['contact_id'],
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('+40 days')),
      'to_date' => date('YmdHis', strtotime('+41 days'))
    ], true);

    $expectedResult = [
      $contract['contact_id'] => [
        $absenceTypeID => -6
      ]
    ];
    $this->assertEquals($expectedResult, LeaveBalanceChange::getOpenLeaveRequestBalanceForContacts(
      [$contract['contact_id']],
      $absencePeriod1->id,
      $absenceTypeID
    ));

    $expectedResult = [
      $contract['contact_id'] => [
        $absenceTypeID => -3
      ]
    ];
    $this->assertEquals($expectedResult, LeaveBalanceChange::getOpenLeaveRequestBalanceForContacts(
      [$contract['contact_id']],
      $absencePeriod2->id,
      $absenceTypeID
    ));
  }

  public function testGetOpenLeaveRequestBalanceForContactsShouldIncludeOnlyTheBalanceChangesForMultipleContacts() {
    $leaveRequestStatuses = LeaveRequest::getStatuses();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      [
        'period_start_date' => CRM_Utils_Date::processDate('-5 days'),
        'period_end_date' => CRM_Utils_Date::processDate('-1 day')
      ]
    );

    $contract2 = HRJobContractFabricator::fabricate(
      ['contact_id' => 2],
      ['period_start_date' => CRM_Utils_Date::processDate('+5 days')]
    );

    $absenceTypeID = 1;

    // within first contract, will be included
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract1['contact_id'],
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => date('YmdHis', strtotime('-4 days')),
      'to_date' => date('YmdHis', strtotime('-2 days'))
    ], true);

    // within first contract, will be included
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract2['contact_id'],
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('+6 days')),
      'to_date' => date('YmdHis', strtotime('+6 days'))
    ], true);


    $result = LeaveBalanceChange::getOpenLeaveRequestBalanceForContacts(
      [$contract1['contact_id'], $contract2['contact_id']],
      $absencePeriod->id,
      $absenceTypeID
    );

    $this->assertCount(2, $result);
    $this->assertEquals(-3, $result[$contract1['contact_id']][$absenceTypeID]);
    $this->assertEquals(-1, $result[$contract2['contact_id']][$absenceTypeID]);
  }

  public function testGetForLeaveRequestDatesReturnsOnTheBalanceChangesLinkedToTheGiveLeaveRequestDates() {
    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => 1,
      'from_date' => date('YmdHis', strtotime('-4 days')),
      'to_date' => date('YmdHis', strtotime('-3 days'))
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => 1,
      'from_date' => date('YmdHis', strtotime('-5 days')),
      'to_date' => date('YmdHis', strtotime('-5 days'))
    ], true);

    $dates = $leaveRequest1->getDates();
    $balanceChanges = LeaveBalanceChange::getForLeaveRequestDates($dates);
    $this->assertCount(2, $balanceChanges);
    $this->assertNotNull($balanceChanges[$dates[0]->id]);
    $this->assertNotNull($balanceChanges[$dates[1]->id]);

    $dates = $leaveRequest2->getDates();
    $balanceChanges = LeaveBalanceChange::getForLeaveRequestDates($dates);
    $this->assertCount(1, $balanceChanges);
    $this->assertNotNull($balanceChanges[$dates[0]->id]);
  }

  public function testCalculateAmountForDateReturnsCorrectly() {
    $leaveRequest = new LeaveRequest();
    $leaveRequest->contact_id = 1;

    $amountToReturn = -2;
    $amount = $this->calculateAmountForDate($leaveRequest, new DateTime('2016-07-28'), $amountToReturn);
    $this->assertEquals($amountToReturn, $amount);

    $amountToReturn = -1;
    $amount = $this->calculateAmountForDate($leaveRequest, new DateTime('2016-07-29'), $amountToReturn);
    $this->assertEquals($amountToReturn, $amount);

    $amountToReturn = -8;
    $amount = $this->calculateAmountForDate($leaveRequest, new DateTime('2016-07-29 14:00'), $amountToReturn);
    $this->assertEquals($amountToReturn, $amount);

    $amountToReturn = -4.5;
    $amount = $this->calculateAmountForDate($leaveRequest, new DateTime('2016-07-29 13:00'), $amountToReturn);
    $this->assertEquals($amountToReturn, $amount);
  }

  private function getBalanceChangesForPeriodEntitlement($leavePeriodEntitlement) {
    $record = new LeaveBalanceChange();
    $record->source_id = $leavePeriodEntitlement->id;
    $record->source_type = LeaveBalanceChange::SOURCE_ENTITLEMENT;
    $record->find();
    return $record;
  }

  private function calculateAmountForDate(LeaveRequest $leaveRequest, DateTime $date, $amount) {
    $dayAmountDeductionService = $this->createLeaveDateAmountDeductionServiceMock($amount);
    $contactWorkPatternService = $this->createContractWorkPatternServiceMock();
    return LeaveBalanceChange::calculateAmountForDate($leaveRequest, $date, $dayAmountDeductionService, $contactWorkPatternService);
  }
}
