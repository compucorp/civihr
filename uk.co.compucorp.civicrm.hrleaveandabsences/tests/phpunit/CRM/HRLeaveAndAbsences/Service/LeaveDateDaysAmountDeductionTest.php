<?php

use CRM_HRLeaveAndAbsences_Service_LeaveDateDaysAmountDeduction as LeaveDateDaysAmountDeduction;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

/**
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveDateDaysAmountDeductionTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  private $leaveDaysAmountDeductionService;

  private $leaveRequest;

  public function setUp() {
    $this->leaveDaysAmountDeductionService = new LeaveDateDaysAmountDeduction();
    $this->leaveRequestDayTypes = $this->getLeaveRequestDayTypes();
    $this->leaveRequest = new LeaveRequest();
  }

  public function testCalculateReturnsTheCorrectAmountWhenFromDateTypeIsHalfDay() {
    $params = [
      'from_date_type' => $this->leaveRequestDayTypes['half_day_am']['value'],
      'from_date' => '2016-01-01'
    ];
    $leaveRequest = $this->getLeaveRequestInstance($params);
    $workDay = $this->getWorkDay();
    $amount = $this->leaveDaysAmountDeductionService->calculate(new DateTime($params['from_date']), $workDay, $leaveRequest);

    $this->assertEquals(0.5, $amount);
  }

  public function testCalculateReturnsTheCorrectAmountWhenToDateTypeIsHalfDay() {
    $params = [
      'to_date_type' => $this->leaveRequestDayTypes['half_day_am']['value'],
      'to_date' => '2016-01-04'
    ];
    $leaveRequest = $this->getLeaveRequestInstance($params);
    $workDay = $this->getWorkDay();
    $amount = $this->leaveDaysAmountDeductionService->calculate(new DateTime($params['to_date']), $workDay, $leaveRequest);

    $this->assertEquals(0.5, $amount);
  }

  public function testCalculateReturnsTheCorrectAmountWhenToDateTypeIsFullDay() {
    $params = [
      'to_date' => '2016-01-04'
    ];
    $leaveRequest = $this->getLeaveRequestInstance($params);
    $workDay = $this->getWorkDay();
    $amount = $this->leaveDaysAmountDeductionService->calculate(new DateTime($params['to_date']), $workDay, $leaveRequest);

    $this->assertEquals($workDay['leave_days'], $amount);
  }

  public function testCalculateReturnsTheCorrectAmountWhenFromDateTypeIsFullDay() {
    $params = [
      'from_date' => '2016-01-04'
    ];
    $leaveRequest = $this->getLeaveRequestInstance($params);
    $workDay = $this->getWorkDay();
    $amount = $this->leaveDaysAmountDeductionService->calculate(new DateTime($params['from_date']), $workDay, $leaveRequest);

    $this->assertEquals($workDay['leave_days'], $amount);
  }

  public function testCalculateReturnsTheCorrectAmountForLeaveDateInBetweenFromAndEndDates() {
    $params = [
      'from_date' => '2016-01-04',
      'to_date' => '2016-01-08'
    ];
    $leaveRequest = $this->getLeaveRequestInstance($params);
    $workDay = $this->getWorkDay();
    $amount = $this->leaveDaysAmountDeductionService->calculate(new DateTime('2016-01-07'), $workDay, $leaveRequest);

    $this->assertEquals($workDay['leave_days'], $amount);
  }

  private function getLeaveRequestInstance($params = []) {
    $defaultParams = [
      'from_date' => '2016-01-01',
      'to_date' => '2026-01-04',
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value']
    ];

    $params = array_merge($defaultParams, $params);
    $leaveRequest = new LeaveRequest();
    $leaveRequest->copyValues($params);

    return $leaveRequest;
  }

  private function getWorkDay() {
    return ['leave_days' => 1];
  }
}
