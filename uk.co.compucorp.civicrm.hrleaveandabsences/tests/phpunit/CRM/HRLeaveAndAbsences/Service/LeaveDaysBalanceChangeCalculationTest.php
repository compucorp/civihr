<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveDaysBalanceChangeCalculation as LeaveDaysBalanceChangeCalculation;

/**
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveDaysBalanceChangeCalculationTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  public function testGetAmountReturnsCorrectly() {
    $leaveDaysCalculationService = new leaveDaysBalanceChangeCalculation();
    $leaveRequest = $this->getLeaveRequestInstance();
    $balanceChanges = $this->getBalanceChanges();
    $amount = $leaveDaysCalculationService->getAmount($leaveRequest, new DateTime('2016-06-01'), $balanceChanges);
    $this->assertEquals($amount, $balanceChanges['breakdown']['2016-06-01']['amount']);

    $amount = $leaveDaysCalculationService->getAmount($leaveRequest, new DateTime('2016-06-02'), $balanceChanges);
    $this->assertEquals($amount, $balanceChanges['breakdown']['2016-06-02']['amount']);

    $amount = $leaveDaysCalculationService->getAmount($leaveRequest, new DateTime('2016-06-03'), $balanceChanges);
    $this->assertEquals($amount, $balanceChanges['breakdown']['2016-06-03']['amount']);

    $amount = $leaveDaysCalculationService->getAmount($leaveRequest, new DateTime('2016-06-04'), $balanceChanges);
    $this->assertEquals($amount, $balanceChanges['breakdown']['2016-06-04']['amount']);
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
    ];

    $leaveRequest = new LeaveRequest();
    $leaveRequest->copyValues($params);

    return $leaveRequest;
  }
}
