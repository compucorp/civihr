<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveHoursBalanceChangeCalculation as LeaveHoursBalanceChangeCalculation;

/**
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveHoursBalanceChangeCalculationTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  public function testGetAmountReturnsCorrectlyForFromDate() {
    $leaveDaysCalculationService = new leaveHoursBalanceChangeCalculation();
    $leaveRequest = $this->getLeaveRequestInstance();
    $balanceChanges = $this->getBalanceChanges();
    $amount = $leaveDaysCalculationService->getAmount($leaveRequest, new DateTime('2016-06-01'), $balanceChanges);

    $this->assertEquals($leaveRequest->from_date_amount, $amount);
  }

  public function testGetAmountReturnsCorrectlyForToDate() {
    $leaveDaysCalculationService = new leaveHoursBalanceChangeCalculation();
    $leaveRequest = $this->getLeaveRequestInstance();
    $balanceChanges = $this->getBalanceChanges();
    $amount = $leaveDaysCalculationService->getAmount($leaveRequest, new DateTime('2016-06-04'), $balanceChanges);

    $this->assertEquals($leaveRequest->to_date_amount, $amount);
  }

  public function testGetAmountReturnsCorrectlyForDatesInBetweenFromAndToDates() {
    $leaveDaysCalculationService = new leaveHoursBalanceChangeCalculation();
    $leaveRequest = $this->getLeaveRequestInstance();
    $balanceChanges = $this->getBalanceChanges();
    $amount = $leaveDaysCalculationService->getAmount($leaveRequest, new DateTime('2016-06-02'), $balanceChanges);
    $this->assertEquals($balanceChanges['breakdown']['2016-06-02']['amount'], $amount);

    $amount = $leaveDaysCalculationService->getAmount($leaveRequest, new DateTime('2016-06-03'), $balanceChanges);
    $this->assertEquals($balanceChanges['breakdown']['2016-06-03']['amount'], $amount);
  }

  public function testGetAmountReturnsZeroForFromDateWhenDeductionNotAllowedForFromDate() {
    $leaveDaysCalculationService = new leaveHoursBalanceChangeCalculation();
    $leaveRequest = $this->getLeaveRequestInstance();
    $balanceChanges = $this->getBalanceChanges();
    $balanceChanges['breakdown']['2016-06-01']['amount'] = 0;

    $amount = $leaveDaysCalculationService->getAmount($leaveRequest, new DateTime('2016-06-01'), $balanceChanges);
    $this->assertEquals(0, $amount);
  }

  public function testGetAmountReturnsZeroForToDateWhenDeductionNotAllowedForToDate() {
    $leaveDaysCalculationService = new leaveHoursBalanceChangeCalculation();
    $leaveRequest = $this->getLeaveRequestInstance();
    $balanceChanges = $this->getBalanceChanges();
    $balanceChanges['breakdown']['2016-06-04']['amount'] = 0;

    $amount = $leaveDaysCalculationService->getAmount($leaveRequest, new DateTime('2016-06-04'), $balanceChanges);
    $this->assertEquals(0, $amount);
  }

  private function getBalanceChanges() {
   return [
      'breakdown' => [
        '2016-06-01' => ['amount' => 1],
        '2016-06-02' => ['amount' => 2],
        '2016-06-03' => ['amount' => 3],
        '2016-06-04' => ['amount' => 4]
      ],
    ];
  }

  private function getLeaveRequestInstance() {
    $params = [
      'from_date' => '2016-06-01 ',
      'to_date' => '2016-06-04',
      'to_date_amount' => 5,
      'from_date_amount' => 6
    ];

    $leaveRequest = new LeaveRequest();
    $leaveRequest->copyValues($params);

    return $leaveRequest;
  }
}
