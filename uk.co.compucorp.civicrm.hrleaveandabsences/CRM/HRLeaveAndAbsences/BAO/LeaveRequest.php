<?php

use CRM_HRCore_Date_BasicDatePeriod as BasicDatePeriod;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern as ContactWorkPattern;
use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException as InvalidLeaveRequestException;

class CRM_HRLeaveAndAbsences_BAO_LeaveRequest extends CRM_HRLeaveAndAbsences_DAO_LeaveRequest {

  use CRM_HRLeaveAndAbsences_ACL_LeaveInformationTrait;

  const REQUEST_TYPE_LEAVE = 'leave';
  const REQUEST_TYPE_SICKNESS = 'sickness';
  const REQUEST_TYPE_TOIL = 'toil';
  const REQUEST_TYPE_PUBLIC_HOLIDAY = 'public_holiday';

  /**
   * Create a new LeaveRequest based on array-data
   *
   * @param array $params key-value pairs
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest|NULL
   *
   * @throws \Exception
   */
  public static function create($params, $validate = true) {
    $entityName = 'LeaveRequest';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);

    if($validate){
      self::validateParams($params);
    }
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
   * A method for validating the params passed to the Leave Request create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  public static function validateParams($params) {
    self::validateMandatory($params);
    self::validateRequestType($params);
    self::validateTOILFieldsBasedOnRequestType($params);
    self::validateSicknessFieldsBasedOnRequestType($params);
    self::validateAbsenceTypeIsActiveAndValid($params);
    self::validateTOILRequest($params);
    self::validateStartDateNotGreaterThanEndDate($params);
    self::validateLeaveDaysAgainstAbsenceTypeMaxConsecutiveLeaveDays($params);
    self::validateAbsenceTypeAllowRequestCancellationForLeaveRequestCancellation($params);
    self::validateAbsencePeriod($params);
    self::validateNoOverlappingLeaveRequests($params);
    self::validateBalanceChange($params);
    self::validateWorkingDay($params);
    self::validateLeaveDatesDoesNotOverlapContracts($params);
  }

