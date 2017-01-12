<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_SicknessRequest as SicknessRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequest as LeaveRequestService;
use CRM_HRLeaveAndAbsences_Service_SicknessRequest as SicknessRequestService;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_SicknessRequest as SicknessRequestFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_SicknessRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_SicknessRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  public function testCreateAlsoCreateTheLeaveRequestBalanceChanges() {
    $contact = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $balanceChangeService = new LeaveBalanceChangeService();
    $service = new SicknessRequestService(
      $balanceChangeService,
      new LeaveRequestService($balanceChangeService)
    );

    $sicknessReasons = array_flip(SicknessRequest::buildOptions('reason', 'validate'));

    // a 9 days request, from thursday to friday next week
    $sicknessRequest = $service->create([
      'type_id' => 1,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-11'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-02-19'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'reason' => $sicknessReasons['accident']
    ], false);

    $leaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used and there are 7 working days during
    // the 9 days period of the requests, the balance will be -7
    $this->assertEquals(-7, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is -7, we must have 9 balance changes, one for
    // each date
    $this->assertCount(9, $balanceChanges);
  }

  public function testCreateDoesNotDuplicateLeaveBalanceChangesOnUpdate() {
    $contact = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $balanceChangeService = new LeaveBalanceChangeService();
    $service = new SicknessRequestService(
      $balanceChangeService,
      new LeaveRequestService($balanceChangeService)
    );

    $sicknessReasons = array_flip(SicknessRequest::buildOptions('reason', 'validate'));

    $params = [
      'type_id' => 1,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-07'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'reason' => $sicknessReasons['accident']
    ];

    // a 7 days leave request, from friday to thursday
    $sicknessRequest = $service->create($params, false);

    $leaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used, and it this is a week long
    // leave request, the balance will be -5 (for the 5 working days)
    $this->assertEquals(-5, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is -5, we must have 7 balance changes, one for
    // each date
    $this->assertCount(7, $balanceChanges);

    // Increase the Leave Request period by 4 days (2 weekend + 2 working days)
    $params['id'] = $sicknessRequest->id;
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-11');
    $sicknessRequest = $service->create($params, false);

    // the associated leave request is still the same
    $this->assertEquals($leaveRequest->id, $sicknessRequest->leave_request_id);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // -5 from before - 2 (from the 2 new working days)
    $this->assertEquals(-7, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // 7 from before + 4 from the new period
    $this->assertCount(11, $balanceChanges);
  }

  public function testDeleteDeletesTheLeaveRequestItsBalanceChangesAndDates() {
    $sicknessRequest = SicknessRequestFabricator::fabricateWithoutValidation([
      'type_id'        => 1,
      'contact_id'     => 1,
      'status_id'      => 1,
      'from_date'      => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date'        => CRM_Utils_Date::processDate('2016-01-07'),
      'to_date_type'   => $this->getLeaveRequestDayTypes()['All Day']['value'],
    ], TRUE);

    $leaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $dates          = $leaveRequest->getDates();
    $this->assertCount(7, $balanceChanges);
    $this->assertCount(7, $dates);

    $balanceChangeService = new LeaveBalanceChangeService();
    $service = new SicknessRequestService(
      $balanceChangeService,
      new LeaveRequestService($balanceChangeService)
    );
    $service->delete($sicknessRequest->id);

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
