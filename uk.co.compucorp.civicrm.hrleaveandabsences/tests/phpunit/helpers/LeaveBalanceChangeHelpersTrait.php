<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange as LeaveBalanceChangeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestFabricator;

trait CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait {

  private $balanceChangeTypes = [];

  public function getBalanceChangeTypeValue($type) {
    if(empty($this->balanceChangeTypes)) {
      $this->balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id', 'validate'));
    }

    return $this->balanceChangeTypes[$type];
  }

  public function createEntitlementBalanceChange($entitlementID, $amount, $type, $expiryDate = null) {
    $params = [
      'type_id' => $type,
      'source_id' => $entitlementID,
      'source_type' => 'entitlement',
      'amount' => $amount
    ];

    if($expiryDate) {
      $params['expiry_date'] = $expiryDate;
    }

    return LeaveBalanceChange::create($params);
  }

  public function createLeaveBalanceChange($entitlementID, $amount) {
    return $this->createEntitlementBalanceChange(
      $entitlementID,
      $amount,
      $this->getBalanceChangeTypeValue('leave')
    );
  }

  public function createOverriddenBalanceChange($entitlementID, $amount) {
    return $this->createEntitlementBalanceChange(
      $entitlementID,
      $amount,
      $this->getBalanceChangeTypeValue('overridden')
    );
  }

  public function createBroughtForwardBalanceChange($entitlementID, $amount, $expiryDate = null) {
    return $this->createEntitlementBalanceChange(
      $entitlementID,
      $amount,
      $this->getBalanceChangeTypeValue('brought_forward'),
      $expiryDate
    );
  }

  public function createPublicHolidayBalanceChange($entitlementID, $amount) {
    return $this->createEntitlementBalanceChange(
      $entitlementID,
      $amount,
      $this->getBalanceChangeTypeValue('public_holiday')
    );
  }

  public function createExpiredBroughtForwardBalanceChange($entitlementID, $amount, $expiredAmount, $expiry = 2) {
    if ($expiry instanceof DateTime) {
      $expiryDate = $expiry->format('YmdHis');
    }

    if (is_int($expiry)) {
      $expiryDate = date('YmdHis', strtotime("-{$expiry} day"));
    }

    if ($expiry == null) {
      $expiryDate = null;
    }

    $this->createEntitlementBalanceChange(
      $entitlementID,
      $amount,
      $this->getBalanceChangeTypeValue('brought_forward'),
      $expiryDate
    );

    $broughtForwardBalanceChangeID = $this->getLastIdInTable(LeaveBalanceChange::getTableName());

    return LeaveBalanceChange::create([
      'type_id' => $this->getBalanceChangeTypeValue('brought_forward'),
      'source_id' => $entitlementID,
      'source_type' => 'entitlement',
      'amount' => $expiredAmount * -1, //expired amounts should be negative
      'expired_balance_change_id' => $broughtForwardBalanceChangeID,
      'expiry_date' => $expiryDate
    ]);
  }

  public function createExpiredTOILRequestBalanceChange($typeID, $contactID, $status, $fromDate, $toDate, $toilToAccrue, $expiryDate, $expiredAmount) {
    $toilRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $typeID,
      'contact_id' => $contactID,
      'status_id' => $status,
      'from_date' => $fromDate,
      'to_date' => $toDate,
      'toil_to_accrue' => $toilToAccrue,
      'toil_duration' => 200,
      'toil_expiry_date' => $expiryDate,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    return $this->createExpiryBalanceChangeForTOILRequest($toilRequest->id, $expiredAmount);
  }

  public function createExpiryBalanceChangeForTOILRequest($toilRequestID, $expiredAmount) {
    $toilBalanceChange = $this->findToilRequestMainBalanceChange($toilRequestID);

    return LeaveBalanceChangeFabricator::fabricate([
      'source_id' => $toilBalanceChange->source_id,
      'source_type' => $toilBalanceChange->source_type,
      'amount' => $expiredAmount * -1,
      'expiry_date' => CRM_Utils_Date::processDate($toilBalanceChange->expiry_date),
      'expired_balance_change_id' => $toilBalanceChange->id,
      'type_id' => $this->getBalanceChangeTypeValue('debit')
    ]);
  }

