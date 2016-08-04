<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

trait CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait {

  private $balanceChangeTypes = [];

  private function getBalanceChangeTypeValue($type) {
    if(empty($this->balanceChangeTypes)) {
      $this->balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id'));
    }

    return $this->balanceChangeTypes[$type];
  }

  public function createSourcelessBalanceChange($entitlementID, $amount, $type, $expiryDate = null) {
    $params = [
      'type_id' => $type,
      'entitlement_id' => $entitlementID,
      'amount' => $amount
    ];

    if($expiryDate) {
      $params['expiry_date'] = $expiryDate;
    }

    return LeaveBalanceChange::create($params);
  }

  public function createLeaveBalanceChange($entitlementID, $amount) {
    return $this->createSourcelessBalanceChange(
      $entitlementID,
      $amount,
      $this->getBalanceChangeTypeValue('Leave')
    );
  }

  public function createBroughtForwardBalanceChange($entitlementID, $amount, $expiryDate = null) {
    return $this->createSourcelessBalanceChange(
      $entitlementID,
      $amount,
      $this->getBalanceChangeTypeValue('Brought Forward'),
      $expiryDate
    );
  }

  public function createPublicHolidayBalanceChange($entitlementID, $amount) {
    return $this->createSourcelessBalanceChange(
      $entitlementID,
      $amount,
      $this->getBalanceChangeTypeValue('Public Holiday')
    );
  }

  public function createExpiredBroughtForwardBalanceChange($entitlementID, $amount, $expiredAmount) {
    $this->createSourcelessBalanceChange(
      $entitlementID,
      $amount,
      $this->getBalanceChangeTypeValue('Brought Forward')
    );

    $broughtForwardBalanceChangeID = $this->getLastIdInTable(LeaveBalanceChange::getTableName());

    LeaveBalanceChange::create([
      'type_id' => $this->getBalanceChangeTypeValue('Brought Forward'),
      'entitlement_id' => $entitlementID,
      'amount' => $expiredAmount * -1, //expired amounts should be negative
      'expired_balance_change_id' => $broughtForwardBalanceChangeID
    ]);
  }

  /**
   * This is a helper method to create the fixtures a Leave Request and its
   * respective dates and balance changes.
   *
   * Creating a real leave request will involve a lot more complicate steps, like
   * calculating the amount of days to be deducted based on a work pattern, but
   * we don't need all that for the tests. Only having the data on the right
   * tables is enough, and that's what this method does.
   *
   * @param int $entitlementID
   *    The ID of the entitlement to which the leave request will be added to
   * @param int $status
   *    One of the values of the Leave Request Status option list
   * @param $fromDate
   *    The start date of the leave request
   * @param null $toDate
   *    The end date of the leave request. If null, it means it starts and ends at the same date
   */
  public function createLeaveRequestBalanceChange($entitlementID, $status, $fromDate, $toDate = null) {
    $leaveRequestTable = LeaveRequest::getTableName();
    $startDate = new DateTime($fromDate);

    if (!$toDate) {
      $endDate = new DateTime($fromDate);
    }
    else {
      $endDate = new DateTime($toDate);
    }

    $fromDate = "'{$fromDate}'";
    $toDate = $toDate ? "'{$toDate}'" : 'NULL';

    $query = "
      INSERT INTO {$leaveRequestTable}(entitlement_id, status_id, from_date, to_date)
      VALUES({$entitlementID}, {$status}, {$fromDate}, {$toDate})
    ";

    CRM_Core_DAO::executeQuery($query);
    $leaveRequestID = $this->getLastIdInTable($leaveRequestTable);

    // We need to add 1 day to the end date to include it
    // when we loop through the DatePeriod
    $endDate->modify('+1 day');
    $interval   = new DateInterval('P1D');
    $datePeriod = new DatePeriod($startDate, $interval, $endDate);

    $leaveRequestDateTableName = LeaveRequestDate::getTableName();
    $balanceChangeTableName = LeaveBalanceChange::getTableName();

    $debitBalanceChangeType = $this->getBalanceChangeTypeValue('Debit');

    foreach ($datePeriod as $date) {
      $dbDate = $date->format('Y-m-d');

      CRM_Core_DAO::executeQuery("
        INSERT INTO {$leaveRequestDateTableName}(date, leave_request_id)
        VALUES('{$dbDate}', {$leaveRequestID})
      ");

      $dateId = $this->getLastIdInTable($leaveRequestDateTableName);

      CRM_Core_DAO::executeQuery("
        INSERT INTO {$balanceChangeTableName}(entitlement_id, type_id, amount, source_id)
        VALUES('{$entitlementID}', {$debitBalanceChangeType}, -1, {$dateId})
      ");
    }

  }

  public function getLastIdInTable($tableName) {
    $dao = CRM_Core_DAO::executeQuery("SELECT id FROM {$tableName} ORDER BY id DESC LIMIT 1");
    $dao->fetch();
    return (int)$dao->id;
  }
}
