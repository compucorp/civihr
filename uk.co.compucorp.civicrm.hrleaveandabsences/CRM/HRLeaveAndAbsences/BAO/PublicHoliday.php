<?php

class CRM_HRLeaveAndAbsences_BAO_PublicHoliday extends CRM_HRLeaveAndAbsences_DAO_PublicHoliday {

  /**
   * Create a new PublicHoliday based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_PublicHoliday|NULL
   **/
  public static function create($params) {
    $className = 'CRM_HRLeaveAndAbsences_DAO_PublicHoliday';
    $entityName = 'PublicHoliday';
    $hook = empty($params['id']) ? 'create' : 'edit';

    self::validateParams($params);

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $transaction = new CRM_Core_Transaction();
    $instance->save();
    $transaction->commit();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Validates all the params passed to the create method
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException
   */
  private static function validateParams($params)
  {
    if(empty($params['title'])) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException(
        'Title value is required'
      );
    }
    self::validateDate($params);
  }

  /**
   * Checks if date value in the $params array is valid.
   *
   * A date cannot be empty and must be a real date.
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException
   */
  private static function validateDate($params)
  {
    if(empty($params['date'])) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException(
        'Date value is required'
      );
    }

    $dateIsValid = CRM_HRLeaveAndAbsences_Validator_Date::isValid($params['date']);
    if(!$dateIsValid) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException(
        'Date value should be valid'
      );
    }
  }

}
