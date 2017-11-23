<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveHoursBalanceChangeCalculation as LeaveHoursBalanceChangeCalculation;

/**
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveHoursBalanceChangeCalculationTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  private $leaveHoursCalculationService;

  public function setUp() {
    $this->leaveHoursCalculationService = new LeaveHoursBalanceChangeCalculation();
  }

  public function testGetAmountReturnsCorrectlyForFromDate() {
    $leaveRequest = $this->getLeaveRequestInstance();
    $balanceChanges = $this->getBalanceChanges();
    $amount = $this->leaveHoursCalculationService->getAmount($leaveRequest, new DateTime($leaveRequest->from_date), $balanceChanges);

    $this->assertEquals($leaveRequest->from_date_amount, $amount);
  }

  public function testGetAmountReturnsCorrectlyForToDate() {
    $leaveRequest = $this->getLeaveRequestInstance();
    $balanceChanges = $this->getBalanceChanges();
    $amount = $this->leaveHoursCalculationService->getAmount($leaveRequest, new DateTime($leaveRequest->to_date), $balanceChanges);

    $this->assertEquals($leaveRequest->to_date_amount, $amount);
  }

  public function testGetAmountReturnsCorrectlyForDatesInBetweenFromAndToDates() {
    $leaveRequest = $this->getLeaveRequestInstance();
    $balanceChanges = $this->getBalanceChanges();
    $amount = $this->leaveHoursCalculationService->getAmount($leaveRequest, new DateTime('2016-06-02'), $balanceChanges);
    $this->assertEquals($balanceChanges['breakdown']['2016-06-02']['amount'], $amount);

    $amount = $this->leaveHoursCalculationService->getAmount($leaveRequest, new DateTime('2016-06-03'), $balanceChanges);
    $this->assertEquals($balanceChanges['breakdown']['2016-06-03']['amount'], $amount);
  }

  public function testGetAmountReturnsZeroForFromDateWhenDeductionNotAllowedForFromDate() {
    $leaveRequest = $this->getLeaveRequestInstance();
    $balanceChanges = $this->getBalanceChanges();
    $balanceChanges['breakdown']['2016-06-01']['amount'] = 0;

    $amount = $this->leaveHoursCalculationService->getAmount($leaveRequest, new DateTime('2016-06-01'), $balanceChanges);
    $this->assertEquals(0, $amount);
  }

  public function testGetAmountReturnsZeroForToDateWhenDeductionNotAllowedForToDate() {
    $leaveRequest = $this->getLeaveRequestInstance();
    $balanceChanges = $this->getBalanceChanges();
    $balanceChanges['breakdown']['2016-06-04']['amount'] = 0;

    $amount = $this->leaveHoursCalculationService->getAmount($leaveRequest, new DateTime('2016-06-04'), $balanceChanges);
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
