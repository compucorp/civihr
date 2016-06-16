<?php

class CRM_HRLeaveAndAbsences_BAO_AbsenceType extends CRM_HRLeaveAndAbsences_DAO_AbsenceType {

  const EXPIRATION_UNIT_DAYS = 1;
  const EXPIRATION_UNIT_MONTHS = 2;
  const EXPIRATION_UNIT_YEARS = 3;

  const REQUEST_CANCELATION_NO = 1;
  const REQUEST_CANCELATION_ALWAYS = 2;
  const REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE = 3;

  /**
   * The list of colors that can be selected for an AbsenceType
   * @var array
   */
  private static $allColors = [
      '#5A6779',
      '#3D4A5E',
      '#263345',
      '#151D2C',
      '#E5807F',
      '#E56A6A',
      '#CC4A49',
      '#B32E2E',
      '#ECA67F',
      '#FA8F55',
      '#D97038',
      '#BF561D',
      '#8EC68A',
      '#6DAD68',
      '#4F944A',
      '#377A31',
      '#C096AA',
      '#B37995',
      '#995978',
      '#803D5E',
      '#9579A8',
      '#84619C',
      '#5F3D76',
      '#47275C',
      '#42B0CB',
      '#2997B3',
      '#147E99',
      '#056780',
  ];

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

    $params['color'] = strtoupper($params['color']);

    unset($params['is_reserved']);

    $instance = new $className();
    $instance->copyValues($params);
    $transaction = new CRM_Core_Transaction();
    $instance->save();

    if(array_key_exists('notification_receivers_ids', $params)) {
      self::saveNotificationReceivers($instance->id, $params['notification_receivers_ids']);
    }

    $transaction->commit();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Deletes the AbsenceType with the given ID.
   *
   * A reserved AbsenceType cannot be deleted. If the given ID is from a
   * reserved type, and exception will be thrown.
   *
   * @param int $id The ID of the AbsenceType to be deleted
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_OperationNotAllowedException
   */
  public static function del($id)
  {
    $absenceType = new CRM_HRLeaveAndAbsences_DAO_AbsenceType();
    $absenceType->id = $id;
    $absenceType->find(true);

    if($absenceType->is_reserved) {
      throw new CRM_HRLeaveAndAbsences_Exception_OperationNotAllowedException('Reserved types cannot be deleted!');
    }

    $absenceType->delete();
  }

  /**
   * Returns all the options available to the Allow Request Cancelation dropdown
   *
   * @return array
   */
  public static function getRequestCancelationOptions() {
     return [
         self::REQUEST_CANCELATION_NO                       => ts('No'),
         self::REQUEST_CANCELATION_ALWAYS                   => ts('Yes - always'),
         self::REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE => ts('Yes - in advance of the start date of the leave')
     ];
  }

  /**
   * Returns a list of options available to the unit dropdown of the carry
   * forward and TOIL expiration.
   *
   * @return array
   */
  public static function getExpirationUnitOptions() {
    return [
        self::EXPIRATION_UNIT_DAYS   => ts('Days'),
        self::EXPIRATION_UNIT_MONTHS => ts('Months'),
        self::EXPIRATION_UNIT_YEARS  => ts('Years')
    ];
  }

  /**
   * Returns a list of colors that are available to be selected for an
   * AbsenceType.
   *
   * First it will return only the colors that haven't been used yet. When all
   * the colors have been used once, it will return all the colors.
   *
   * @return array
   */
  public static function getAvailableColors() {
    $colorsInUse = self::getColorsInUse();
    if(count(self::$allColors) == count($colorsInUse)) {
      return self::$allColors;
    }

    $availableColors = [];
    foreach(self::$allColors as $color) {
      if(!in_array($color, $colorsInUse)) {
        $availableColors[] = $color;
      }
    }

    return $availableColors;
  }

  /**
   * Returns an array containing all the fields values for the
   * AbsenceType with the given ID.
   *
   * This method is mainly used by the AbsenceType form, so it
   * can get the data to fill its fields.
   *
   * An empty array is returned if it is not possible to load
   * the data.
   *
   * @param int $id The id of the AbsenceType to retrieve the values
   *
   * @return array An array containing the values
   */
  public static function getValuesArray($id) {
    $result = civicrm_api3('AbsenceType', 'get', array('id' => $id));
    $absenceType = $result['values'][$id];
    $absenceType['notification_receivers_ids'] = self::getNotificationReceiversIDs($id);
    return $absenceType;
  }

  /**
   * Unset the is_default flag for every AbsenceType that has it
   */
  private static function unsetDefaultTypes() {
    $tableName = self::getTableName();
    $query = "UPDATE {$tableName} SET is_default = 0 WHERE is_default = 1";
    CRM_Core_DAO::executeQuery($query);
  }

  /**
   * @param array $params The params array received by the create method
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

  /**
   * Validates the add_public_holiday_to_entitlement field.
   *
   * There can be only one AbsenceType where this field is true. So this
   * method checks if one such type already exists and throws an error if that
   * is the case.
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @throws \CiviCRM_API3_Exception
   */
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
   * Validates the TOIL fields
   *
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
   * Validates the Carry Forward fields
   *
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

    $has_carry_forward_expiration_duration = !empty($params['carry_forward_expiration_duration']);
    $has_carry_forward_expiration_unit = !empty($params['carry_forward_expiration_unit']);
    if($has_carry_forward_expiration_duration && $has_carry_forward_expiration_unit && !$allow_carry_forward) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'To set the carry forward expiry duration you must allow Carry Forward'
      );
    }

    if($has_carry_forward_expiration_unit xor $has_carry_forward_expiration_duration) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException(
          'Invalid Carry Forward Expiration. It should have both Unit and Duration'
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


  /**
   * Gets a list of all the colors in AbsenceType::allColors
   * that have already been used in leave/absence types.
   *
   * @return array The list of colors already used
   */
  private static function getColorsInUse()
  {
    $colors = [];
    $tableName = self::getTableName();
    $query = "SELECT DISTINCT(color) as color FROM {$tableName}";
    $dao = CRM_Core_DAO::executeQuery($query);
    while($dao->fetch()) {
      $colors[] = $dao->color;
    }

    return $colors;
  }

  /**
   * Adds a list of notification receivers (contacts) to an Absence Type.
   *
   * @param int $typeId the ID of the type to add the notification receivers to
   * @param array $contactsIds the IDs of the contacts to be added as notification receivers
   */
  private static function saveNotificationReceivers($typeId, $contactsIds) {
    CRM_HRLeaveAndAbsences_BAO_NotificationReceiver::removeReceiversFromAbsenceType($typeId);
    if(!empty($contactsIds)) {
      CRM_HRLeaveAndAbsences_BAO_NotificationReceiver::addReceiversToAbsenceType($typeId, $contactsIds);
    }
  }

  /**
   * Returns a list of the Notification Receivers IDs for an Absence Type.
   *
   * @param int $typeId the ID of the type to get the notification receivers
   *
   * @return array the IDs of the notification receivers for the Absence Type
   */
  private static function getNotificationReceiversIDs($typeId) {
    return CRM_HRLeaveAndAbsences_BAO_NotificationReceiver::getReceiversIDsForAbsenceType($typeId);
  }

}
