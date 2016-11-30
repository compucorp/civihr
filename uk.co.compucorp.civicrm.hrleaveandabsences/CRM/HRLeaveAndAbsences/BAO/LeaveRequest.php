<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

class CRM_HRLeaveAndAbsences_BAO_LeaveRequest extends CRM_HRLeaveAndAbsences_DAO_LeaveRequest {

  /**
   * Create a new LeaveRequest based on array-data
   *
   * @param array $params key-value pairs
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest|NULL
   *
   * @throws \Exception
   */
  public static function create($params) {
    $entityName = 'LeaveRequest';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);

    $transaction = new CRM_Core_Transaction();
    try {
      $instance->save();
      $instance->saveDates();
      $transaction->commit();
    } catch(Exception $e) {
      $transaction->rollback();
      // We throw the catched Exception so forms can handle the
      // error and properly inform the user about what happened
      throw $e;
    }

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Returns a LeaveRequest instance representing the Public Holiday Leave Request
   * for the given $publicHoliday and assigned to the Contact with the given
   * $contactID
   *
   * @param int $contactID
   * @param \CRM_HRLeaveAndAbsences_BAO_PublicHoliday $publicHoliday
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest|null
   */
  public static function findPublicHolidayLeaveRequest($contactID, PublicHoliday $publicHoliday) {
    $leaveRequestTable = self::getTableName();
    $leaveRequestDateTable = LeaveRequestDate::getTableName();
    $leaveBalanceChangeTable = LeaveBalanceChange::getTableName();

    $query = "
      SELECT lr.* FROM {$leaveRequestTable} lr
      INNER JOIN {$leaveRequestDateTable} lrd 
        ON lrd.leave_request_id = lr.id
      INNER JOIN {$leaveBalanceChangeTable} lbc
        ON lbc.source_id = lrd.id AND lbc.source_type = %1
      WHERE lr.contact_id = %2 AND 
            lrd.date = %3 AND
            lbc.type_id = %4
    ";

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id'));

    $params = [
      1 => [LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY, 'String'],
      2 => [$contactID, 'Integer'],
      3 => [CRM_Utils_Date::processDate($publicHoliday->date, null, false, 'Y-m-d'), 'String'],
      4 => [$balanceChangeTypes['Public Holiday'], 'Integer']
    ];

    $leaveRequest = CRM_Core_DAO::executeQuery($query, $params, true, self::class);

    if($leaveRequest->N == 1) {
      $leaveRequest->fetch();

      return $leaveRequest;
    }

    return null;
  }

  /**
   * Returns a list of all Absence Types, together with its total balance change.
   * That is, the sum of all the Leave Balance Changes for Leave Requests of that
   * Absence Type, for the given $contactID during the given $periodID.
   *
   * Balance Changes for Public Holiday Leave Requests won't be considered,
   * except when $publicHolidays is true. In that case, on the balance changes
   * for that type of request will be considered.
   *
   * @param int $contactID
   * @param int $periodID
   * @param array $leaveRequestStatus
   *   And array of values from the Leave Request Status OptionGroup
   * @param bool $publicHolidaysOnly
   *   When true, will get the balance change only for the Public Holiday Leave Requests
   *
   * @return array
   */
  public static function getBalanceChangeByAbsenceType(
    $contactID,
    $periodID,
    $leaveRequestStatus = [],
    $publicHolidaysOnly = false
  ) {
    $periodEntitlements = LeavePeriodEntitlement::getPeriodEntitlementsForContact($contactID, $periodID);

    $results = [];
    $excludePublicHolidays = !$publicHolidaysOnly;
    foreach($periodEntitlements as $periodEntitlement) {
      $balance = LeaveBalanceChange::getLeaveRequestBalanceForEntitlement(
        $periodEntitlement,
        $leaveRequestStatus,
        null,
        null,
        $excludePublicHolidays,
        $publicHolidaysOnly
      );
      $results[$periodEntitlement->type_id] = $balance;
    }

    return $results;
  }

  /**
   * Returns all the LeaveRequestDate instances related to this LeaveRequest.
   *
   * @return CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate[]
   *  The dates in ascending order according to the date
   */
  public function getDates() {
    return LeaveRequestDate::getDatesForLeaveRequest($this->id);
  }

  /**
   * Creates and saves LeaveRequestDates for this LeaveRequest
   */
  private function saveDates() {
    $this->deleteDates();

    $startDate = new DateTime($this->from_date);

    if (!$this->to_date) {
      $endDate = new DateTime($this->from_date);
    }
    else {
      $endDate = new DateTime($this->to_date);
    }

    // We need to add 1 day to the end date to include it
    // when we loop through the DatePeriod
    $endDate->modify('+1 day');

    $interval   = new DateInterval('P1D');
    $datePeriod = new DatePeriod($startDate, $interval, $endDate);

    foreach ($datePeriod as $date) {
      LeaveRequestDate::create([
        'date' => $date->format('YmdHis'),
        'leave_request_id' => $this->id
      ]);
    }
  }

  /**
   * Deletes all the dates related to this LeaveRequest
   */
  private function deleteDates() {
    LeaveRequestDate::deleteDatesForLeaveRequest($this->id);
  }

  /**
   * Calculates the overall balance change that a Leave Request will create given a
   * start and end date and also returns the breakdown by days
   *
   * @param $params
   *   - contact_id
   *   - from_date: The starting date of the leave period
   *   - from_type: The from type e.g 1/2 AM, 1/2 PM
   *   - to_date: The ending day of the leave period
   *   - to_type: Same description as from_type
   *
   * @return array
   *   An array of formatted results
   */
  public static function calculateBalanceChange($contactId, $fromDate, $fromType, $toDate, $toType) {

    $leaveRequest = new self();
    $leaveRequest->contact_id = $contactId;

    $startDate = new DateTime($fromDate);
    $endDate = new DateTime($toDate);
    $endDateUnmodified = new DateTime($toDate);
    // add one day to end date to include it in DatePeriod
    $endDate->modify('+1 day');
    $interval   = new DateInterval('P1D');
    $datePeriod = new DatePeriod($startDate, $interval, $endDate);


    $fromDateIsHalfDay = in_array($fromType, ['1/2 AM', '1/2 PM']);
    $toDateIsHalfDay = in_array($toType, ['1/2 AM', '1/2 PM']);
    $resultsBreakdown = [
      'amount' => 0,
      'breakdown' => []
    ];
    foreach ($datePeriod as $date) {
        $amount = LeaveBalanceChange::calculateAmountForDate($leaveRequest, $date);
        $type = LeaveBalanceChange::getWorkDayTypeForDate($leaveRequest, $date);

      //since its an half day, 0.5 will be deducted irrespective of the amount returned from the work pattern
      if($fromDateIsHalfDay && $date->format('Y-m-d') == $startDate->format('Y-m-d') && $amount != 0) {
        $amount = -1 * 0.5;
        $type =  $fromType;
      }

      //since its an half day, 0.5 will be deducted irrespective of the amount returned from the work pattern
      if($toDateIsHalfDay && $date->format('Y-m-d') == $endDateUnmodified->format('Y-m-d') && $amount !=0) {
        $amount =  -1 * 0.5;
        $type = $toType;
      }
      //replacing the sting values to that which is expected by frontend
      $type = str_replace(['Yes', 'No'], ['All day', 'Non working day'], $type);

      $result = [
        'date' => $date->format('Y-m-d'),
        'amount' => abs($amount),
        'type' => $type
      ];
      $resultsBreakdown['amount'] += $amount;
      $resultsBreakdown['breakdown'][] = $result;
    }
    return $resultsBreakdown;
  }
}
