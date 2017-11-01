<?php

use CRM_HRLeaveAndAbsences_Service_LeaveDateHoursAmountDeduction as LeaveDateHoursAmountDeduction;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

/**
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveDateHoursAmountDeductionTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  private $leaveHoursAmountDeductionService;

  private $leaveRequest;

  public function setUp() {
    $this->leaveHoursAmountDeductionService = new LeaveDateHoursAmountDeduction();
    $this->leaveRequest = new LeaveRequest();
  }

  public function testCalculateReturnsTheCorrectAmountForFromDate() {
    $params = [
      'from_date' => '2016-01-01 13:00'
    ];
    $leaveRequest = $this->getLeaveRequestInstance($params);
    $workDay = $this->getWorkDay();
    $amount = $this->leaveHoursAmountDeductionService->calculate(new DateTime($params['from_date']), $workDay, $leaveRequest);

    $this->assertEquals($workDay['number_of_hours'], $amount);
  }

  public function testCalculateReturnsTheCorrectAmountForToDate() {
    $params = [
      'to_date' => '2016-01-04 15:00'
    ];
    $leaveRequest = $this->getLeaveRequestInstance($params);
    $workDay = $this->getWorkDay();
    $amount = $this->leaveHoursAmountDeductionService->calculate(new DateTime($params['to_date']), $workDay, $leaveRequest);

    $this->assertEquals($workDay['number_of_hours'], $amount);
  }

  public function testCalculateReturnsTheCorrectAmountForToDateInBetweenLeaveDates() {
    $params = [
      'from_date' => '2016-01-04 13:00',
      'to_date' => '2016-01-08 17:00'
    ];
    $leaveRequest = $this->getLeaveRequestInstance($params);
    $workDay = $this->getWorkDay();
    $amount = $this->leaveHoursAmountDeductionService->calculate(new DateTime('2016-01-07 00:00'), $workDay, $leaveRequest);

    $this->assertEquals($workDay['number_of_hours'], $amount);
  }

  private function getLeaveRequestInstance($params = []) {
    $defaultParams = [
      'from_date' => '2016-01-01 ',
      'to_date' => '2026-01-04',
    ];

    $params = array_merge($defaultParams, $params);
    $leaveRequest = new LeaveRequest();
    $leaveRequest->copyValues($params);

    return $leaveRequest;
  }

  private function getWorkDay() {
    return ['number_of_hours' => 8];
  }
}
