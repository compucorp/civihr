<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequest as LeaveRequestService;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  public function testCreateAlsoCreateTheLeaveRequestBalanceChanges() {
    $contact = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $leaveRequestService = new LeaveRequestService(new LeaveBalanceChangeService());

    // a 7 days leave request, from monday to sunday
    $leaveRequest = $leaveRequestService->create([
      'type_id' => 1,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-04'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-10'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
    ], false);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used, and it this is a week long
    // leave request, the balance will be -5 (for the 5 working days)
    $this->assertEquals(-5, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is 5, we must have 7 balance changes, one for
    // each date
    $this->assertCount(7, $balanceChanges);
  }

  public function testCreateDoesNotDuplicateLeaveBalanceChangesOnUpdate() {
    $contact = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $leaveRequestService = new LeaveRequestService(new LeaveBalanceChangeService());

    // a 7 days leave request, from friday to thursday
    $params = [
      'type_id' => 1,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-07'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
    ];

    $leaveRequest = $leaveRequestService->create($params, false);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used, and it this is a week long
    // leave request, the balance will be 5 (for the 5 working days)
    $this->assertEquals(-5, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is 5, we must have 7 balance changes, one for
    // each date
    $this->assertCount(7, $balanceChanges);

    // Increase the Leave Request period by 4 days (2 weekend + 2 working days)
    $params['id'] = $leaveRequest->id;
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-11');
    $leaveRequestService->create($params, false);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // 5 from before + 2 (from the 2 new working days)
    $this->assertEquals(-7, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // 7 from before + 4 from the new period
    $this->assertCount(11, $balanceChanges);
  }

  public function testDeleteDeletesTheLeaveRequestItsBalanceChangesAndDates() {
    $leaveRequestDateTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id'        => 1,
      'contact_id'     => 1,
      'status_id'      => 1,
      'from_date'      => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $leaveRequestDateTypes['all_day'],
      'to_date'        => CRM_Utils_Date::processDate('2016-01-07'),
      'to_date_type'   => $leaveRequestDateTypes['all_day'],
    ], TRUE);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $dates          = $leaveRequest->getDates();
    $this->assertCount(7, $balanceChanges);
    $this->assertCount(7, $dates);

    $leaveRequestService = new LeaveRequestService(new LeaveBalanceChangeService());
    $leaveRequestService->delete($leaveRequest->id);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $dates          = $leaveRequest->getDates();
    $this->assertCount(0, $balanceChanges);
    $this->assertCount(0, $dates);

    try {
      $leaveRequest = LeaveRequest::findById($leaveRequest->id);
    } catch (Exception $e) {
      return;
    }

    $this->fail("Expected to not find the LeaveRequest with {$leaveRequest->id}, but it was found");
  }
}
