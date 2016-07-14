<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

class CRM_HRLeaveAndAbsences_BAO_AbsencePeriod extends CRM_HRLeaveAndAbsences_DAO_AbsencePeriod {

  /**
   * The number of working days in this AbsencePeriod.
   *
   * This is used to cache the result of getNumberOfWorkingDays().
   *
   * @var int
   */
  private $numberOfWorkingDays = 0;

  /**
   * Variable to cache the return from the getPreviousPeriod method
   *
   * If false, it means the period was never loaded. If null, it means there's
   * no previous period.
   *
   * @var \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   */
  private $previousPeriod = false;

  /**
   * Create a new AbsencePeriod based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_AbsencePeriod|NULL
   */
  public static function create($params) {
    $entityName = 'AbsencePeriod';
    $hook = empty($params['id']) ? 'create' : 'edit';

    self::validateParams($params);

    if(empty($params['weight'])) {
      $params['weight'] = self::getMaxWeight() + 1;
    }

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);

    $transaction = new CRM_Core_Transaction();
    $instance->save();
    if(!$instance->id) {
      $transaction->rollback();
    }
    self::updatePeriodsOrder($instance);
    $transaction->commit();

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Validates all the params passed to the create method
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException
   */
  private static function validateParams($params)
  {
    self::validateDates($params);

    if(self::overlapsWithAnotherPeriod($params)) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException(
        'This Absence Period overlaps with another existing Period'
      );
    }
  }

  /**
   * Checks if start_date and end_date values in the $params array are valid.
   *
   * A date cannot be empty, must be a real date and start_date can't be greater
   * than end_date.
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException
   */
  private static function validateDates($params)
  {
    if(empty($params['start_date']) || empty($params['end_date'])) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException(
        'Both the start and end dates are required'
      );
    }

    $startDateIsValid = CRM_HRLeaveAndAbsences_Validator_Date::isValid($params['start_date']);
    $endDateIsValid = CRM_HRLeaveAndAbsences_Validator_Date::isValid($params['end_date']);
    if(!$startDateIsValid || !$endDateIsValid) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException(
        'Both the start and end dates should be valid'
      );
    }

    $startDate = strtotime($params['start_date']);
    $endDate = strtotime($params['end_date']);
    if($startDate >= $endDate) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException(
        'Start Date should be less than End Date'
      );
    }
  }

  /**
   * Checks if there's an existing Absence Period that overlaps with the
   * start and end dates on the given params array.
   *
   * @param array $params - The params array passed to the create method
   *
   * @return bool
   */
  private static function overlapsWithAnotherPeriod($params)
  {
    $tableName = self::getTableName();
    $query = "
      SELECT COUNT(*) as overlaping_periods
      FROM {$tableName}
      WHERE (start_date <= %1) AND (end_date >= %2)
    ";
    $queryParams = [
      1 => [$params['end_date'], 'String'],
      2 => [$params['start_date'], 'String'],
    ];

    if(!empty($params['id'])) {
      $query .= ' AND (id != %3)';
      $queryParams[3] = [$params['id'], 'Integer'];
    }

    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    $dao->fetch();
    return $dao->overlaping_periods > 0;
  }

  /**
   * This method is called after saving an entity and it makes sure that there
   * will only one period for each weight.
   *
   * It checks if there's another period with the same weight and, if positive,
   * increase the weight of every period that has an equal or greater weight.
   *
   * @param CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $instance - The just saved AbsencePeriod
   */
  private static function updatePeriodsOrder($instance)
  {
    if(self::theresAnotherPeriodWithTheSameWeight($instance)) {
      self::increaseWeightsEqualOrGreaterTo($instance);
    }
  }

  /**
   * Checks if there's another period with the same weight of the given AbsencePeriod
   *
   * @param CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $instance - An AbsencePeriod
   *
   * @return bool
   */
  private static function theresAnotherPeriodWithTheSameWeight($instance)
  {
    $tableName = self::getTableName();
    $query = "
      SELECT COUNT(*) as periods
      FROM {$tableName}
      WHERE weight = %1 AND id != %2
    ";
    $queryParams = [
      1 => [$instance->weight, 'Integer'],
      2 => [$instance->id, 'Integer'],
    ];

    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    $dao->fetch();
    return $dao->periods > 0;
  }

  /**
   * Increases the weight of every existing period with a weight equal to or
   * greater than the weight of the given AbsencePeriod
   *
   * @param CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $instance - An AbsencePeriod
   */
  private static function increaseWeightsEqualOrGreaterTo($instance)
  {
    $tableName = self::getTableName();
    $query = "
      UPDATE {$tableName}
      SET weight = weight + 1
      WHERE weight >= %1 AND id != %2
    ";
    $queryParams = [
      1 => [$instance->weight, 'Integer'],
      2 => [$instance->id, 'Integer'],
    ];

    CRM_Core_DAO::executeQuery($query, $queryParams);
  }

  /**
   * Gets the maximum weight of all Absence Periods
   *
   * Returns 0 if there's no Period available
   *
   * @return int the maximum weight
   */
  public static function getMaxWeight() {
    $tableName = self::getTableName();
    $query = "SELECT MAX(weight) as max_weight FROM {$tableName}";
    $dao = CRM_Core_DAO::executeQuery($query);
    if($dao->fetch()) {
      return $dao->max_weight;
    }

    return 0;
  }

  /**
   * Returns an array containing all the fields values for the
   * AbsencePeriod with the given ID.
   *
   * This method is mainly used by the AbsencePeriod form, so it
   * can get the data to fill its fields.
   *
   * An empty array is returned if it is not possible to load
   * the data.
   *
   * @param int $id The id of the AbsencePeriod to retrieve the values
   *
   * @return array An array containing the values
   */
  public static function getValuesArray($id) {
    try {
      $result = civicrm_api3('AbsencePeriod', 'getsingle', ['id' => $id]);
      return $result;
    } catch (CiviCRM_API3_Exception $ex) {
      return [];
    }
  }

  /**
   * This method returns the most recent date that can be used as a Start Date.
   *
   * The returned date is maximum End Date of all existing Absence Period + 1 day.
   *
   * If there's no existing Absence Period, the current date is returned
   *
   * @return string The most recent start date available in Y-m-d format
   */
  public static function getMostRecentStartDateAvailable()
  {
    $tableName = self::getTableName();
    $query = "SELECT MAX(end_date) as latest_end_date FROM {$tableName}";
    $dao = CRM_Core_DAO::executeQuery($query);
    if($dao->fetch() && $dao->latest_end_date) {
      $latestEndDate = new DateTime($dao->latest_end_date);
      $latestEndDate->add(new DateInterval('P1D'));
      return $latestEndDate->format('Y-m-d');
    }

    return date('Y-m-d');
  }

  /**
   * Returns the current AbsencePeriod. That is, the period that contains the
   * current date. If no such period is found, null will be returned.
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod|null
   */
  public static function getCurrentPeriod()
  {
    $period = new self();
    $period->whereAdd("start_date <= CURDATE()");
    $period->whereAdd("end_date >= CURDATE()");
    $period->limit(1);
    if($period->find(true)) {
      return $period;
    }

    return null;
  }

  /**
   * Returns the number of working days for this Absence Period.
   *
   * The number is given by "number of ways in period" - "weekends" - "public holidays".
   *
   * If a public holiday falls on a weekend, we only count one day.
   *
   * @return int
   */
  public function getNumberOfWorkingDays()
  {
    if(!$this->numberOfWorkingDays) {
      if(!CRM_HRLeaveAndAbsences_Validator_Date::isValid($this->start_date, 'Y-m-d')) {
        throw new UnexpectedValueException('You can only get the number of working days for an AbsencePeriod with a valid start date');
      }

      if(!CRM_HRLeaveAndAbsences_Validator_Date::isValid($this->end_date, 'Y-m-d')) {
        throw new UnexpectedValueException('You can only get the number of working days for an AbsencePeriod with a valid end date');
      }

      $startDate = new DateTime($this->start_date);
      $endDate = new DateTime($this->end_date);
      $oneDayInterval = new DateInterval('P1D');

      // DatePeriod doesn't include the end date,
      // so we add one more day for it to be included
      $endDate->add($oneDayInterval);

      $numberOfWorkingDays = 0;
      $period = new DatePeriod($startDate, $oneDayInterval, $endDate);
      foreach($period as $date) {
        $dayOfTheWeek = $date->format('N');
        $dayIsWorkingDay = $dayOfTheWeek > 0 && $dayOfTheWeek < 6;
        if($dayIsWorkingDay) {
          $numberOfWorkingDays++;
        }
      }

      $numberOfPublicHolidays = CRM_HRLeaveAndAbsences_BAO_PublicHoliday::getNumberOfPublicHolidaysForPeriod(
        $this->start_date,
        $this->end_date,
        true
      );

      $this->numberOfWorkingDays = $numberOfWorkingDays - $numberOfPublicHolidays;
    }

    return $this->numberOfWorkingDays;
  }

  /**
   * Returns the number of working days to work on this AbsencePeriod between
   * the given start and end dates.
   *
   * This method doesn't count days outside the AbsencePeriod. Meaning that,
   * If the given start date is less than the AbsencePeriod start date, then
   * the AbsencePeriod's start date will be used. If the given end date is
   * greater than AbsencePeriod end date, then the AbsencePeriod's end date
   * will be used.
   *
   * @param string $startDate A date in the Y-m-d format
   * @param string $endDate A date in the Y-m-d format
   *
   * @return int
   */
  public function getNumberOfWorkingDaysToWork($startDate, $endDate) {
    if (!CRM_HRLeaveAndAbsences_Validator_Date::isValid($startDate, 'Y-m-d')) {
      throw new InvalidArgumentException('getNumberOfWorkingDaysToWork expects a valid startDate in Y-m-d format');
    }

    if (!CRM_HRLeaveAndAbsences_Validator_Date::isValid($endDate, 'Y-m-d')) {
      throw new InvalidArgumentException('getNumberOfWorkingDaysToWork expects a valid endDate in Y-m-d format');
    }

    if (strtotime($startDate) < strtotime($this->start_date)) {
      $startDate = $this->start_date;
    }

    if (strtotime($endDate) > strtotime($this->end_date)) {
      $endDate = $this->end_date;
    }

    $periodToWork             = new self();
    $periodToWork->start_date = $startDate;
    $periodToWork->end_date   = $endDate;

    return $periodToWork->getNumberOfWorkingDays();
  }

  /**
   * Returns the Absence Period previous to this one. That is, the Absence
   * Period where weight is equal to this Period weight - 1.
   *
   * @return null|CRM_HRLeaveAndAbsences_BAO_AbsencePeriod - The previous Absence Period or null if there's none
   */
  public function getPreviousPeriod()
  {
    if($this->previousPeriod === false) {
      $this->previousPeriod = null;

      $previousPeriod = new self();
      $previousPeriod->weight = $this->weight - 1;
      $previousPeriod->find(true);

      if($previousPeriod->id) {
        $this->previousPeriod = $previousPeriod;
      }
    }

    return $this->previousPeriod;
  }

  /**
   * Calculates the expiration date for the given AbsenceType within this period.
   *
   * If the AbsenceType has an expiration duration (that is,
   * carry_forward_expiration_duration and carry_forward_expiration_unit are not
   * empty), its value is used to calculate the expiration date starting from
   * the period start date.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsenceType $absenceType
   *
   * @return null|string A date in Y-m-d or null if it can not be calculated
   *
   * @throws \UnexpectedValueException
   */
  public function getExpirationDateForAbsenceType(AbsenceType $absenceType)
  {
    if(!$this->hasValidDates()) {
      throw new UnexpectedValueException(
        'You can only calculate the expiration date for an AbsenceType from an AbsencePeriod with start and end dates'
      );
    }

    $expirationDate = null;

    if($absenceType->hasExpirationDuration()) {
      $expirationDate = $this->getExpirationDurationDate($absenceType);
    }

    return $expirationDate;
  }

  /**
   * Returns the expiration date calculated based on the AbsenceType expiration
   * duration.
   *
   * Example: If the expiration duration is 5 days and this AbsencePeriod
   * start_date is 2016-01-01, the expiration date will be 2016-01-06.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsenceType $absenceType
   *
   * @return null|string A date in Y-m-d or null if it can not be calculated
   */
  private function getExpirationDurationDate(AbsenceType $absenceType)
  {
    if(!$absenceType->allow_carry_forward || !$absenceType->hasExpirationDuration()) {
      return null;
    }

    switch($absenceType->carry_forward_expiration_unit) {
      case AbsenceType::EXPIRATION_UNIT_DAYS:
        $unit = 'D';
        break;
      case AbsenceType::EXPIRATION_UNIT_MONTHS:
        $unit = 'M';
        break;
      case AbsenceType::EXPIRATION_UNIT_YEARS:
        $unit = 'Y';
        break;
      default:
        return null;
    }

    $intervalSpec = 'P'. $absenceType->carry_forward_expiration_duration . $unit;
    $interval = new DateInterval($intervalSpec);
    $expirationDate = new DateTime($this->start_date);
    $expirationDate->add($interval);

    return $expirationDate->format('Y-m-d');
  }

  /**
   * Returns if this AbsencePeriod has valid start and end dates.
   *
   * @return bool
   */
  private function hasValidDates() {
    return CRM_HRLeaveAndAbsences_Validator_Date::isValid($this->start_date, 'Y-m-d') &&
           CRM_HRLeaveAndAbsences_Validator_Date::isValid($this->end_date, 'Y-m-d');
  }
}
