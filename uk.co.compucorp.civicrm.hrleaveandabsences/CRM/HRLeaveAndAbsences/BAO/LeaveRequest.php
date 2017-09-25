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
use \CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_Hrjobcontract_BAO_HRJobContract as JobContract;

class CRM_HRLeaveAndAbsences_BAO_LeaveRequest extends CRM_HRLeaveAndAbsences_DAO_LeaveRequest {

  use CRM_HRLeaveAndAbsences_ACL_LeaveInformationTrait;

  const REQUEST_TYPE_LEAVE = 'leave';
  const REQUEST_TYPE_SICKNESS = 'sickness';
  const REQUEST_TYPE_TOIL = 'toil';
  const REQUEST_TYPE_PUBLIC_HOLIDAY = 'public_holiday';

  //Validations Mode for the create method
  const VALIDATIONS_ON = 1;
  const VALIDATIONS_OFF = 2;
  const IMPORT_VALIDATION = 3;

  /**
   * Create a new LeaveRequest based on array-data
   *
   * @param array $params key-value pairs
   * @param int $validationMode
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest|NULL
   *
   * @throws \Exception
   */
  public static function create($params, $validationMode = self::VALIDATIONS_ON) {
    $entityName = 'LeaveRequest';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);

    self::validateParams($params, $validationMode);
    unset($params['is_deleted']);

    $datesChanged = self::datesChanged($params);
    $instance = new self();
    $instance->copyValues($params);