  /**
   * A method for validating the mandatory fields in the params
   * passed to the Leave Request create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateMandatory($params) {
    $mandatoryFields = [
      'from_date',
      'from_date_type',
      'contact_id',
      'type_id',
      'status_id',
      'to_date',
      'to_date_type',
      'request_type'
    ];

    foreach($mandatoryFields as $field) {
      if (empty($params[$field])) {
        throw new InvalidLeaveRequestException(
          "The {$field} field should not be empty",
          "leave_request_empty_{$field}",
          $field
        );
      }
    }


  }

  /**
   * Validates the values of the request_type field
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateRequestType($params) {
    $validRequestTypes = [
      self::REQUEST_TYPE_LEAVE,
      self::REQUEST_TYPE_SICKNESS,
      self::REQUEST_TYPE_TOIL,
      self::REQUEST_TYPE_PUBLIC_HOLIDAY
    ];

    if(!in_array($params['request_type'], $validRequestTypes)) {
      throw new InvalidLeaveRequestException(
        'The request_type is invalid',
        'leave_request_invalid_request_type',
        'request_type'
      );
    }
  }

  /**
   * Validates the TOIL mandatory fields according to the value of request_type.
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateTOILFieldsBasedOnRequestType($params) {
    $toilRequiredFields = [ 'toil_duration', 'toil_to_accrue' ];
    $toilFields = array_merge($toilRequiredFields, [ 'toil_expiry_date' ]);

    if(self::REQUEST_TYPE_TOIL == $params['request_type']) {
      foreach($toilRequiredFields as $requiredField) {
        if(empty($params[$requiredField])) {
          throw new InvalidLeaveRequestException(
            "The {$requiredField} can not be empty when request_type is toil",
            "leave_request_empty_{$requiredField}",
            $requiredField
          );
        }
      }
    } else {
      foreach($toilFields as $field) {
        if(!empty($params[$field])) {
          throw new InvalidLeaveRequestException(
            "The {$field} should be empty when request_type is not toil",
            "leave_request_non_empty_{$field}",
            $field
          );
        }
      }
    }
  }

  /**
   * Runs all the validations specific for TOIL Requests
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateTOILRequest($params) {
    if($params['request_type'] !== self::REQUEST_TYPE_TOIL) {
      return;
    }

    self::validateTOILToAccrueIsAValidOptionValue($params);
    self::validateTOILPastDays($params);
    self::validateTOILToAccruedAmountIsValid($params);
  }

  /**
   * Validates if the value passed to the TOIL To Accrued field is one of the
   * options available on the hrleaveandabsences_toil_amounts option group
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateTOILToAccrueIsAValidOptionValue($params) {
    $toilAmountOptions = self::buildOptions('toil_to_accrue', 'validate');
    if(!array_key_exists($params['toil_to_accrue'], $toilAmountOptions)) {
      throw new InvalidLeaveRequestException(
        'The TOIL to accrue amount is not valid',
        'leave_request_toil_to_accrue_is_invalid',
        'toil_to_accrue'
      );
    }
  }

  /**
   * Validate that the user cannot request TOIL for past days
   * if the absence type is not set up as such.
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateTOILPastDays($params) {
    $absenceType = AbsenceType::findById($params['type_id']);

    $fromDate = new DateTime($params['from_date']);
    $toDate = new DateTime($params['to_date']);
    $todayDate = new DateTime('today');
    $leaveDatesHasPastDates = $fromDate < $todayDate || $toDate < $todayDate;

    if ($leaveDatesHasPastDates && !$absenceType->allow_accrue_in_the_past) {
      throw new InvalidLeaveRequestException(
        'You cannot request TOIL for past days',
        'leave_request_toil_cannot_be_requested_for_past_days',
        'from_date'
      );
    }
  }

  /**
   * Validate that the TOIL amount to be accrued plus total approved accrued TOIL for the period
   * is not greater than the maximum defined(if any) for the Absence Type
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateTOILToAccruedAmountIsValid($params) {
    $absenceType = AbsenceType::findById($params['type_id']);
    $unlimitedAccrual = empty($absenceType->max_leave_accrual) && $absenceType->max_leave_accrual != 0;

    $periodContainingToilDates = AbsencePeriod::getPeriodContainingDates(
      new DateTime($params['from_date']),
      new DateTime($params['to_date'])
    );

    $totalApprovedToilForPeriod = self::getTotalApprovedToilForPeriod(
      $periodContainingToilDates,
      $params['contact_id'],
      $params['type_id']
    );
    $totalProjectedToilForPeriod = $totalApprovedToilForPeriod + $params['toil_to_accrue'];

    if ($totalProjectedToilForPeriod > $absenceType->max_leave_accrual && !$unlimitedAccrual) {
      throw new InvalidLeaveRequestException(
        'The TOIL amount plus all approved TOIL for current period is greater than the maximum for this Absence Type',
        'leave_request_toil_amount_more_than_maximum_for_absence_type',
        'toil_to_accrue'
      );
    }
  }


  /**
   * This method returns the total sum of TOIL accrued for an absence type by a contact
   * over a given absence period
   *
   * @param int $contactID
   * @param int $typeID
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $period
   *
   * @return float
   */
  private static function getTotalApprovedToilForPeriod(AbsencePeriod $period, $contactID, $typeID) {
    $leaveRequestStatuses = array_flip(self::buildOptions('status_id'));

    $leaveRequestStatusFilter = [
      $leaveRequestStatuses['Approved'],
      $leaveRequestStatuses['Admin Approved']
    ];

    $totalApprovedTOIL = LeaveBalanceChange::getTotalTOILBalanceChangeForContact(
      $contactID,
      $typeID,
      new DateTime($period->start_date),
      new DateTime($period->end_date),
      $leaveRequestStatusFilter
    );

    return $totalApprovedTOIL;
  }

