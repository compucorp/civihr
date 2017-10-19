<?php

use CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException as InvalidWorkDayException;

class CRM_HRLeaveAndAbsences_BAO_WorkDay extends CRM_HRLeaveAndAbsences_DAO_WorkDay {

  /**
   * @var array|null
   *   Caches the list of option values for the type field.
   */
  private static $workDayTypes;

  /**
   * Create a new WorkDay based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_WorkDay|NULL
   *
   */
  public static function create($params) {
    $entityName = 'WorkDay';
    $hook = empty($params['id']) ? 'create' : 'edit';
    self::validateParams($params);

    if(!empty($params['id'])) {
      unset($params['week_id']);
    }

    $params['number_of_hours'] = null;
    if($params['type'] == self::getWorkingDayTypeValue()) {
      $params['number_of_hours'] = self::calculateNumberOfHours(
          $params['time_from'],
          $params['time_to'],
          $params['break']
      );
    }

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Returns the value for the option value, of the Work Day Type option group,
   * with the given name.
   *
   * @param string $optionName
   *
   * @return string
   */
  private static function getTypeValue($optionName) {
    if(empty(self::$workDayTypes)) {
      self::$workDayTypes = array_flip(self::buildOptions('type', 'validate'));

      // The option value values are stored as strings on the database, so we
      // need to make sure the values will always be returned as strings, even
      // if they're numeric.
      array_walk(self::$workDayTypes, function(&$type) {
        $type = (string)$type;
      });
    }

    return empty(self::$workDayTypes[$optionName]) ? null : self::$workDayTypes[$optionName];
  }

  /**
   * Returns the value of the option value for the "Working Day" Work Day type
   *
   * @return string
   */
  public static function getWorkingDayTypeValue() {
    return self::getTypeValue('working_day');
  }

  /**
   * Returns the value of the option value for the "Non-Working Day" Work Day type
   *
   * @return string
   */
  public static function getNonWorkingDayTypeValue() {
    return self::getTypeValue('non_working_day');
  }

  /**
   * Returns the value of the option value for the "Weekend" Work Day type
   *
   * @return string
   */
  public static function getWeekendTypeValue() {
    return self::getTypeValue('weekend');
  }

  /**
   * Validates the $params array passed to the create method
   *
   * @param array $params
   *   The array passed to the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   */
  private static function validateParams($params) {
    self::validateWorkDayType($params);
    self::validateDayOfTheWeek($params);
    self::validateWorkHours($params);
  }

  /**
   * Validates if the day of the week in $params is valid according to ISO-8601
   *
   * @param array $params
   *   The array passed to the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   */
  private static function validateDayOfTheWeek($params) {
    $dayOfTheWeek = empty($params['day_of_the_week']) ? null : $params['day_of_the_week'];

    if(!is_int($dayOfTheWeek) || ($dayOfTheWeek < 1 || $dayOfTheWeek > 7)) {
      throw new InvalidWorkDayException(
        'Day of the Week should be a number between 1 and 7, according to ISO-8601'
      );
    }
  }

  /**
   * Validates the hours of a work day:
   * - If type is Working Day, then hours are required
   * - From time should not be greater than to time
   * - Break cannot be more than the number of hours between from and to time
   * - Hours should be in a HH:mm format
   *
   * @param array $params
   *   The array passed to the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   */
  private static function validateWorkHours($params) {
    $typeOfDay = empty($params['type']) ? null : $params['type'];
    $timeFrom = empty($params['time_from']) ? null : $params['time_from'];
    $timeTo = empty($params['time_to']) ? null : $params['time_to'];
    $break = empty($params['break']) ? null : $params['break'];

    $hasTimesOrBreak = $timeFrom || $timeTo || $break;
    $isWorkingDay = $typeOfDay == self::getWorkingDayTypeValue();
    if(!$isWorkingDay && $hasTimesOrBreak) {
      throw new InvalidWorkDayException(
        'Time From, Time To and Break should be empty for Non Working Days and Weekends'
      );
    }

    if($timeFrom && !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $timeFrom)) {
      throw new InvalidWorkDayException(
        'Time From format should be hh:mm'
      );
    }

    if($timeTo && !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $timeTo)) {
      throw new InvalidWorkDayException(
        'Time To format should be hh:mm'
      );
    }

    $hasTimesAndBreak = $timeFrom && $timeTo && $break;
    if($isWorkingDay && !$hasTimesAndBreak) {
      throw new InvalidWorkDayException(
        'Time From, Time To and Break are required for Working Days'
      );
    }

    $hasTimes = !is_null($timeFrom) && !is_null($timeTo);
    if($hasTimes && (strtotime($timeFrom) >= strtotime($timeTo))) {
      throw new InvalidWorkDayException(
          'Time From should be less than Time To'
      );
    }

    $secondsInWorkingHours = strtotime($timeTo) - strtotime($timeFrom);
    $secondsInBreak = $break * 3600;
    if($hasTimes && ($secondsInBreak >= $secondsInWorkingHours)) {
      throw new InvalidWorkDayException(
          'Break should be less than the number of hours between Time From and Time To'
      );
    }
  }

  /**
   * Validates if the the type of the given work day is one of the values of the
   * option values in the Work Day Type option group
   *
   * @param array $params
   *   The array passed to the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   */
  private static function validateWorkDayType($params) {
    $type = empty($params['type']) ? null : $params['type'];
    $validTypes = array_keys(self::buildOptions('type'));

    if(!in_array($type, $validTypes)) {
      throw new InvalidWorkDayException(
        'Invalid Work Day Type'
        //'Invalid Work Day Type: ' . $type . 'The valid types are: '.implode(', ', $validTypes)
      );
    }
  }

  /**
   * Calculate the number of hours to work on this work day, based on the time
   * from, time to and break
   *
   * @param string $timeFrom
   *   The time from hour, in HH:mm format
   * @param string $timeTo
   *   The time to hour, in HH:mm format
   * @param float $break
   *   The amount of break hours
   *
   * @return float
   */
  private static function calculateNumberOfHours($timeFrom, $timeTo, $break) {
    $timeFromInHours = strtotime($timeFrom) / 3600;
    $timeToInHours = strtotime($timeTo) / 3600;
    $numberOfHours = $timeToInHours - $timeFromInHours - $break;

    return $numberOfHours;
  }
}