    $transaction = new CRM_Core_Transaction();
    try {
      $instance->save();
      $instance->saveDates($datesChanged);
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
   * @param int $validationMode
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  public static function validateParams($params, $validationMode = self::VALIDATIONS_ON) {
    if($validationMode == self::VALIDATIONS_OFF) {
      return;
    }
    self::validateMandatory($params);
    self::validateLeaveRequestSoftDeleteDuringUpdate($params);
    self::validateRequestType($params);
    self::validateTOILFieldsBasedOnRequestType($params);
    self::validateSicknessFieldsBasedOnRequestType($params);
    self::validateStartDateNotGreaterThanEndDate($params);
    self::validateNoOverlappingLeaveRequests($params);
    self::validateLeaveDatesDoesNotOverlapContractsWithLapses($params);

    list($absenceType, $absencePeriod) = self::loadCommonObjects($params);
    self::validateAbsenceTypeIsActiveAndValid($params, $absenceType);
    self::validateTOILRequest($params, $absenceType, $absencePeriod);
    self::validateLeaveDaysAgainstAbsenceTypeMaxConsecutiveLeaveDays($params, $absenceType);
    self::validateAbsenceTypeAllowRequestCancellationForLeaveRequestCancellation($params, $absenceType);
    self::validateAbsencePeriod($params, $absencePeriod);

    if($validationMode != self::IMPORT_VALIDATION) {
      self::validateEntitlementAndWorkingDayAndBalanceChange($params, $absenceType, $absencePeriod);
    }


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
   * A method for validating that a LeaveRequest cannot be soft deleted
   * during an update on the BAO.
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  public static function validateLeaveRequestSoftDeleteDuringUpdate($params) {
    if (isset($params['id']) && !empty($params['is_deleted'])) {
      throw new InvalidLeaveRequestException(
        'Leave Request can not be soft deleted during an update, use the delete method instead!',
        'leave_request_cannot_be_soft_deleted',
        'is_deleted'
      );
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
   * @param AbsenceType $absenceType
   * @param AbsencePeriod $absencePeriod
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateTOILRequest($params, $absenceType, $absencePeriod) {
    if($params['request_type'] !== self::REQUEST_TYPE_TOIL) {
      return;
    }

    self::validateTOILToAccrueIsAValidOptionValue($params);
    self::validateTOILPastDays($params, $absenceType);
    self::validateTOILToAccruedAmountIsValid($params, $absenceType, $absencePeriod);
  }

  /**
   * Validates if the value passed to the TOIL To Accrue field is one of the
   * options available on the hrleaveandabsences_toil_amounts option group and
   * is also a numeric value.
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateTOILToAccrueIsAValidOptionValue($params) {
    $toilAmountOptions = array_flip(self::buildOptions('toil_to_accrue', 'validate'));
    if(!in_array($params['toil_to_accrue'], $toilAmountOptions) || !is_numeric($params['toil_to_accrue'])) {
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
   * @param AbsenceType $absenceType
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateTOILPastDays($params, $absenceType) {
    $fromDate = new DateTime($params['from_date']);
    $toDate = new DateTime($params['to_date']);
    $todayDate = new DateTime('today');
    $leaveDatesHasPastDates = $fromDate < $todayDate || $toDate < $todayDate;

    if ($leaveDatesHasPastDates && !$absenceType->allow_accrue_in_the_past) {
      throw new InvalidLeaveRequestException(
        'You may only request TOIL for overtime to be worked in the future. Please modify the date of this request',
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
   * @param AbsenceType $absenceType
   * @param AbsencePeriod $absencePeriod
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateTOILToAccruedAmountIsValid($params, $absenceType, $absencePeriod) {
    $unlimitedAccrual = empty($absenceType->max_leave_accrual) && $absenceType->max_leave_accrual !== 0;
    $oldToilRequest = '';

    if (!empty($params['id'])) {
      $oldToilRequest = self::findById($params['id']);
    }

    $periodContainingToilDates = $absencePeriod;
    $totalApprovedToilForPeriod = self::getTotalApprovedToilForPeriod(
      $periodContainingToilDates,
      $params['contact_id'],
      $params['type_id']
    );
    $totalProjectedToilForPeriod = $totalApprovedToilForPeriod + $params['toil_to_accrue'];

    if ($oldToilRequest && $oldToilRequest->id && self::isAlreadyApproved($oldToilRequest)) {
      $periodContainingOldToilDates = self::getPeriodContainingDates($oldToilRequest);
      $isSamePeriodWithOldToil = $periodContainingOldToilDates->id == $periodContainingToilDates->id;

      if ($isSamePeriodWithOldToil) {
        $totalProjectedToilForPeriod -= $oldToilRequest->toil_to_accrue;
      }
    }

    $maxLeaveAccrual = $absenceType->max_leave_accrual;
    if ($totalProjectedToilForPeriod > $maxLeaveAccrual && !$unlimitedAccrual) {
      throw new InvalidLeaveRequestException(
        'The maximum amount of leave that you can accrue is '. round($maxLeaveAccrual, 1) .' days. Please modify the dates of this request',
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
    return LeaveBalanceChange::getTotalApprovedToilForPeriod($period, $contactID, $typeID);
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
   * @param AbsencePeriod $absencePeriod
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateAbsencePeriod($params, $absencePeriod) {
    //this condition means that no absence period was found that contains both the start and end date
    //either there was an overlap or the absence period does not simply exist.
    if (!$absencePeriod) {
      throw new InvalidLeaveRequestException(
        'The Leave request dates are not contained within a valid absence period',
        'leave_request_not_within_absence_period',
        'from_date'
      );
    }
  }

  /**
   * This method validates that the leave request does not overlap
   * contracts with lapses in between any of the contract periods.
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateLeaveDatesDoesNotOverlapContractsWithLapses($params) {
    $fromDate = new DateTime($params['from_date']);
    $toDate = new DateTime($params['to_date']);
    $contractsOverlappingToAndFromDates = JobContract::getContractsWithDetailsInPeriod(
      $fromDate->format('Y-m-d'), $toDate->format('Y-m-d'),
      $params['contact_id']
    );

    if (count($contractsOverlappingToAndFromDates) > 1) {
      $contractToCompare = reset($contractsOverlappingToAndFromDates);
      while($nextContract = next($contractsOverlappingToAndFromDates)) {
        $intervalInDays = self::getDateIntervalInDays(
          new DateTime($contractToCompare['period_end_date']),
          new DateTime($nextContract['period_start_date'])
        );

        $contractToCompare = $nextContract;

        if($intervalInDays > 1) {
          throw new InvalidLeaveRequestException(
            'This leave request is after your contract end date. Please modify dates of this request',
            'leave_request_overlapping_multiple_contracts',
            'from_date'
          );
        }
      }
    }
  }

  /**
   * This method checks and ensures that the balance change for the leave request is
   * not greater than the remaining balance of the period if the Request’s AbsenceType
   * do not allow overuse. It also validates that the leave request to be created has at least one day,
   * The logic is based on the fact that if there's no working day for a leave request
   * the returned balance change will be Zero.
   * In case the contact does not have a period entitlement for the absence type, an
   * appropriate exception is thrown.
   *
   * @param array $params
   *   The params array received by the create method
   * @param AbsenceType $absenceType
   * @param AbsencePeriod $period
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateEntitlementAndWorkingDayAndBalanceChange($params, $absenceType, $period) {
    $leavePeriodEntitlement = LeavePeriodEntitlement::getPeriodEntitlementForContact($params['contact_id'], $period->id, $params['type_id']);
    if(!$leavePeriodEntitlement) {
      throw new InvalidLeaveRequestException(
        'Contact does not have period entitlement for the absence type',
        'leave_request_contact_has_no_entitlement',
        'type_id'
      );
    }

    //TOIL accrual is independent of Current Balance.
    if($params['request_type'] == self::REQUEST_TYPE_TOIL) {
      return;
    }

    // Leave Request is able to be rejected or cancelled disregarding the balance
    if (in_array($params['status_id'], self::getCancelledStatuses())) {
      return;
    }

    $leaveRequestBalance = self::getLeaveRequestBalance($params);
    if ($leaveRequestBalance == 0) {
      throw new InvalidLeaveRequestException(
        'Leave Request must have at least one working day to be created',
        'leave_request_doesnt_have_working_day',
        'from_date'
      );
    }

    $requestsToExcludeFromBalance = [];
    if (!empty($params['id'])) {
      $oldLeaveRequest = self::findById($params['id']);

      if (self::isAlreadyApproved($oldLeaveRequest)) {
        $requestsToExcludeFromBalance[] = $oldLeaveRequest->id;
      }
    }

    $currentBalance = $leavePeriodEntitlement->getBalance($requestsToExcludeFromBalance);

    if(!$absenceType->allow_overuse && ($currentBalance + $leaveRequestBalance) < 0) {
      throw new InvalidLeaveRequestException(
        'There are only '. $currentBalance .' days leave available. This request cannot be made or approved',
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
    $leaveRequestBalance = self::calculateBalanceChange(
      $params['contact_id'],
      new DateTime($params['from_date']),
      $params['from_date_type'],
      new DateTime($params['to_date']),
      $params['to_date_type']
    );

    return $leaveRequestBalance['amount'];
  }

  /**
   * This method checks that there is no overlapping leave request
   * with the status Approved, Admin Approved, Awaiting Approval or More Information Required
   * (Exception: if the other Leave Request is a Public Holiday Leave Request, then it can overlap).
   *
   * @params array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateNoOverlappingLeaveRequests($params) {
    $leaveRequestStatuses = self::getStatuses();

    $leaveRequestStatusFilter = [
      $leaveRequestStatuses['approved'],
      $leaveRequestStatuses['admin_approved'],
      $leaveRequestStatuses['awaiting_approval'],
      $leaveRequestStatuses['more_information_required'],
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
        'This leave request overlaps with another request. Please modify dates of this request',
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
   * @param AbsenceType $absenceType
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateLeaveDaysAgainstAbsenceTypeMaxConsecutiveLeaveDays($params, $absenceType) {
    $fromDate = new DateTime($params['from_date']);
    $toDate = new DateTime($params['to_date']);

    $interval = $toDate->diff($fromDate);
    $intervalInDays = $interval->format("%a");
    $maxConsecutiveLeaveDays = $absenceType->max_consecutive_leave_days;

    if (!empty($maxConsecutiveLeaveDays) && $intervalInDays > $maxConsecutiveLeaveDays) {
      throw new InvalidLeaveRequestException(
        'Only a maximum '. round($maxConsecutiveLeaveDays, 1) .' days leave can be taken in one request. Please modify days of this request',
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
   * @param AbsenceType $absenceType
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateAbsenceTypeAllowRequestCancellationForLeaveRequestCancellation($params, $absenceType) {
    $leaveRequestStatuses = self::getStatuses();
    $leaveRequestIsForCurrentUser = CRM_Core_Session::getLoggedInContactID() == $params['contact_id'];
    $isACancellationRequest = ($params['status_id'] == $leaveRequestStatuses['cancelled']);

    if($leaveRequestIsForCurrentUser && $isACancellationRequest) {
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
   * @param AbsenceType $absenceType
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   */
  private static function validateAbsenceTypeIsActiveAndValid($params, $absenceType) {
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
    $leaveRequest->is_deleted = 0;

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
      WHERE lr.contact_id = %2 AND lr.is_deleted = 0
      AND lrd.date BETWEEN %3 AND %4
    ";

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
      4 => [CRM_Utils_Date::processDate($toDate, null, false, 'Y-m-d'), 'String'],
      5 => [self::REQUEST_TYPE_PUBLIC_HOLIDAY, 'String']
    ];

    $leaveRequest = CRM_Core_DAO::executeQuery($query, $params, true, self::class);

    $overlappingLeaveRequests = [];
    while($leaveRequest->fetch()) {
      $overlappingLeaveRequests[] = clone $leaveRequest;
    }
    return $overlappingLeaveRequests;
  }

  /**
   * Returns a list of all possible statuses for a Leave Request
   *
   * @return array
   */
  public static function getStatuses() {
    return array_flip(self::buildOptions('status_id', 'validate'));
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
   * Creates and saves LeaveRequestDates for this LeaveRequest.
   *
   * If its an update and the dates has not changed when comparing
   * the values in the db to the values about to be updated, i.e
   * dateChanged = false, the previous dates are not deleted and
   * re-created but left as is.
   *
   * @param bool|null $datesChanged
   */
  private function saveDates($datesChanged = null) {
    if($datesChanged === false) {
      return;
    }

    $this->deleteDatesAndBalanceChanges();

    $datePeriod = new BasicDatePeriod($this->from_date, $this->to_date);

    foreach ($datePeriod as $date) {
      LeaveRequestDate::create([
        'date' => $date->format('YmdHis'),
        'leave_request_id' => $this->id
      ]);
    }
  }

  /**
   * Deletes all the dates and balance changes related to this LeaveRequest
   */
  private function deleteDatesAndBalanceChanges() {
    LeaveBalanceChange::deleteAllForLeaveRequest($this);
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
   *       'label' => 'Half-day AM'
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
   * Soft Deletes the LeaveRequest with the given ID by setting the is_deleted column to 1
   *
   * @param int $id
   *   The ID of the LeaveRequest to be soft deleted
   */
  public static function softDelete($id) {
    $leaveRequest = self::findById($id);
    $leaveRequest->is_deleted = 1;
    $leaveRequest->save();
  }

  /**
   * This function overrides the \CRM_Core_DAO::findById method.
   * The difference is that it takes the is_deleted property into consideration when
   * finding a leave request record.
   *
   * @param int $id
   *   ID of leave request to be found
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest
   *
   * @throws \Exception
   */
  public static function findById($id) {
    $leaveRequest = new self();
    $leaveRequest->id = $id;
    $leaveRequest->is_deleted = 0;
    if (!$leaveRequest->find(TRUE)) {
      throw new Exception("Unable to find a " . self::class . " with id {$id}.");
    }

    return $leaveRequest;
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

  /**
   * Returns Statuses on which a Leave Request is considered to be Approved
   *
   * @return array
   */
  public static function getApprovedStatuses() {
    $leaveStatuses = self::getStatuses();

    return [$leaveStatuses['approved'], $leaveStatuses['admin_approved']];
  }

  /**
   * Returns Statuses on which a Leave Request is considered to be Open
   *
   * @return array
   */
  public static function getOpenStatuses() {
    $leaveStatuses = self::getStatuses();

    return [$leaveStatuses['awaiting_approval'], $leaveStatuses['more_information_required']];
  }

  /**
   * Returns Statuses on which a Leave Request is considered to be Cancelled
   *
   * @return array
   */
  public static function getCancelledStatuses() {
    $leaveStatuses = self::getStatuses();

    return [$leaveStatuses['cancelled'], $leaveStatuses['rejected']];
  }

  /**
   * Checks whether a leave request has been approved.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return bool
   */
  private static function isAlreadyApproved(LeaveRequest $leaveRequest) {
    $oldStatus = $leaveRequest->status_id;

    return in_array($oldStatus, self::getApprovedStatuses());
  }

  /**
   * Returns the Absence Period containing the from_date and to_date
   * of the Leave Request.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod|null
   */
  private static function getPeriodContainingDates(LeaveRequest $leaveRequest) {
    $periodContainingDates = AbsencePeriod::getPeriodContainingDates(
      new DateTime($leaveRequest->from_date),
      new DateTime($leaveRequest->to_date)
    );

    return $periodContainingDates;
  }

  /**
   * Returns the interval in days between the from and to Dates.
   *
   * @param \DateTime $fromDate
   * @param \DateTime $toDate
   *
   * @return int
   */
  private static function getDateIntervalInDays(DateTime $fromDate, DateTime $toDate) {
    $interval = $toDate->diff($fromDate);
    return (int) $interval->format("%a");
  }

  /**
   * Returns the objects commonly used in some of the validation functions.
   *
   * @param array $params
   *
   * @return array
   */
  private static function loadCommonObjects($params) {
    $toDate = new DateTime($params['to_date']);
    $fromDate = new DateTime($params['from_date']);
    $absenceType = AbsenceType::findById($params['type_id']);
    $period = AbsencePeriod::getPeriodContainingDates($fromDate, $toDate);

    return [$absenceType, $period];
  }

  /**
   * Checks if the from_date or to_date of a leave request has changed by comparing the
   * date values to be updated to the current values in the database
   *
   * @param array $params
   *
   * @return bool|null
   *   Returns null for when a leave request is newly
   *   created.
   */
  public static function datesChanged($params) {
    if(!empty($params['id'])) {
      $leaveRequest = self::findById($params['id']);
      $fromDate = new DateTime($params['from_date']);
      $fromDateType = $params['from_date_type'];
      $toDate = new DateTime($params['to_date']);
      $toDateType = $params['to_date_type'];
      $leaveRequestFromDate = new DateTime($leaveRequest->from_date);
      $leaveRequestFromDateType = $leaveRequest->from_date_type;
      $leaveRequestToDate = new DateTime($leaveRequest->to_date);
      $leaveRequestToDateType = $leaveRequest->to_date_type;

      $isNotSameFromDate = $leaveRequestFromDate != $fromDate;
      $isNotSameFromDateType = $leaveRequestFromDateType != $fromDateType;
      $isNotSameToDate = $leaveRequestToDate != $toDate;
      $isNotSameToDateType = $leaveRequestToDateType != $toDateType;

      return ($isNotSameFromDate || $isNotSameFromDateType) ||
        ($isNotSameToDate || $isNotSameToDateType);
    }

    return null;
  }

  /**
   * Returns the balance change for the leave request.
   *
   * If its an already created leave request and the dates did not change
   * and the change_balance parameter is false, the balance change as of
   * when the leave request was created is returned.
   *
   * @param array $params
   *
   * @return float
   */
  private static function getLeaveRequestBalance($params) {
    $useNewBalance = !empty($params['change_balance']);
    $useOldBalance = !empty($params['id']) &&
      !self::datesChanged($params) && !$useNewBalance;

    if($useOldBalance) {
      $leaveRequest = self::findById($params['id']);
      return LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    }

    return self::calculateBalanceChangeFromCreateParams($params);
  }
}