  /**
   * Validates the Sickness mandatory fields according to the value of request_type.
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateSicknessFieldsBasedOnRequestType($params) {
    $sicknessRequiredFields = [ 'sickness_reason' ];
    $sicknessFields = array_merge($sicknessRequiredFields, ['sickness_required_documents']);

    if(self::REQUEST_TYPE_SICKNESS == $params['request_type']) {
      foreach($sicknessRequiredFields as $requiredField) {
        if(empty($params[$requiredField])) {
          throw new InvalidLeaveRequestException(
            "The {$requiredField} can not be empty when request_type is sickness",
            "leave_request_empty_{$requiredField}",
            $requiredField
          );
        }
      }
    } else {
      foreach($sicknessFields as $field) {
        if(!empty($params[$field])) {
          throw new InvalidLeaveRequestException(
            "The {$field} should be empty when request_type is not sickness",
            "leave_request_non_empty_{$field}",
            $field
          );
        }
      }
    }
  }

  /**
   * This method validates that a leave request has dates within a valid absence period
   * and also that a leave request dates does not overlap two or more absence periods
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateAbsencePeriod($params) {
    $toDate = new DateTime($params['to_date']);
    $fromDate = new DateTime($params['from_date']);
    $period = AbsencePeriod::getPeriodContainingDates($fromDate, $toDate);

    //this condition means that no absence period was found that contains both the start and end date
    //either there was an overlap or the absence period does not simply exist.
    if (!$period) {
      throw new InvalidLeaveRequestException(
        'The Leave request dates are not contained within a valid absence period',
        'leave_request_not_within_absence_period',
        'from_date'
      );
    }
  }

  /**
   * This method validates that the leave request does not have dates in more than one contract period
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateLeaveDatesDoesNotOverlapContracts($params) {
    $fromDate = new DateTime($params['from_date']);
    $toDate = new DateTime($params['to_date']);

    $contract = civicrm_api3('HRJobContract', 'getcontractswithdetailsinperiod', [
      'contact_id' => $params['contact_id'],
      'start_date' => $fromDate->format('Y-m-d'),
      'end_date' => $toDate->format('Y-m-d'),
    ]);

    if ($contract['count'] > 1) {
      throw new InvalidLeaveRequestException(
        'The Leave request dates must not have dates in more than one contract period',
        'leave_request_overlapping_multiple_contracts',
        'from_date'
      );
    }
  }

  /**
   * This method checks and ensures that the balance change for the leave request is
   * not greater than the remaining balance of the period if the Request’s AbsenceType
   * do not allow overuse.
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateBalanceChange($params) {
    $toDate = new DateTime($params['to_date']);
    $fromDate = new DateTime($params['from_date']);
    $absenceType = AbsenceType::findById($params['type_id']);
    $period = AbsencePeriod::getPeriodContainingDates($fromDate, $toDate);

    $leavePeriodEntitlement = LeavePeriodEntitlement::getPeriodEntitlementForContact($params['contact_id'], $period->id, $params['type_id']);
    $currentBalance = $leavePeriodEntitlement->getBalance();
    $leaveRequestBalance = self::calculateBalanceChangeFromCreateParams($params);

    if(!$absenceType->allow_overuse && $leaveRequestBalance > $currentBalance) {
      throw new InvalidLeaveRequestException(
        'Balance change for the leave request cannot be greater than the remaining balance of the period',
        'leave_request_balance_change_greater_than_remaining_balance',
        'type_id'
      );
    }
  }

  /**
   * This method returns the balance change that a leave request will have
   * based on the params received by the create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @return float
   */
  private static function calculateBalanceChangeFromCreateParams($params) {
    $leaveRequestOptionsValue = self::getLeaveRequestDayTypeOptionsGroupByValue();

    $fromDateType = $leaveRequestOptionsValue[$params['from_date_type']];
    $toDateType = $leaveRequestOptionsValue[$params['to_date_type']];

    $leaveRequestBalance = self::calculateBalanceChange(
      $params['contact_id'],
      new DateTime($params['from_date']),
      $fromDateType,
      new DateTime($params['to_date']),
      $toDateType
    );

    return abs($leaveRequestBalance['amount']);
  }

