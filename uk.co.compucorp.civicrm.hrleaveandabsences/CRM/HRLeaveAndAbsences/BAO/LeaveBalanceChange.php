<?php

use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern as ContactWorkPattern;
use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;

class CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange extends CRM_HRLeaveAndAbsences_DAO_LeaveBalanceChange {

  const SOURCE_ENTITLEMENT = 'entitlement';
  const SOURCE_LEAVE_REQUEST_DAY = 'leave_request_day';
  const SOURCE_TOIL_REQUEST = 'toil_request';

  /**
   * Create a new LeaveBalanceChange based on array-data
   *
   * @param array $params key-value pairs
   *
   * @return CRM_HRLeaveAndAbsences_DAO_LeaveBalanceChange|NULL
   */
  public static function create($params) {
    $entityName = 'LeaveBalanceChange';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Creates LeaveBalanceChanges for each of the LeaveRequestDates of the given
   * LeaveRequest.
   *
   * The amount for each balance change will be calculated accordingly to the
   * WorkPattern(s) of the contact of the LeaveRequest.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   */
  public static function createForLeaveRequest(LeaveRequest $leaveRequest) {
    $balanceChangeTypes = array_flip(self::buildOptions('type_id'));

    foreach($leaveRequest->getDates() as $date) {
      self::create([
        'source_id'   => $date->id,
        'source_type' => self::SOURCE_LEAVE_REQUEST_DAY,
        'type_id'     => $balanceChangeTypes['Leave'],
        'amount'      => self::calculateAmountForDate(
          $leaveRequest,
          new \DateTime($date->date)
        )
      ]);
    }
  }

  /**
   * Returns the sum of all balance changes between the given LeavePeriodEntitlement
   * dates.
   *
   * This method can also sum only balance changes caused by leave requests with
   * specific statuses. For this, one can pass an array of statuses as the
   * $leaveRequestStatus parameter.
   *
   * Note: the balance changes linked to the given LeavePeriodEntitlement, that
   * is source_id == entitlement->id and source_type == 'entitlement', will also
   * be included in the sum.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement $periodEntitlement
   *    The LeavePeriodEntitlement to get the Balance to
   * @param array $leaveRequestStatus
   *    An array of values from Leave Request Status option list
   *
   * @return float
   */
  public static function getBalanceForEntitlement(LeavePeriodEntitlement $periodEntitlement, $leaveRequestStatus = []) {
    $balanceChangeTable = self::getTableName();
    $leaveRequestDateTable = LeaveRequestDate::getTableName();
    $leaveRequestTable = LeaveRequest::getTableName();

    $whereLeaveRequestDates = self::buildLeaveRequestDateWhereClause($periodEntitlement);

    $whereLeaveRequestStatus = '';
    if(is_array($leaveRequestStatus) && !empty($leaveRequestStatus)) {
      array_walk($leaveRequestStatus, 'intval');
      $whereLeaveRequestStatus = ' AND leave_request.status_id IN('. implode(', ', $leaveRequestStatus) .')';
    }

    $query = "
      SELECT SUM(leave_balance_change.amount) balance
      FROM {$balanceChangeTable} leave_balance_change
      LEFT JOIN {$leaveRequestDateTable} leave_request_date 
             ON leave_balance_change.source_id = leave_request_date.id AND 
                leave_balance_change.source_type = '". self::SOURCE_LEAVE_REQUEST_DAY ."'
      LEFT JOIN {$leaveRequestTable} leave_request ON leave_request_date.leave_request_id = leave_request.id
      WHERE (
              $whereLeaveRequestDates AND
              leave_request.type_id = {$periodEntitlement->type_id} AND
              leave_request.contact_id = {$periodEntitlement->contact_id} 
              $whereLeaveRequestStatus
            )
            OR
            (
              leave_balance_change.source_id = {$periodEntitlement->id} AND 
              leave_balance_change.source_type = '" . self::SOURCE_ENTITLEMENT . "'
            )
    ";

    $result = CRM_Core_DAO::executeQuery($query);
    $result->fetch();

    return (float)$result->balance;
  }

  /**
   * Returns the LeaveBalanceChange instances that are part of the
   * LeavePeriodEntitlement with the given ID.
   *
   * The Breakdown is made of the balance changes representing the parts that,
   * together, make the period entitlement. They are: The Leave, the Brought
   * Forward and the Public Holidays. These are all balance changes, where the
   * source_id is the LeavePeriodEntitlement's ID and source_type is equal to
   * "entitlement", since they're are created during the entitlement calculation.
   * Passing true for $returnExpiredOnly parameter will return only expired leave balance changes
   * while Passing false will return only Non expired leave balance changes for the entitlement ID
   *
   * @param int $entitlementID
   *   The ID of the LeavePeriodEntitlement to get the Breakdown to
   * @param boolean $returnExpiredOnly
   *   Whether to return Only Expired or Only Non Expired LeaveBalanceChanges
   *
   * @return CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange[]
   */
  public static function getBreakdownBalanceChangesForEntitlement($entitlementID, $returnExpiredOnly = false) {
    $entitlementID = (int)$entitlementID;
    $balanceChangeTable = self::getTableName();

    if(!$returnExpiredOnly){
      $expiredBalanceWhereCondition = " AND expired_balance_change_id IS NULL";
    }
    if($returnExpiredOnly){
      $expiredBalanceWhereCondition = " AND (expired_balance_change_id IS NOT NULL AND expiry_date < %1)";
    }

    $query = "
      SELECT *
      FROM {$balanceChangeTable}
      WHERE source_id = {$entitlementID} AND
            source_type = '" . self::SOURCE_ENTITLEMENT . "' {$expiredBalanceWhereCondition}
      ORDER BY id
    ";

    $changes = [];
    $params = [
      1 => [date('Y-m-d'), 'String']
    ];

    $result = CRM_Core_DAO::executeQuery($query, $params, true, self::class);
    while($result->fetch()) {
      $changes[] = clone $result;
    }

    return $changes;
  }

  /**
   * Returns the balance for the Balance Changes that are part of the
   * LeavePeriodEntitlement with the given ID.
   *
   * This basically gets the output of getBreakdownBalanceChangesForEntitlement()
   * and sums up the amount of the returned LeaveBalanceChange instances.
   *
   * @see CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange::getBreakdownBalanceChangesForEntitlement()
   *
   * @param int $entitlementID
   *    The ID of the LeavePeriodEntitlement to get the Breakdown Balance to
   *
   * @return float
   */
  public static function getBreakdownBalanceForEntitlement($entitlementID) {
    $balanceChanges = self::getBreakdownBalanceChangesForEntitlement($entitlementID);

    $balance = 0.0;
    foreach($balanceChanges as $balanceChange) {
      $balance += (float)$balanceChange->amount;
    }

    return $balance;
  }

  /**
   * Returns the sum of all balance changes generated by LeaveRequests on
   * LeavePeriodEntitlement with the given ID.
   *
   * This method can also sum only balance changes caused by leave requests with
   * specific statuses. For this, one can pass an array of statuses as the
   * $leaveRequestStatus parameter.
   *
   * It's also possible to get the balance only for leave requests taken between
   * a given date range. For this, one can use the $dateLimit and $dateStart params.
   *
   * Public Holidays may also be stored as Leave Requests. If you want to exclude
   * them from the sum, or only sum their balance changes, you can use the
   * $excludePublicHolidays or $includePublicHolidaysOnly params.
   *
   * Since balance changes caused by LeaveRequests are negative, this method
   * will return a negative number.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement $periodEntitlement
   * @param array $leaveRequestStatus
   *   An array of values from Leave Request Status option list
   * @param \DateTime $dateLimit
   *   When given, will make the method count only days taken as leave up to this date
   * @param \DateTime $dateStart
   *   When given, will make the method count only days taken as leave starting from this date
   * @param bool $excludePublicHolidays
   *   When true, it won't sum the balance changes for Public Holiday Leave Requests
   * @param bool $includePublicHolidaysOnly
   *   When true, it won't sum only the balance changes for Public Holiday Leave Requests
   *
   * @return float
   */
  public static function getLeaveRequestBalanceForEntitlement(
    LeavePeriodEntitlement $periodEntitlement,
    $leaveRequestStatus = [],
    DateTime $dateLimit = NULL,
    DateTime $dateStart = NULL,
    $excludePublicHolidays = false,
    $includePublicHolidaysOnly = false
  ) {

    $balanceChangeTable = self::getTableName();
    $leaveRequestDateTable = LeaveRequestDate::getTableName();
    $leaveRequestTable = LeaveRequest::getTableName();

    $whereLeaveRequestDates = self::buildLeaveRequestDateWhereClause($periodEntitlement);

    $query = "
      SELECT SUM(leave_balance_change.amount) balance
      FROM {$balanceChangeTable} leave_balance_change
      INNER JOIN {$leaveRequestDateTable} leave_request_date 
              ON leave_balance_change.source_id = leave_request_date.id AND 
                 leave_balance_change.source_type = '" . self::SOURCE_LEAVE_REQUEST_DAY . "'
      INNER JOIN {$leaveRequestTable} leave_request ON leave_request_date.leave_request_id = leave_request.id
      WHERE {$whereLeaveRequestDates} AND
            leave_request.type_id = {$periodEntitlement->type_id} AND
            leave_request.contact_id = {$periodEntitlement->contact_id} 
    ";

    if(is_array($leaveRequestStatus) && !empty($leaveRequestStatus)) {
      array_walk($leaveRequestStatus, 'intval');
      $query .= ' AND leave_request.status_id IN('. implode(', ', $leaveRequestStatus) .') ';
    }

    if($dateLimit) {
      $query .= " AND leave_request_date.date <= '{$dateLimit->format('Y-m-d')}' ";
    }

    if($dateStart) {
      $query .= " AND leave_request_date.date >= '{$dateStart->format('Y-m-d')}' ";
    }

    $balanceChangeTypes = array_flip(self::buildOptions('type_id'));
    if($excludePublicHolidays) {
      $query .= " AND leave_balance_change.type_id != '{$balanceChangeTypes['Public Holiday']}'";
    }

    if($includePublicHolidaysOnly) {
      $query .= " AND leave_balance_change.type_id = '{$balanceChangeTypes['Public Holiday']}'";
    }

    $result = CRM_Core_DAO::executeQuery($query);
    $result->fetch();

    return (float)$result->balance;
  }

  /**
   * Returns all the LeaveBalanceChanges linked to the LeaveRequestDates of the
   * given LeaveRequest
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange[]
   */
  public static function getBreakdownForLeaveRequest(LeaveRequest $leaveRequest) {
    $balanceChangeTable = self::getTableName();
    $leaveRequestDateTable = LeaveRequestDate::getTableName();
    $leaveRequestTable = LeaveRequest::getTableName();

    $query = "
      SELECT bc.*
      FROM {$balanceChangeTable} bc
      INNER JOIN {$leaveRequestDateTable} lrd 
        ON bc.source_id = lrd.id AND bc.source_type = %1
      INNER JOIN {$leaveRequestTable} lr
        ON lrd.leave_request_id = lr.id
      WHERE lr.id = %2
      ORDER BY id
    ";

    $params = [
      1 => [self::SOURCE_LEAVE_REQUEST_DAY, 'String'],
      2 => [$leaveRequest->id, 'Integer'],
    ];

    $changes = [];

    $result = CRM_Core_DAO::executeQuery($query, $params, true, self::class);
    while($result->fetch()) {
      $changes[] = clone $result;
    }

    return $changes;
  }

  /**
   * Returns the sum of all LeaveBalanceChanges linked to the LeaveRequestDates
   * of the given LeaveRequest.
   *
   * This basically gets the output of getBreakdownForLeaveRequest()
   * and sums up the amount of the returned LeaveBalanceChange instances.
   *
   * @see \CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange::getBreakdownForLeaveRequest()
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return float
   */
  public static function getTotalBalanceChangeForLeaveRequest(LeaveRequest $leaveRequest) {
    $balanceChanges = self::getBreakdownForLeaveRequest($leaveRequest);

    $balance = 0.0;
    foreach($balanceChanges as $balanceChange) {
      $balance += (float)$balanceChange->amount;
    }

    return $balance;
  }

  /**
   * This method checks every leave balance change record with an expiry_date in
   * the past and that still don't have a record for the expired days (that is,
   * a balance change record of this same type and with an expired_balance_change_id
   * pointing to the expired record), and creates it.
   *
   * @return int The number of records created
   */
  public static function createExpirationRecords() {
    $numberOfRecordsCreated = 0;

    $tableName = self::getTableName();
    $query = "
      SELECT balance_to_expire.*
      FROM {$tableName} balance_to_expire
      LEFT JOIN {$tableName} expired_balance_change
             ON balance_to_expire.id = expired_balance_change.expired_balance_change_id
      WHERE balance_to_expire.expiry_date IS NOT NULL AND
            balance_to_expire.expiry_date < CURDATE() AND
            balance_to_expire.expired_balance_change_id IS NULL AND
            expired_balance_change.id IS NULL
      ORDER BY balance_to_expire.source_id ASC, balance_to_expire.expiry_date ASC
    ";

    $startDate = null;
    $sourceID = null;
    $sourceType = null;

    $dao = CRM_Core_DAO::executeQuery($query, [], true, self::class);
    while($dao->fetch()) {
      if($dao->source_id != $sourceID && $dao->source_type != $sourceType) {
        $startDate = null;
        $sourceID = $dao->source_id;
        $sourceType = $dao->source_type;
      }

      $expiredAmount = self::calculateExpiredAmount($dao, $startDate);
      // Since these days should be deducted from the entitlement,
      // We need to store the expired amount as a negative number
      $expiredAmount *= -1;

      self::create([
        'source_id' => $dao->source_id,
        'source_type' => $dao->source_type,
        'type_id' => $dao->type_id,
        'amount' => $expiredAmount,
        'expiration_date' => date('YmdHis', strtotime($dao->expiry_date)),
        'expired_balance_change_id' => $dao->id
      ]);

      $numberOfRecordsCreated++;
      $startDate = (new DateTime($dao->expiry_date))->modify('+1 day');
    }
    return $numberOfRecordsCreated;
  }

  /**
   * This method calculates how many days of the given LeaveBalanceChange have
   * expired.
   *
   * To do this, we get the leave request balance (in other words, the number of
   * days taken as leave) up to the balance change expiry date and subtracts it
   * from the balance change amount.
   *
   * This method also supports a $startDate param. When given, the method will
   * calculate the expired amount counting only days taken as leave between the
   * $startDate and the balance change expiry date.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange $balanceChange
   * @param \DateTime|NULL $startDate
   *
   * @return float
   */
  private static function calculateExpiredAmount(
    CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange $balanceChange,
    DateTime $startDate = NULL
  ) {
    $leaveRequestStatus = array_flip(LeaveRequest::buildOptions('status_id'));
    $approvedStatuses = [
      $leaveRequestStatus['Approved'],
      $leaveRequestStatus['Admin Approved']
    ];

    $balanceChange->amount = (float)$balanceChange->amount;

    $entitlement = LeavePeriodEntitlement::findById($balanceChange->source_id);
    $expiredAmount = self::getLeaveRequestBalanceForEntitlement(
      $entitlement,
      $approvedStatuses,
      new DateTime($balanceChange->expiry_date),
      $startDate
    );

    // Since the the Leave Request Balance is negative, when we add it
    // to the amount we're actually subtracting the value
    return $balanceChange->amount + $expiredAmount;
  }

  /**
   * Creates the where clause to filter leave requests by the LeavePeriodEntitlement
   * dates.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement $periodEntitlement
   *
   * @return string
   */
  private static function buildLeaveRequestDateWhereClause(
    LeavePeriodEntitlement $periodEntitlement
  ) {
    $contractsDates = $periodEntitlement->getStartAndEndDates();

    $leaveRequestDatesClauses = [];
    foreach ($contractsDates as $dates) {
      $leaveRequestDatesClauses[] = "leave_request_date.date BETWEEN '{$dates['start_date']}' AND '{$dates['end_date']}'";
    }
    $whereLeaveRequestDates = implode(' OR ', $leaveRequestDatesClauses);

    // This is just a trick to make it easier to
    // interpolate this clause in SQL query string.
    // if theres no date, we return the clause as a catch all condition
    if(empty($whereLeaveRequestDates)) {
      $whereLeaveRequestDates = '1=1';
    }

    // Finally, since this is a list of conditions separate
    // by OR, we wrap it in parenthesis
    return "($whereLeaveRequestDates)";
  }

  /**
   * Calculates the amount to be deducted for a leave taken by the given contact
   * on the given date.
   *
   * This works by fetching the contact's work pattern active during the given
   * date and then using it to get the amount of days to be deducted. If there's
   * no work pattern assigned to the contact, the default work pattern will be
   * used instead.
   *
   * This method also considers the existence of Public Holidays Leave Requests
   * overlapping the dates of the LeaveRequest. For those dates, the amount of
   * days to be deducted will be 0.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *  The LeaveRequest which the $date belongs to
   * @param \DateTime $date
   *
   * @return float
   */
  public static function calculateAmountForDate(LeaveRequest $leaveRequest, DateTime $date) {
    if(self::thereIsAPublicHolidayLeaveRequest($leaveRequest, $date)) {
      return 0.0;
    }

    $workPattern = ContactWorkPattern::getWorkPattern($leaveRequest->contact_id, $date);
    $startDate = ContactWorkPattern::getStartDate($leaveRequest->contact_id, $date);

    if(!$workPattern || !$startDate) {
      return 0.0;
    }

    return $workPattern->getLeaveDaysForDate($date, $startDate) * -1;
  }

  /**
   * Returns if there is a Public Holiday Leave Request for the given
   * $date and with the same contact_id and type_id as the given $leaveRequest
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   * @param \DateTime $date
   *
   * @return bool
   */
  private static function thereIsAPublicHolidayLeaveRequest(LeaveRequest $leaveRequest, DateTime $date) {
    $balanceChange = self::getExistingBalanceChangeForALeaveRequestDate($leaveRequest, $date);

    if(is_null($balanceChange)) {
      return false;
    }

    $balanceChangeTypes = array_flip(self::buildOptions('type_id'));

    return $balanceChange->type_id == $balanceChangeTypes['Public Holiday'];
  }

  /**
   * Returns an existing LeaveBalanceChange record linked to a LeaveRequestDate
   * with the same date as $date and belonging to a LeaveRequest with the same
   * contact_id and type_id as those of the given $leaveRequest.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   * @param \DateTime $date
   *
   * @return \CRM_Core_DAO|null|object
   */
  public static function getExistingBalanceChangeForALeaveRequestDate(LeaveRequest $leaveRequest, DateTime $date) {
    $balanceChangeTable = self::getTableName();
    $leaveRequestDateTable = LeaveRequestDate::getTableName();
    $leaveRequestTable = LeaveRequest::getTableName();

    $query = "
      SELECT bc.*
      FROM {$balanceChangeTable} bc
      INNER JOIN {$leaveRequestDateTable} lrd
        ON bc.source_id = lrd.id AND bc.source_type = %1
      INNER JOIN {$leaveRequestTable} lr
        ON lrd.leave_request_id = lr.id
      WHERE lrd.date = %2 AND
            lr.contact_id = %3
      ORDER BY id
    ";

    $params = [
      1 => [self::SOURCE_LEAVE_REQUEST_DAY, 'String'],
      2 => [$date->format('Y-m-d'), 'String'],
      3 => [$leaveRequest->contact_id, 'Integer'],
    ];

    $result = CRM_Core_DAO::executeQuery($query, $params, true, self::class);

    if($result->N == 1) {
      $result->fetch();
      return $result;
    }

    return null;
  }

  /**
   * Deletes the LeaveBalanceChange linked to the given LeaveRequestDate
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate $date
   */
  public static function deleteForLeaveRequestDate(LeaveRequestDate $date) {
    $leaveBalanceChangeTable = self::getTableName();
    $query = "DELETE FROM {$leaveBalanceChangeTable} WHERE source_id = %1 AND source_type = %2";

    $params = [
      1 => [$date->id, 'Integer'],
      2 => [self::SOURCE_LEAVE_REQUEST_DAY, 'String']
    ];

    CRM_Core_DAO::executeQuery($query, $params);
  }

  /**
   * Deletes the LeaveBalanceChanges linked to all of the LeaveRequestDates of
   * the given LeaveRequest
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   */
  public static function deleteAllForLeaveRequest(LeaveRequest $leaveRequest) {
    $leaveBalanceChangeTable = self::getTableName();
    $leaveRequestDateTable = LeaveRequestDate::getTableName();

    $query = "DELETE bc FROM {$leaveBalanceChangeTable} bc
              INNER JOIN {$leaveRequestDateTable} lrd
                ON bc.source_id = lrd.id AND bc.source_type = %1
              WHERE lrd.leave_request_id = %2";

    $params = [
      1 => [self::SOURCE_LEAVE_REQUEST_DAY, 'String'],
      2 => [$leaveRequest->id, 'Integer']
    ];

    CRM_Core_DAO::executeQuery($query, $params);
  }
}
