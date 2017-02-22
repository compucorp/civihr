<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveBalanceChangeTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveBalanceChangeTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;

  private $leaveBalanceChangeService;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $this->leaveBalanceChangeService = new LeaveBalanceChangeService();
  }

  public function testItCanCreateBalanceChangesForALeaveRequest() {
    $contact = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $leaveRequestDateTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));

    // a 9 days leave request, from friday to saturday of the next week
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $leaveRequestDateTypes['all_day'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-09'),
      'to_date_type' => $leaveRequestDateTypes['all_day'],
    ]);

    $this->leaveBalanceChangeService->createForLeaveRequest($leaveRequest);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used and there are 3 weekend days on the
    // leave period (2 saturdays and 1 sunday), the balance change will be -6
    // (the working days of all the 9 days requested)
    $this->assertEquals(-6, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is -6, we must have 9 balance changes, one for
    // each date
    $this->assertCount(9, $balanceChanges);
  }

  public function testItCanCreateBalanceChangesForALeaveRequestOfTypeTOIL() {
    $leaveRequestDateTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));

    $expiryDate = new DateTime('2016-03-01');

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $leaveRequestDateTypes['all_day'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-02'),
      'to_date_type' => $leaveRequestDateTypes['all_day'],
      'toil_to_accrue' => 2,
      'toil_duration' => 500,
      'toil_expiry_date' => $expiryDate->format('YmdHis'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);

    $this->leaveBalanceChangeService->createForLeaveRequest($leaveRequest);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // It should be the same as the toil_to_accrue amount
    $this->assertEquals(2, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // There should have 2 balance changes, one for each of the dates of the request
    $this->assertCount(2, $balanceChanges);

    // The toil_to_accrue amount is stored only on the balance change for the
    // first date. For all the other dates, the amount should be 0. The expiry
    // date is also only store in the balance change for the first date.
    $this->assertEquals(2, $balanceChanges[0]->amount);
    $this->assertEquals($expiryDate->format('Y-m-d'), $balanceChanges[0]->expiry_date);
    $this->assertEquals(0, $balanceChanges[1]->amount);
    $this->assertNull($balanceChanges[1]->expiry_date);
  }
}