  /**
   * This method validates that the leave request to be created has at least one day
   * The logic is based on the fact that if there's no working day for a leave request
   * the returned balance change will be Zero.
   *
   * @param array $params
   *  The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateWorkingDay($params) {
    $leaveRequestBalance = self::calculateBalanceChangeFromCreateParams($params);
    if ($leaveRequestBalance == 0) {
      throw new InvalidLeaveRequestException(
        'Leave Request must have at least one working day to be created',
        'leave_request_doesnt_have_working_day',
        'from_date'
      );
    }
  }

  /**
   * This method checks that there is no overlapping leave request
   * with the status Approved, Admin Approved, Waiting Approval or More Information Requested
   * (Exception: if the other Leave Request is a Public Holiday Leave Request, then it can overlap).
   *
   * @params array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateNoOverlappingLeaveRequests($params) {
    $leaveRequestStatuses = array_flip(self::buildOptions('status_id'));

    $leaveRequestStatusFilter = [
      $leaveRequestStatuses['Approved'],
      $leaveRequestStatuses['Admin Approved'],
      $leaveRequestStatuses['Waiting Approval'],
      $leaveRequestStatuses['More Information Requested'],
    ];

    $overlappingLeaveRequests = self::findOverlappingLeaveRequests(
      $params['contact_id'],
      $params['from_date'],
      $params['to_date'],
      $leaveRequestStatusFilter
    );

    //if its an update and the only overlapping leave request is the leave request being updated itself
    if (!empty($params['id'])) {
      if(count($overlappingLeaveRequests) == 1 &&  $overlappingLeaveRequests[0]->id == $params['id']){
        return;
      }
    }

    if ($overlappingLeaveRequests) {
      throw new InvalidLeaveRequestException(
        'This Leave request has dates that overlaps with an existing leave request',
        'leave_request_overlaps_another_leave_request',
        'from_date'
      );
    }
  }

  /**
   * This method checks that the start date of a leave request should not be
   * greater than the end date provided the request is for a non single day leave request.
   * For single day leave requests, the end date is optional
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateStartDateNotGreaterThanEndDate($params) {
      $fromDate = new DateTime($params['from_date']);
      $toDate = new DateTime($params['to_date']);

      if ($fromDate > $toDate) {
        throw new InvalidLeaveRequestException(
          'Leave Request start date cannot be greater than the end date',
          'leave_request_from_date_greater_than_end_date',
          'from_date'
        );
      }
  }

  /**
   * This method checks that the number of days for the Leave Request
   * is not greater than the max_consecutive_leave_days for the absence type
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateLeaveDaysAgainstAbsenceTypeMaxConsecutiveLeaveDays($params) {
    $absenceType = AbsenceType::findById($params['type_id']);
    $fromDate = new DateTime($params['from_date']);
    $toDate = new DateTime($params['to_date']);

    $interval = $toDate->diff($fromDate);
    $intervalInDays = $interval->format("%a");

    if (!empty($absenceType->max_consecutive_leave_days) && $intervalInDays > $absenceType->max_consecutive_leave_days) {
      throw new InvalidLeaveRequestException(
        'Leave Request days cannot be greater than maximum consecutive days for absence type',
        'leave_request_days_greater_than_max_consecutive_days',
        'type_id'
      );
    }
  }

  /**
   * This method checks if the absence type allows cancellation in advance of start date and that the leave request from_date
   * should not be in the past in the event of a leave request cancellation by a user.
   *
   * Also checks that a user's leave request should not be cancelled if the absence type does not
   * allow leave request cancellation
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateAbsenceTypeAllowRequestCancellationForLeaveRequestCancellation($params) {
    $leaveRequestStatuses = array_flip(self::buildOptions('status_id', 'validate'));
    $leaveRequestIsForCurrentUser = CRM_Core_Session::getLoggedInContactID() == $params['contact_id'];
    $isACancellationRequest = ($params['status_id'] == $leaveRequestStatuses['cancelled']);

    if($leaveRequestIsForCurrentUser && $isACancellationRequest) {
      $absenceType = AbsenceType::findById($params['type_id']);
      $today = new DateTime('today');
      $fromDate = new DateTime($params['from_date']);

      if($absenceType->allow_request_cancelation == AbsenceType::REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE && $fromDate < $today) {
        throw new InvalidLeaveRequestException(
          'Leave Request with past days cannot be cancelled',
          'leave_request_past_days_cannot_be_cancelled',
          'type_id'
        );
      }

      if($absenceType->allow_request_cancelation == AbsenceType::REQUEST_CANCELATION_NO) {
        throw new InvalidLeaveRequestException(
          'Absence Type does not allow leave request cancellation',
          'leave_request_absence_type_disallows_cancellation',
          'type_id'
        );
      }
    }
  }

  /**
   * This method validates that the absence type is active and is valid for the
   * type of request. That is, if this is a Sickness Request, then only absence
   * types where is_sick is set can be used. If it's a TOIL Request, then only
   * absence types where allow_accruals_request is set can be used.
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateAbsenceTypeIsActiveAndValid($params) {
    $absenceType = AbsenceType::findById($params['type_id']);

    if (!$absenceType->is_active) {
      throw new InvalidLeaveRequestException(
        'Absence Type is not active',
        'leave_request_absence_type_not_active',
        'type_id'
      );
    }

    if($params['request_type'] === self::REQUEST_TYPE_SICKNESS && !$absenceType->is_sick) {
      throw new InvalidLeaveRequestException(
        'This absence type does not allow sickness requests',
        'leave_request_invalid_sickness_absence_type',
        'type_id'
      );
    }

    if($params['request_type'] === self::REQUEST_TYPE_TOIL && !$absenceType->allow_accruals_request) {
      throw new InvalidLeaveRequestException(
        'This absence type does not allow TOIL requests',
        'leave_request_invalid_toil_absence_type',
        'type_id'
      );

    }
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
    $leaveRequest = new self();
    $leaveRequest->contact_id = (int)$contactID;
    $leaveRequest->from_date = date('Ymd', strtotime($publicHoliday->date));
    $leaveRequest->request_type = self::REQUEST_TYPE_PUBLIC_HOLIDAY;

    $leaveRequest->find(true);
    if($leaveRequest->id) {
      $leaveRequest->fetch();

      return $leaveRequest;
    }

    return null;
  }

  /**
   * Returns the leave request days already existing for the given contact
   * within the $fromDate to $todDate period and having the statuses supplied.
   *
   * @param int $contactID
   * @param string $fromDate
   * @param string $toDate
   * @param array $leaveRequestStatus
   * @param boolean $excludePublicHolidayLeaveRequests
   *   Whether to exclude public holiday leave requests from overlapping leave requests or not
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest[]|null
   */
  public static function findOverlappingLeaveRequests($contactID, $fromDate, $toDate, $leaveRequestStatus = [], $excludePublicHolidayLeaveRequests = true) {
    $leaveRequestTable = self::getTableName();
    $leaveRequestDateTable = LeaveRequestDate::getTableName();
    $leaveBalanceChangeTable = LeaveBalanceChange::getTableName();

    $query = "
      SELECT DISTINCT lr.* FROM {$leaveRequestTable} lr
      INNER JOIN {$leaveRequestDateTable} lrd 
        ON lrd.leave_request_id = lr.id
      INNER JOIN {$leaveBalanceChangeTable} lbc
        ON lbc.source_id = lrd.id AND lbc.source_type = %1
      WHERE lr.contact_id = %2
    ";

    if (!empty($toDate)){
      $query .= ' AND lrd.date BETWEEN %3 AND %4';
    }
    else{
      $query .= ' AND lrd.date = %3';
    }

    if (is_array($leaveRequestStatus) && !empty($leaveRequestStatus)) {
      array_walk($leaveRequestStatus, 'intval');
      $query .= ' AND lr.status_id IN('. implode(', ', $leaveRequestStatus) .')';
    }

    if ($excludePublicHolidayLeaveRequests) {
      $query .= " AND lr.request_type != %5";
    }

    $params = [
      1 => [LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY, 'String'],
      2 => [$contactID, 'Integer'],
      3 => [CRM_Utils_Date::processDate($fromDate, null, false, 'Y-m-d'), 'String'],
      5 => [self::REQUEST_TYPE_PUBLIC_HOLIDAY, 'String']
    ];
    if (!empty($toDate)) {
      $params[4] = [CRM_Utils_Date::processDate($toDate, null, false, 'Y-m-d'), 'String'];
    }

    $leaveRequest = CRM_Core_DAO::executeQuery($query, $params, true, self::class);

    $overlappingLeaveRequests = [];
    while($leaveRequest->fetch()) {
      $overlappingLeaveRequests[] = clone $leaveRequest;
    }
    return $overlappingLeaveRequests;
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

    $datePeriod = new BasicDatePeriod($this->from_date, $this->to_date);

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
   * @param int $contactId
   * @param \DateTime $fromDate
   * @param string $fromDateType
   * @param \DateTime $toDate
   * @param string $toDateType
   *
   * @return array
   *   An array of formatted results
   */
  public static function calculateBalanceChange($contactId, DateTime $fromDate, $fromDateType, DateTime $toDate, $toDateType) {
    $leaveRequest = new self();
    $leaveRequest->contact_id = $contactId;

    $datePeriod = new BasicDatePeriod($fromDate, $toDate);

    $leaveRequestDayTypes = array_flip(self::buildOptions('from_date_type', 'validate'));

    $halfDayTypesValues = [
      $leaveRequestDayTypes['half_day_am'],
      $leaveRequestDayTypes['half_day_pm'],
    ];
    $fromDateIsHalfDay = in_array($fromDateType, $halfDayTypesValues);
    $toDateIsHalfDay = in_array($toDateType, $halfDayTypesValues);

    $resultsBreakdown = [
      'amount' => 0,
      'breakdown' => []
    ];

    $leaveRequestDayTypeOptionsGroup = self::getLeaveRequestDayTypeOptionsGroup();

    foreach ($datePeriod as $date) {
      if (self::publicHolidayLeaveRequestExists($contactId, $date)) {
        $amount = 0.0;
        $dayType = $leaveRequestDayTypes['public_holiday'];
      }
      else {
        $amount = LeaveBalanceChange::calculateAmountForDate($leaveRequest, $date);
        $type = ContactWorkPattern::getWorkDayType($contactId, $date);
        $dayType = self::getLeaveRequestDayTypeFromWorkDayType($type);
      }

      //since its an half day, 0.5 will be deducted irrespective of the amount returned from the work pattern
      if($fromDateIsHalfDay && $date == $fromDate && $amount != 0) {
        $amount = -0.5;
        $dayType = $fromDateType;
      }

      //since its an half day, 0.5 will be deducted irrespective of the amount returned from the work pattern
      if($toDateIsHalfDay && $date == $toDate && $amount !=0) {
        $amount = -0.5;
        $dayType = $toDateType;
      }

      $result = [
        'date' => $date->format('Y-m-d'),
        'amount' => abs($amount),
        'type' => $leaveRequestDayTypeOptionsGroup[$dayType]
      ];
      $resultsBreakdown['amount'] += $amount;
      $resultsBreakdown['breakdown'][] = $result;
    }

    return $resultsBreakdown;
  }

  /**
   * Returns the LeaveRequest Day Type ID equivalent of a WorkDay Type ID based on a mapping
   *
   * @param int|string $type
   *   The WorkDay Type ID
   *
   * @return int|null
   *   The LeaveRequest Day Type ID
   */
  private static function getLeaveRequestDayTypeFromWorkDayType($type) {
    $leaveRequestDayTypes = array_flip(self::buildOptions('from_date_type', 'validate'));

    switch($type) {
      case WorkDay::getNonWorkingDayTypeValue():
        return $leaveRequestDayTypes['non_working_day'];

      case WorkDay::getWorkingDayTypeValue():
        return $leaveRequestDayTypes['all_day'];

      case WorkDay::getWeekendTypeValue():
        return $leaveRequestDayTypes['weekend'];

      default:
        return '';
    }
  }

  /**
   * Returns LeaveRequest Day Type Options in a nested array format
   * with the day_type_id key as the array key and details about the day_type_id
   * as the value
   *
   * @return array
   *   [
   *     '1' => [
   *       'id' => 1,
   *       'value' => '1',
   *       'label' => 'All Day'
   *     ],
   *     '2 => [
   *       'id' => 2,
   *       'value' => '2',
   *       'label' => '1/2 AM'
   *     ]
   *   ]
   */
  private static function getLeaveRequestDayTypeOptionsGroup() {
    $leaveRequestDayTypeOptionsGroup = [];
    $leaveRequestDayTypeOptions = self::buildOptions('from_date_type');

    foreach($leaveRequestDayTypeOptions  as $key => $label) {
      $leaveRequestDayTypeOptionsGroup[$key] = [
        'id' => $key,
        'value' => $key,
        'label' => $label
      ];
    }

    return $leaveRequestDayTypeOptionsGroup;
  }

  /**
   * Returns a LeaveRequest Object if a public holiday leave request exists for the given date
   *
   * @param int $contactID
   * @param \DateTime $date
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest|null
   */
  private static function publicHolidayLeaveRequestExists($contactID, DateTime $date) {
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = $date->format('Y-m-d');
    return self::findPublicHolidayLeaveRequest($contactID, $publicHoliday);
  }

  /**
   * Returns LeaveRequest Day Type Options in a nested array format
   * with the day_type_id key as the array key and details about the day_type_id as the value
   *
   * @return array
   *   [
   *     '1' => [
   *     'id' => 1,
   *     'value' => '1',
   *     'name' => 'all_day'
   *     ],
   *     '2 => [
   *     'id' => 2,
   *     'value' => '2',
   *     'name' => 'half_day_am',
   *     ]
   *   ]
   */
  private static function getLeaveRequestDayTypeOptionsGroupByValue() {
    $leaveRequestDayTypeOptionsGroup = [];
    $leaveRequestDayTypeOptions = self::buildOptions('from_date_type');
    foreach($leaveRequestDayTypeOptions  as $key => $label) {
      $name = CRM_Core_Pseudoconstant::getName(self::class, 'from_date_type', $key);
      $leaveRequestDayTypeOptionsGroup[$key] = [
        'id' => $key,
        'value' => $key,
        'name' => $name
      ];
    }
    return $leaveRequestDayTypeOptionsGroup;
  }

  /**
   * Deletes the TOIL Requests associated with an Absence Type (within the given Absence Period)
   * and all their LeaveBalanceChanges and LeaveRequestDates.
   *
   * @param int $absenceTypeID
   *   The absence Type that TOIL requests is to be deleted for.
   * @param DateTime $startDate
   *   Records linked to LeaveRequests with from_date >= this date will be deleted
   */
  public static function deleteAllNonExpiredTOILRequestsForAbsenceType($absenceTypeID, DateTime $startDate) {
    $leaveBalanceChangeTable = LeaveBalanceChange::getTableName();
    $leaveRequestTable = self::getTableName();
    $leaveRequestDateTable = LeaveRequestDate::getTableName();

    $query = "DELETE bc, lrd, lr 
              FROM {$leaveRequestTable} lr
              INNER JOIN {$leaveRequestDateTable} lrd 
                ON lrd.leave_request_id = lr.id 
              INNER JOIN {$leaveBalanceChangeTable} bc 
                ON bc.source_id = lrd.id AND bc.source_type = %1 
              WHERE lr.type_id = %2 AND 
                    lr.from_date >= %3 AND 
                    lr.request_type = %4 AND
                    lr.toil_expiry_date > %5
              ";
    $params = [
      1 => [LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY, 'String'],
      2 => [$absenceTypeID, 'Integer'],
      3 => [$startDate->format('Y-m-d'), 'String'],
      4 => [self::REQUEST_TYPE_TOIL, 'String'],
      5 => [date('Y-m-d'), 'String']
    ];

    CRM_Core_DAO::executeQuery($query, $params);
  }

  /**
   *{@inheritdoc}
   */
  public function addSelectWhereClause() {
    if (CRM_Core_Permission::check([['view all contacts', 'edit all contacts']])) {
      return;
    }

    $clauses['contact_id'] = $this->getLeaveInformationACLClauses();

    CRM_Utils_Hook::selectWhereClause($this, $clauses);
    return $clauses;
  }
}
