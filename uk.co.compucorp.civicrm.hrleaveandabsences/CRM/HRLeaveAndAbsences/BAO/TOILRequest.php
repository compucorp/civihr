<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException as InvalidTOILRequestException;

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
    if(!empty($params['to_date'])){
      $params['to_date_type'] = $dateTypeOptions['All Day'];
    }

    if ($hook == 'edit') {
      $instance->id = $params['id'];
      $instance->find(true);

      if ($instance->leave_request_id) {
        $instance->copyValues($params);
        $params['id'] = $instance->leave_request_id;
        $leaveRequest = LeaveRequest::create($params, $validate);
        $instance->save();
      }
    }

    if ($hook == 'create') {
      $leaveRequest = LeaveRequest::create($params, $validate);
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
    self::validateValidTOILAmountNotGreaterThanMaximum($params);
    self::validateValidTOILPastDaysRequest($params);
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
   * Validate that the TOIL amount is not greater than the maximum defined(if any) for
   * the Absence Type
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   */
  private static function validateValidTOILAmountNotGreaterThanMaximum($params) {
    $absenceType = AbsenceType::findById($params['type_id']);
    $unlimitedAccrual = empty($absenceType->max_leave_accrual) && $absenceType->max_leave_accrual != 0;
    if ($params['toil_to_accrue'] > $absenceType->max_leave_accrual && !$unlimitedAccrual) {
      throw new InvalidTOILRequestException(
        'The TOIL amount requested for is greater than the maximum for this Absence Type',
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
    $absenceType = AbsenceType::findById($params['type_id']);
    $fromDate = new DateTime($params['from_date']);
    if (!empty($params['to_date'])) {
      $toDate = new DateTime($params['to_date']);
    }
    else{
      $toDate = clone $fromDate;
    }
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
}