  /**
   * This is a helper method to create the fixtures for a Leave Request and its
   * respective dates and balance changes.
   *
   * Creating a real leave request will involve a lot more complicate steps, like
   * calculating the amount of days to be deducted based on a work pattern, but
   * we don't need all that for the tests. Only having the data on the right
   * tables is enough, and that's what this method does.
   *
   * @param int $typeID
   *    The ID of the Absence Type this Leave Request will belong to
   * @param int $contactID
   *    The ID of the Contact (user) this Leave Request will belong to
   * @param int $status
   *    One of the values of the Leave Request Status option list
   * @param string $fromDate
   *    The start date of the leave request
   * @param null|string $toDate
   *    The end date of the leave request. If null, it means it starts and ends at the same date
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest
   */
  public function createLeaveRequestBalanceChange($typeID, $contactID, $status, $fromDate, $toDate = null) {
    $fromDate = new DateTime($fromDate);

    if (!$toDate) {
      $toDate = clone $fromDate;
    }
    else {
      $toDate = new DateTime($toDate);
    }

    return LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $typeID,
      'contact_id' => $contactID,
      'status_id' => $status,
      'from_date' => $fromDate->format('Ymd'),
      'to_date' => $toDate->format('Ymd')
    ], true);
  }

  public function getLastIdInTable($tableName) {
    $dao = CRM_Core_DAO::executeQuery("SELECT id FROM {$tableName} ORDER BY id DESC LIMIT 1");
    $dao->fetch();
    return (int)$dao->id;
  }

  /**
   * Finds the main LeaveBalanceChange associated with the Leave Request of
   * request_type TOIL. The main balance change is the one linked to the first
   * dates of the LeaveRequest and where we store the accrued amount and the
   * expiry date.
   *
   * @param int $toilRequestID
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange|null
   */
  public function findToilRequestMainBalanceChange($toilRequestID) {
    $balanceChangeTable = LeaveBalanceChange::getTableName();
    $leaveRequestDateTable = LeaveRequestDate::getTableName();

    $query = "
      SELECT balance_change.* 
      FROM {$balanceChangeTable} balance_change
      INNER JOIN {$leaveRequestDateTable} as leave_request_date 
        ON leave_request_date.id = balance_change.source_id AND balance_change.source_type = %1
      WHERE leave_request_date.leave_request_id = %2 AND
            balance_change.expired_balance_change_id IS NULL 
      ORDER BY leave_request_date.date ASC 
      LIMIT 1
    ";

    $params = [
      1 => [LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY, 'String'],
      2 => [$toilRequestID, 'Integer']
    ];

    $result = CRM_Core_DAO::executeQuery($query, $params, true, LeaveBalanceChange::class);

    if($result->N === 1) {
      $result->fetch();

      return $result;
    }

    return null;
  }

  public function createLeaveBalanceChangeServiceMock() {
    $leaveBalanceChangeService = $this->getMockBuilder(CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange::class)
      ->setMethods(['recalculateExpiredBalanceChangesForLeaveRequestPastDates', 'createForLeaveRequest'])
      ->getMock();

    $leaveBalanceChangeService->expects($this->once())
      ->method('recalculateExpiredBalanceChangesForLeaveRequestPastDates')
      ->with($this->isInstanceOf(CRM_HRLeaveAndAbsences_BAO_LeaveRequest::class));

    $leaveBalanceChangeService->expects($this->any())
      ->method('createForLeaveRequest');

    return $leaveBalanceChangeService;
  }

  public function createLeaveBalanceChangeServiceForPublicHolidayLeaveRequestMock() {
    $leaveBalanceChangeService = $this->getMockBuilder(CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange::class)
      ->setMethods(['calculateAmountToBeDeductedForDate'])
      ->getMock();

    $leaveBalanceChangeService->expects($this->any())
      ->method('calculateAmountToBeDeductedForDate')
      ->will($this->returnValue(-1));

    return $leaveBalanceChangeService;
  }

  /**
   * Fabricates a Public Holiday leave request with the balance change amount as -1
   * Without this method, most of the tests will need to have a work pattern assigned to
   * the contact or have a default work pattern and will need to be mindful of which day is a
   * working day or not for the public holiday balance change to be successfully created.
   *
   * @param int $contactID
   * @param \CRM_HRLeaveAndAbsences_BAO_PublicHoliday $publicHoliday
   */
  public function fabricatePublicHolidayLeaveRequestWithMockBalanceChange($contactID, PublicHoliday $publicHoliday) {
    $balanceChangeServiceMock = $this->createLeaveBalanceChangeServiceForPublicHolidayLeaveRequestMock();
    PublicHolidayLeaveRequestFabricator::fabricate($contactID, $publicHoliday, $balanceChangeServiceMock);
  }

  public function createLeaveDateAmountDeductionServiceMock($amount) {
    $dateAmountDeductionService = $this->getMockBuilder(CRM_HRLeaveAndAbsences_Service_LeaveDateAmountDeduction::class)
      ->setMethods(['calculate'])
      ->getMock();

    $dateAmountDeductionService->expects($this->any())
      ->method('calculate')
      ->will($this->returnValue($amount * -1));

    return $dateAmountDeductionService;
  }

  public function createContractWorkPatternServiceMock($returnValue) {
    $contractWorkPatternService = $this->getMockBuilder(CRM_HRLeaveAndAbsences_Service_ContactWorkPattern::class)
      ->setMethods(['getContactWorkDayForDate'])
      ->getMock();

    $contractWorkPatternService->expects($this->once())
      ->method('getContactWorkDayForDate')
      ->will($this->returnValue($returnValue));

    return $contractWorkPatternService;
  }
}
