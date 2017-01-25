<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException as InvalidTOILRequestException;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_TOILRequest as TOILRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

class CRM_HRLeaveAndAbsences_BAO_TOILRequest extends CRM_HRLeaveAndAbsences_DAO_TOILRequest {

  /**
   * Create a new TOILRequest based on array-data
   *
   * @param array $params key-value pairs
   * @param boolean $validate
   *   Whether to allow validation in LeaveRequest.create method or not
   *
   * @return CRM_HRLeaveAndAbsences_BAO_TOILRequest
   **/
  public static function create($params, $validate = true) {
    $entityName = 'TOILRequest';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    if ($validate) {
      self::validateParams($params);
    }

    $instance = new self();
    //set from_date_type and to_date_type to be full day by default
    $dateTypeOptions = array_flip(LeaveRequest::buildOptions('from_date_type'));
    $params['from_date_type'] = $dateTypeOptions['All Day'];
    $params['to_date_type'] = $dateTypeOptions['All Day'];

    if ($hook == 'edit') {
      $instance->id = $params['id'];
      $instance->find(true);

      if ($instance->leave_request_id) {
        $instance->copyValues($params);
        $params['id'] = $instance->leave_request_id;
        $leaveRequest = LeaveRequest::create($params, false);
        $instance->save();
      }
    }

    if ($hook == 'create') {
      $leaveRequest = LeaveRequest::create($params, false);
      $instance->copyValues($params);
      $instance->leave_request_id = $leaveRequest->id;
      $instance->save();
    }

    $expiryDate = !empty($params['expiry_date']) ? new DateTime($params['expiry_date']) : null;
    $instance->saveBalanceChange($leaveRequest, $params['toil_to_accrue'], $expiryDate);
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * A method for validating the params passed to the TOIL Request create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   */
  public static function validateParams($params) {
    self::validateMandatoryFields($params);
    self::validateTOILAmountIsValid($params);
    self::validateAbsenceTypeAllowsAccrual($params);
    self::validateValidTOILAmountNotGreaterThanMaximum($params);
    self::validateValidTOILPastDaysRequest($params);

    //run LeaveRequest Validation after all validations on TOIL Request
    LeaveRequest::validateParams($params);
  }

  /**
   * Validates if all the mandatory fields are present
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   */
  private static function validateMandatoryFields($params) {
    if(empty($params['duration'])) {
      throw new InvalidTOILRequestException(
        'The TOIL duration cannot be empty',
        'toil_request_duration_is_empty',
        'duration'
      );
    }

    if(empty($params['toil_to_accrue'])) {
      throw new InvalidTOILRequestException(
        'The TOIL amount cannot be empty',
        'toil_request_toil_to_accrue_is_empty',
        'toil_to_accrue'
      );
    }
  }

  /**
   * Validate that the TOIL amount is one of the values in the TOIL Amount option group.
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   */
  private static function validateTOILAmountIsValid($params) {
    $toilAmountOptions = self::toilAmountOptions();
    if (!in_array($params['toil_to_accrue'], $toilAmountOptions)) {
      throw new InvalidTOILRequestException(
        'The TOIL amount is not valid',
        'toil_request_toil_amount_is_invalid',
        'toil_to_accrue'
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
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   */
  private static function validateValidTOILAmountNotGreaterThanMaximum($params) {
    if(empty($params['contact_id']) || empty($params['type_id'])){
      return;
    }

    $absenceType = AbsenceType::findById($params['type_id']);
    $unlimitedAccrual = empty($absenceType->max_leave_accrual) && $absenceType->max_leave_accrual != 0;

    $currentPeriod = AbsencePeriod::getCurrentPeriod();
    $startDate = new DateTime($currentPeriod->start_date);
    $endDate = new DateTime($currentPeriod->end_date);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $leaveRequestStatusFilter = [
      $leaveRequestStatuses['Approved'],
      $leaveRequestStatuses['Admin Approved']
    ];

    $totalApprovedTOIL = LeaveBalanceChange::getTotalTOILBalanceChangeForContact(
      $params['contact_id'],
      $startDate,
      $endDate,
      $leaveRequestStatusFilter
    );
    $totalProjectedToilForPeriod = $totalApprovedTOIL + $params['toil_to_accrue'];

    if ($totalProjectedToilForPeriod > $absenceType->max_leave_accrual && !$unlimitedAccrual) {
      throw new InvalidTOILRequestException(
        'The TOIL amount plus all approved TOIL for current period is greater than the maximum for this Absence Type',
        'toil_request_toil_amount_more_than_maximum_for_absence_type',
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
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   */
  private static function validateValidTOILPastDaysRequest($params) {
    if(empty($params['type_id']) || empty($params['from_date']) || empty($params['to_date'])){
      return;
    }

    $absenceType = AbsenceType::findById($params['type_id']);
    $fromDate = new DateTime($params['from_date']);
    $toDate = new DateTime($params['to_date']);
    $todayDate = new DateTime('today');
    $leaveDatesHasPastDates = $fromDate < $todayDate || $toDate < $todayDate;

    if ($leaveDatesHasPastDates && !$absenceType->allow_accrue_in_the_past) {
      throw new InvalidTOILRequestException(
        "You cannot request TOIL for past days",
        'toil_request_toil_cannot_be_requested_for_past_days',
        'from_date'
      );
    }
  }

  /**
   * Validate that the user cannot request TOIL if the allow_accruals_request flag on
   * the absence type is false
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   */
  private static function validateAbsenceTypeAllowsAccrual($params) {
    if(empty($params['type_id'])){
      return;
    }

    $absenceType = AbsenceType::findById($params['type_id']);

    if (!$absenceType->allow_accruals_request) {
      throw new InvalidTOILRequestException(
        "This absence Type does not allow TOIL accrual",
        'toil_request_toil_accrual_not_allowed_for_absence_type',
        'type_id'
      );
    }
  }
  /**
   * Saves Balance Change for the TOIL Request
   *
   * @param CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *   The Leave Request created by this TOIL Request
   * @param float $toilToAccrue
   *   The amount of TOIL to be accrued.
   * @param \DateTime $expiryDate
   *   The date the LeaveBalanceChange will expire
   */
  private function saveBalanceChange(LeaveRequest $leaveRequest, $toilToAccrue, DateTime $expiryDate = null) {
    $this->deleteBalanceChange();

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id'));
    if($expiryDate === null) {
      $absenceType = AbsenceType::findById($leaveRequest->type_id);
      $expiryDate =  $absenceType->calculateToilExpiryDate(new DateTime());
    }

    LeaveBalanceChange::create([
      'type_id' => $balanceChangeTypes['Credit'],
      'amount' => $toilToAccrue,
      'expiry_date' => $expiryDate ? $expiryDate->format('Ymd') : null,
      'source_id' => $this->id,
      'source_type' => LeaveBalanceChange::SOURCE_TOIL_REQUEST
    ]);
  }

  /**
   * Delete Balance Change for this TOIL Request
   */
  private function deleteBalanceChange() {
    $dao = new LeaveBalanceChange();
    $dao->source_id = $this->id;
    $dao->source_type = LeaveBalanceChange::SOURCE_TOIL_REQUEST;
    $dao->delete();
  }

  /**
   * Returns the option group for the TOIL amounts in an array format.
   *
   * @return array
   */
  private static function toilAmountOptions() {
    $result = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'hrleaveandabsences_toil_amounts',
    ]);
    $toilAmounts = [];

    foreach ($result['values'] as $toilAmount) {
      $toilAmounts[$toilAmount['name']] = $toilAmount['value'];
    }
    return $toilAmounts;
  }

  /**
   * Deletes the TOIL Requests associated with an Absence Type (within the given Absence Period)
   * and all the LeaveRequests, LeaveBalanceChanges and LeaveRequestDates of the TOIL Requests.
   *
   * @param int $absenceTypeID
   *   The absence Type that TOIL requests is to be deleted for.
   * @param DateTime $startDate
   *   Records linked to LeaveRequests with from_date >= this date will be deleted
   * @param DateTime|null $endDate
   *   If this is present records linked to LeaveRequests with to_date <= this date will be deleted
   * @param boolean $nonExpiredOnly
   *   Whether to delete only records linked to non expired balance changes
   */
  public static function deleteAllForAbsenceType($absenceTypeID, DateTime $startDate, DateTime $endDate = null, $nonExpiredOnly = false) {
    $leaveBalanceChangeTable = LeaveBalanceChange::getTableName();
    $toilRequestTable = TOILRequest::getTableName();
    $leaveRequestTable = LeaveRequest::getTableName();
    $leaveRequestDateTable = LeaveRequestDate::getTableName();

    $query = "DELETE tr, bc, lr, lrd FROM {$toilRequestTable} tr
              INNER JOIN {$leaveBalanceChangeTable} bc ON bc.source_id = tr.id AND bc.source_type = %1
              INNER JOIN {$leaveRequestTable} lr ON tr.leave_request_id = lr.id
              INNER JOIN {$leaveRequestDateTable} lrd ON lr.id = lrd.leave_request_id
              WHERE lr.type_id = %2
              AND lr.from_date >= %3
              ";

    $params = [
      1 => [LeaveBalanceChange::SOURCE_TOIL_REQUEST, 'String'],
      2 => [$absenceTypeID, 'Integer'],
      3 => [$startDate->format('Y-m-d'), 'String'],
    ];

    if ($endDate) {
      $query .= " AND lr.to_date <= %5";
      $params[5] = [$endDate->format('Y-m-d'), 'String'];
    }

    if ($nonExpiredOnly) {
      $query .= " AND bc.expiry_date > %4";
      $params[4] = [date('Y-m-d'), 'String'];
    }

    CRM_Core_DAO::executeQuery($query, $params);
  }
}
