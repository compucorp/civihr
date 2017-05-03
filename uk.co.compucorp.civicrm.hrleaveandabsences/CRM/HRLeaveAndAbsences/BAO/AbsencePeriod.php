<?php

use CRM_HRCore_Date_BasicDatePeriod as BasicDatePeriod;
use CRM_HRLeaveAndAbsences_Validator_Date as DateValidator;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException as InvalidAbsencePeriodException;

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
   * Create a new AbsencePeriod based on array-data
   *
   * @param array $params
   *  An array of field => value pairs
   *
   * @return \CRM_HRLeaveAndAbsences_DAO_AbsencePeriod|NULL
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
  private static function validateParams($params) {
    self::validateDates($params);

    if(self::overlapsWithAnotherPeriod($params)) {
      throw new InvalidAbsencePeriodException(
        'This Absence Period overlaps with another existing Period'
      );
    }

    self::validateAbsencePeriodTitle($params);
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
  private static function validateDates($params) {
    if(empty($params['start_date']) || empty($params['end_date'])) {
      throw new InvalidAbsencePeriodException(
        'Both the start and end dates are required'
      );
    }

    $startDateIsValid = DateValidator::isValid($params['start_date']);
    $endDateIsValid = DateValidator::isValid($params['end_date']);
    if(!$startDateIsValid || !$endDateIsValid) {
      throw new InvalidAbsencePeriodException(
        'Both the start and end dates should be valid'
      );
    }

    $startDate = strtotime($params['start_date']);
    $endDate = strtotime($params['end_date']);
    if($startDate >= $endDate) {
      throw new InvalidAbsencePeriodException(
        'Start Date should be less than End Date'
      );
    }
  }

  /**
   * Checks if another absence period exists with same title as
   * the absence period being created/updated and throws an exception if found.
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException
   */
  private static function validateAbsencePeriodTitle($params) {
    $title = !empty($params['title']) ? $params['title'] : '';
    $absencePeriod = new self();
    $absencePeriod->title = $title;
    $absencePeriod->find(true);

    if (!$absencePeriod->id) {
      return;
    }

    $throwExceptionOnCreate = empty($params['id']);
    $throwExceptionOnUpdate = !empty($params['id']) && $absencePeriod->id != $params['id'];

    if ($throwExceptionOnCreate || $throwExceptionOnUpdate) {
      throw new InvalidAbsencePeriodException(
        'Absence Period with same title already exists!'
      );
    }
  }

  /**
   * Checks if there's an existing Absence Period that overlaps with the
   * start and end dates on the given params array.
   *
   * @param array $params
   *  The params array passed to the create method
   *
   * @return bool
   */
  private static function overlapsWithAnotherPeriod($params) {
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
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $instance
   *  The just saved AbsencePeriod
   */
  private static function updatePeriodsOrder($instance) {
    if(self::theresAnotherPeriodWithTheSameWeight($instance)) {
      self::increaseWeightsEqualOrGreaterTo($instance);
    }
  }

  /**
   * Checks if there's another period with the same weight of the given AbsencePeriod
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $instance
   *
   * @return bool
   */
  private static function theresAnotherPeriodWithTheSameWeight($instance) {
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
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $instance
   */
  private static function increaseWeightsEqualOrGreaterTo($instance) {
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
   * @return int
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
   * @param int $id
   *  The id of the AbsencePeriod to retrieve the values
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
   * @return string
   *  The most recent start date available in Y-m-d format
   */
  public static function getMostRecentStartDateAvailable() {
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
  public static function getCurrentPeriod() {
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
   * Returns the AbsencePeriod which overlaps the given date. If not period
   * overlaps it, then null is returned
   *
   * @param \DateTime $date
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod|null
   */
  public static function getPeriodOverlappingDate(DateTime $date) {
    $overlappingDate = $date->format('Y-m-d');

    $period = new self();
    $period->whereAdd("start_date <= '{$overlappingDate}'");
    $period->whereAdd("end_date >= '{$overlappingDate}'");
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
  public function getNumberOfWorkingDays() {
    if(!$this->numberOfWorkingDays) {
      if(!DateValidator::isValid($this->start_date, 'Y-m-d')) {
        throw new UnexpectedValueException('You can only get the number of working days for an AbsencePeriod with a valid start date');
      }

      if(!DateValidator::isValid($this->end_date, 'Y-m-d')) {
        throw new UnexpectedValueException('You can only get the number of working days for an AbsencePeriod with a valid end date');
      }

      $numberOfWorkingDays = 0;
      $period = new BasicDatePeriod($this->start_date, $this->end_date);
      foreach($period as $date) {
        $dayOfTheWeek = $date->format('N');
        $dayIsWorkingDay = $dayOfTheWeek > 0 && $dayOfTheWeek < 6;
        if($dayIsWorkingDay) {
          $numberOfWorkingDays++;
        }
      }

      $numberOfPublicHolidays = PublicHoliday::getCountForPeriod(
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
   * @param string $startDate
   *  A date in the Y-m-d format
   * @param string $endDate
   *  A date in the Y-m-d format
   *
   * @return int
   */
  public function getNumberOfWorkingDaysToWork($startDate, $endDate) {
    if (!DateValidator::isValid($startDate, 'Y-m-d')) {
      throw new InvalidArgumentException('getNumberOfWorkingDaysToWork expects a valid startDate in Y-m-d format');
    }

    if (!DateValidator::isValid($endDate, 'Y-m-d')) {
      throw new InvalidArgumentException('getNumberOfWorkingDaysToWork expects a valid endDate in Y-m-d format');
    }

    list($startDate, $endDate) = $this->adjustDatesToMatchPeriodDates($startDate, $endDate);

    $periodToWork             = new self();
    $periodToWork->start_date = $startDate;
    $periodToWork->end_date   = $endDate;

    return $periodToWork->getNumberOfWorkingDays();
  }

  /**
   * Returns the Absence Period previous to this one. That is, the Absence
   * Period where weight is equal to this Period weight - 1.
   *
   * @return null|\CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   *  The previous Absence Period or null if there's none
   */
  public function getPreviousPeriod() {
    return $this->getAbsencePeriodByWeight($this->weight - 1);
  }

  /**
   * Returns the Absence Period next to this one. That is, the Absence
   * Period where weight is equal to this Period weight + 1.
   *
   * @return null|\CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   *  The next Absence Period or null if there's none
   */
  public function getNextPeriod() {
    return $this->getAbsencePeriodByWeight($this->weight + 1);
  }

  /**
   * Returns the Absence Period with the given $weight.
   *
   * If there is no Absence Period with the given value, null will be returned.
   *
   * @param int $weight
   *
   * @return null|\CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   */
  private function getAbsencePeriodByWeight($weight) {
    $nextPeriod = new self();
    $nextPeriod->weight = (int)$weight;
    $nextPeriod->find(true);
    if($nextPeriod->id) {
      return $nextPeriod;
    }

    return null;
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
   * @return null|string
   *  A date in Y-m-d or null if it can not be calculated
   *
   * @throws \UnexpectedValueException
   */
  public function getExpirationDateForAbsenceType(AbsenceType $absenceType) {
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
   * @return null|string
   *  A date in Y-m-d or null if it can not be calculated
   */
  private function getExpirationDurationDate(AbsenceType $absenceType) {
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
    return DateValidator::isValid($this->start_date, 'Y-m-d') &&
           DateValidator::isValid($this->end_date, 'Y-m-d');
  }

  /**
   * This method will adjust the date range given by $startDate and $endDate
   * to be inside this period date range.
   *
   * If the given $startDate is less than the period start date, it will be
   * changed to be equals the period start date. If the given $endDate is greater
   * than the period end date, it will be changed to be equals to the period
   * end date.
   *
   * Example:
   * Period start date: 2016-01-01
   * Period end date: 2016-12-31
   * $startDate: 2015-10-01
   * $endDate: 2016-07-01
   *
   * Adjusted values:
   * $startDate: 2016-01-01 (Adjusted to be equals to the period start date)
   * $endDate: 2016-07-01 (Not adjusted since it's less then the period end date)
   *
   * @param string $startDate
   *    A date in the Y-m-d format
   * @param string $endDate
   *    A date in the Y-m-d format
   *
   * @return array
   *    An array containing the adjusted dates. The first item is the
   *    $startDate and second one is the $endDate
   */
  public function adjustDatesToMatchPeriodDates($startDate, $endDate) {
    if (strtotime($startDate) < strtotime($this->start_date)) {
      $startDate = $this->start_date;
    }

    if (strtotime($endDate) > strtotime($this->end_date)) {
      $endDate = $this->end_date;
    }

    return [$startDate, $endDate];
  }

  /**
   * Returns the absence period that contains both the fromdate and todate
   * If the fromdate and todate have dates in two absence periods(i.e an overlap),
   * or if no valid absence period is found containing the dates, null is returned.
   *
   * @param DateTime $fromDate
   * @param DateTime|null $toDate
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod|null
   */
  public static function getPeriodContainingDates(DateTime $fromDate, DateTime $toDate = null) {
    $tableName = self::getTableName();

    if (!$toDate) {
      $toDate = clone $fromDate;
    }

    $query = "SELECT * FROM {$tableName} WHERE start_date <= %1 AND end_date >= %2";
    $queryParams = [
      1 => [$fromDate->format('Y-m-d'), 'String'],
      2 => [$toDate->format('Y-m-d'), 'String']
    ];

    $absencePeriod = CRM_Core_DAO::executeQuery($query, $queryParams, true, self::class);

    if ($absencePeriod->fetch()) {
      return $absencePeriod;
    }
    return null;
  }
}
