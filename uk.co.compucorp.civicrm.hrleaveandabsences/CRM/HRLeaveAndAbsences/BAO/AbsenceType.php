<?php

class CRM_HRLeaveAndAbsences_BAO_AbsenceType extends CRM_HRLeaveAndAbsences_DAO_AbsenceType {

  const EXPIRATION_UNIT_DAYS = 1;
  const EXPIRATION_UNIT_MONTHS = 2;
  const EXPIRATION_UNIT_YEARS = 3;

  const REQUEST_CANCELATION_NO = 1;
  const REQUEST_CANCELATION_ALWAYS = 2;
  const REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE = 3;

  /**
   * Create a new AbsenceType based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_AbsenceType|NULL
   **/
  public static function create($params) {
    $className = 'CRM_HRLeaveAndAbsences_DAO_AbsenceType';
    $entityName = 'AbsenceType';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    self::validateParams($params);
    if(isset($params['is_default']) && $params['is_default']) {
      self::unsetDefaultTypes();
    }

    if(empty($params['id'])) {
      $params['weight'] = self::getMaxWeight() + 1;
    }

    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  public static function getRequestCancelationOptions() {
     return [
         self::REQUEST_CANCELATION_NO                       => ts('No'),
         self::REQUEST_CANCELATION_ALWAYS                   => ts('Yes - always'),
         self::REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE => ts('Yes - in advance of the start date of the leave')
     ];
  }

  public static function getExpirationUnitOptions() {
    return [
        self::EXPIRATION_UNIT_DAYS   => ts('Days'),
        self::EXPIRATION_UNIT_MONTHS => ts('Months'),
        self::EXPIRATION_UNIT_YEARS  => ts('Years')
    ];
  }

  public static function getDefaultValues($id) {
    $absenceType = civicrm_api3('AbsenceType', 'get', array('id' => $id));
    return $absenceType['values'][$id];
  }

  private static function unsetDefaultTypes() {
    $tableName = self::getTableName();
    $query = "UPDATE {$tableName} SET is_default = 0 WHERE is_default = 1";
    CRM_Core_DAO::executeQuery($query);
  }

  /**
   * @param $params The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   */
  private static function validateParams($params) {
    if(!empty($params['add_public_holiday_to_entitlement'])) {
      self::validateAddPublicHolidayToEntitlement();
    }

    if (!empty($params['allow_request_cancelation']) &&
        !array_key_exists($params['allow_request_cancelation'], self::getRequestCancelationOptions())
    ) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'Invalid Request Cancelation Option'
      );
    }

    self::validateTOIL($params);
    self::validateCarryForward($params);
  }

  private static function validateAddPublicHolidayToEntitlement() {
    $result = civicrm_api3('AbsenceType', 'getcount', array(
        'sequential' => 1,
        'add_public_holiday_to_entitlement' => 1,
    ));
    if(!isset($result['result']) || $result['result'] > 0) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'There is already one Absence Type where "Must staff take public holiday as leave" is selected'
      );
    }
  }

  /**
   * @param $params The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   */
  private static function validateTOIL($params) {
    $allow_accruals_request = !empty($params['allow_accruals_request']);
    if(!empty($params['max_leave_accrual']) && !$allow_accruals_request) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'To set maximum amount of leave that can be accrued you must allow staff to accrue additional days'
      );
    }

    if(!empty($params['allow_accrue_in_the_past']) && !$allow_accruals_request) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'To allow accrue in the past you must allow staff to accrue additional days'
      );
    }

    $has_accrual_expiration_duration = !empty($params['accrual_expiration_duration']);
    $has_accrual_expiration_unit = !empty($params['accrual_expiration_unit']);
    if($has_accrual_expiration_duration && $has_accrual_expiration_unit && !$allow_accruals_request) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'To set the accrual expiry duration you must allow staff to accrue additional days'
      );
    }

    if ($has_accrual_expiration_unit &&
        !array_key_exists($params['accrual_expiration_unit'], self::getExpirationUnitOptions())
    ) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'Invalid Accrual Expiration Unit'
      );
    }

    if ($has_accrual_expiration_duration xor $has_accrual_expiration_unit) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'Invalid Accrual Expiration. It should have both Unit and Duration'
      );
    }
  }

  /**
   * @param $params The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   */
  private static function validateCarryForward($params) {
    $allow_carry_forward = !empty($params['allow_carry_forward']);
    if(!empty($params['max_number_of_days_to_carry_forward']) && !$allow_carry_forward) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'To set the Max Number of Days to Carry Forward you must allow Carry Forward'
      );
    }

    $has_carry_forward_expiration_day = !empty($params['carry_forward_expiration_day']);
    $has_carry_forward_expiration_month = !empty($params['carry_forward_expiration_month']);
    $has_carry_forward_expiration_date = $has_carry_forward_expiration_day && $has_carry_forward_expiration_month;
    if($has_carry_forward_expiration_date && !$allow_carry_forward) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'To set the Carry Forward Expiration Date you must allow Carry Forward'
      );
    }

    $has_carry_forward_expiration_duration = !empty($params['carry_forward_expiration_duration']);
    $has_carry_forward_expiration_unit = !empty($params['carry_forward_expiration_unit']);
    if($has_carry_forward_expiration_duration && $has_carry_forward_expiration_unit && !$allow_carry_forward) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'To set the carry forward expiry duration you must allow Carry Forward'
      );
    }

    $has_carry_forward_expiration_duration_or_unit = $has_carry_forward_expiration_unit || $has_carry_forward_expiration_duration;
    if($has_carry_forward_expiration_date && $has_carry_forward_expiration_duration_or_unit) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          "You can't set both the Carry Forward Expiration Date and Period"
      );
    }

    if($has_carry_forward_expiration_unit xor $has_carry_forward_expiration_duration) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'Invalid Carry Forward Expiration. It should have both Unit and Duration'
      );
    }

    if ($has_carry_forward_expiration_date &&
        !self::isValidDateAndMonth($params['carry_forward_expiration_day'], $params['carry_forward_expiration_month'])
    ) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'Invalid Carry Forward Expiration Date'
      );
    }

    if (!empty($params['carry_forward_expiration_unit']) &&
        !array_key_exists($params['carry_forward_expiration_unit'], self::getExpirationUnitOptions())
    ) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'Invalid Carry Forward Expiration Unit'
      );
    }
  }

  /**
   * Checks if a date in dd-mm format is valid.
   *
   * @TODO Find a better place to put this method.
   *
   */
  private static function isValidDateAndMonth($day, $month) {
    if($month < 1 || $month > 12) {
      return false;
    }

    if($month == 2 && $day > 29) {
      return false;
    }

    if(in_array($month, [4, 6, 9, 11]) && $day > 30) {
      return false;
    }

    return true;
  }

  /**
   * Gets the maximum weight of all leave/absence types
   *
   * Returns 0 if there's no type available
   *
   * @return int the maximu weight
   */
  private static function getMaxWeight() {
    $tableName = self::getTableName();
    $query = "SELECT MAX(weight) as max_weight FROM {$tableName}";
    $dao = CRM_Core_DAO::executeQuery($query);
    if($dao->fetch()) {
      return $dao->max_weight;
    }

    return 0;
  }
}
