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
   * Delete a PublicHoliday with given ID.
   *
   * @param int $id
   */
  public static function del($id) {
    $publicHoliday = new CRM_HRLeaveAndAbsences_DAO_PublicHoliday();
    $publicHoliday->id = $id;
    $publicHoliday->find(true);
    $publicHoliday->delete();
  }

  /**
   * Return an array containing properties of Public Holiday with given ID.
   *
   * @param int $id
   * @return array|NULL
   */
  public static function getValuesArray($id) {
    $result = civicrm_api3('PublicHoliday', 'get', array('id' => $id));
    return !empty($result['values'][$id]) ? $result['values'][$id] : null;
  }

  /**
   * Validates all the params passed to the create method
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException
   */
  private static function validateParams($params) {
    if(empty($params['title']) && empty($params['id'])) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException(
        'Title value is required'
      );
    }
    self::validateDate($params);
    self::checkIfDateIsUnique($params);
  }

  /**
   * If there is no date specified but id exists then we skip the date validation.
   * Otherwise a date cannot be empty and must be a real date.
   *
   * @param array $params
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException
   * @return bool
   */
  private static function validateDate($params) {
    // Skip date validation if we are editing an exsisting record and no new date is specified.
    if (!isset($params['date']) && !empty($params['id'])) {
      return true;
    }
    if (empty($params['date'])) {
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

  /**
   * Check if there is no Public Holiday already existing with provided date.
   *
   * @param array $params
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException
   */
  private static function checkIfDateIsUnique($params) {
    // Skip date validation if we are editing an exsisting record and no new date is specified.
    if (!isset($params['date']) && !empty($params['id'])) {
      return true;
    }
    // Check for Public Holiday already existing with given date.
    $duplicateDateParams = array(
      'date' => $params['date'],
    );
    if (!empty($params['id'])) {
      $duplicateDateParams['id'] = array('!=' => $params['id']);
    }
    $duplicateDate = civicrm_api3('PublicHoliday', 'getcount', $duplicateDateParams);
    if ($duplicateDate) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException(
        'There is a Public Holiday already existing with given date'
      );
    }
  }

  /**
   * Returns the number of active Public Holidays between the given
   * start and end dates (inclusive)
   *
   * @param string $startDate The start date of the period
   * @param string $endDate The end date of the period
   * @param bool $excludeWeekends When true it will not count Public Holidays that fall on a weekend. It's false by default
   *
   * @return int The Number of Public Holidays for the given Period
   */
  public static function getNumberOfPublicHolidaysForPeriod($startDate, $endDate, $excludeWeekends = false) {
    $startDate = CRM_Utils_Date::processDate($startDate, null, false, 'Ymd');
    $endDate = CRM_Utils_Date::processDate($endDate, null, false, 'Ymd');

    $tableName = self::getTableName();
    $query = "
      SELECT COUNT(*) as public_holidays
      FROM {$tableName}
      WHERE date >= %1 AND date <= %2 AND is_active = 1
    ";

    if($excludeWeekends) {
      $query .= ' AND DAYOFWEEK(date) BETWEEN 2 AND 6';
    }

    $queryParams = [
      1 => [$startDate, 'Date'],
      2 => [$endDate, 'Date'],
    ];
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    $dao->fetch(true);

    return (int)$dao->public_holidays;
  }

  /**
   * Returns the number of Public Holidays in the Current Period
   *
   * @param bool $excludeWeekends
   *  If true, public holidays that falls on a weekend won't be counted. Default is false
   *
   * @return int
   */
  public static function getNumberOfPublicHolidaysForCurrentPeriod($excludeWeekends = false) {
    $currentPeriod = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::getCurrentPeriod();

    if(!$currentPeriod) {
      return 0;
    }

    return self::getNumberOfPublicHolidaysForPeriod(
      $currentPeriod->start_date,
      $currentPeriod->end_date,
      $excludeWeekends
    );
  }

  /**
   * This method returns s list of active PublicHoliday instances between the
   * given start and end dates (inclusive)
   *
   * @param string
   *    $startDate The start date of the period
   * @param string
   *    $endDate The end date of the period
   * @param bool $excludeWeekends
   *    When true it will not include Public Holidays that fall on a weekend. It's false by default
   *
   * @return CRM_HRLeaveAndAbsences_BAO_PublicHoliday[]
   */
  public static function getPublicHolidaysForPeriod($startDate, $endDate, $excludeWeekends = false) {
    $startDate = CRM_Utils_Date::processDate($startDate, null, false, 'Ymd');
    $endDate = CRM_Utils_Date::processDate($endDate, null, false, 'Ymd');

    $tableName = self::getTableName();

    $where = 'date >= %1 AND date <= %2 AND is_active = 1';

    // Weekends are Saturday and Sunday
    // So, to exclude them we return only the public holidays
    // between monday (2) and friday (6)
    if($excludeWeekends) {
      $where .= ' AND DAYOFWEEK(date) BETWEEN 2 AND 6';
    }

    $query = "
      SELECT *
      FROM {$tableName}
      WHERE {$where}
      ORDER BY date ASC
    ";

    $queryParams = [
      1 => [$startDate, 'Date'],
      2 => [$endDate, 'Date'],
    ];
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams, true, self::class);

    $publicHolidays = [];
    while($dao->fetch(true)) {
      $publicHolidays[] = clone $dao;
    }

    return $publicHolidays;
  }
}
