<?php

use CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern as ContactWorkPattern;
use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;

class CRM_HRLeaveAndAbsences_BAO_WorkPattern extends CRM_HRLeaveAndAbsences_DAO_WorkPattern {

  /**
   * This field is used to cache the results from the getWeeks method
   *
   * @var array
   */
  private $weeks = null;

  /**
   * Create a new WorkPattern based on array-data
   *
   * This method can handle related weeks. For that, you should pass
   * the data as:
   *
   * <code>
   * $params = [
   *  ...
   *  'weeks' => [
   *    [
   *      'days' => [
   *        ['type' => 2, 'day_of_the_week' => 1, 'time_from' => '09:00', ...],
   *        ...
   *      ]
   *    ],
   *    ...
   *  ],
   *  ...
   * ];
   * </code>
   *
   * Note that you don't need to include the week number, as they will be
   * automatically generated based on the order of the week inside the array.
   *
   * @param array $params key-value pairs
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_WorkPattern|NULL
   *
   * @throws \Exception
   */
  public static function create($params) {
    $entityName = 'WorkPattern';
    $hook = empty($params['id']) ? 'create' : 'edit';

    if(isset($params['is_default']) && $params['is_default']) {
      self::unsetDefaultWorkPatterns();
    }

    if($hook == 'create') {
      $params['weight'] = self::getMaxWeight() + 1;
    }

    if($hook == 'edit' && isset($params['is_active']) && !$params['is_active']) {
      self::checkIfPatternCanBeDisabled($params['id']);
    }

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new CRM_HRLeaveAndAbsences_BAO_WorkPattern();
    $instance->copyValues($params);

    $transaction = new CRM_Core_Transaction();
    try {
      $instance->save();
      if(!empty($params['weeks'])) {
        $instance->saveWeeks($params['weeks']);
      }
      $transaction->commit();
    } catch(Exception $e) {
      $transaction->rollback();
      // We throw the catched Exception how forms can handle the
      // error and properly inform the user about what happened
      throw $e;
    }

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Deletes the Work Pattern with the given ID
   *
   * @param int $id The ID of the Work Pattern to be deleted
   */
  public static function del($id)
  {
    $workPattern = new CRM_HRLeaveAndAbsences_DAO_WorkPattern();
    $workPattern->id = $id;
    $workPattern->find(true);
    $workPattern->delete();
  }

  /**
   * This method checks if a Work Pattern can be disabled.
   *
   * A Work Pattern can be disabled only if it's not the last enabled pattern.
   *
   * @param int $id
   *  The ID of the WorkPattern to be checked
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidWorkPatternException
   */
  private static function checkIfPatternCanBeDisabled($id) {
    $tableName = self::getTableName();
    $id = (int)$id;

    $query = "
        SELECT COUNT(*) as total
        FROM {$tableName}
        WHERE is_active = 1 AND id <> $id
    ";

    $dao = CRM_Core_DAO::executeQuery($query);
    $dao->fetch();

    $total = (int)$dao->total;

    if($total == 0) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidWorkPatternException(
        "You cannot disable a Work Pattern if it's the last one"
      );
    }
  }

  /**
   * Returns the default WorkPattern
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_WorkPattern
   */
  public static function getDefault() {
    $workPattern = new self();
    $workPattern->is_default = 1;
    $workPattern->find(true);

    return $workPattern;
  }

  /**
   * This method works like find() (it actually uses it)
   * but it includes the number_of_weeks and number_of_hours
   * for each pattern.
   *
   * It loads all the data with a single SQL query, giving you a
   * better performance than loading the related information in
   * different queries.
   *
   */
  public function findWithNumberOfWeeksAndHours()
  {
    $week = new CRM_HRLeaveAndAbsences_BAO_WorkWeek();
    $day = new CRM_HRLeaveAndAbsences_BAO_WorkDay();
    $week->joinAdd($day, 'LEFT');
    $this->joinAdd($week, 'LEFT');
    $this->orderBy('weight');
    $this->selectAdd('civicrm_hrleaveandabsences_work_pattern.*');
    $this->selectAdd('COUNT(DISTINCT civicrm_hrleaveandabsences_work_week.id) as number_of_weeks');
    $this->selectAdd('SUM(civicrm_hrleaveandabsences_work_day.number_of_hours) as number_of_hours');
    $this->groupBy('civicrm_hrleaveandabsences_work_pattern.id');
    $this->find();
  }

  /**
   * Saves this WorkPattern's related Weeks.
   *
   * To make things easier, we delete all the existing pattern's week before
   * adding the new ones. Since this is called from inside of a transaction,
   * if we get any errors, the transaction will be rolled back and the deleted
   * weeks will be restored.
   *
   * The Week's numbers are automatically generated based on the order of the
   * week on the array.
   *
   * @param array $weeks An array of weeks. Every week must contain a 'days'
   *                     array, containing the days to be added to this week
   *
   * @throws \Exception
   */
  private function saveWeeks($weeks)
  {
    if(!$this->id) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidWorkPatternException(
        ts('It is not possible to add weeks to an non-existing Work Pattern')
      );
    }

    $this->deleteWeeks();
    $weekNumber = 1;
    foreach($weeks as $week) {
      if(!empty($week['days'])) {
        CRM_HRLeaveAndAbsences_BAO_WorkWeek::create([
          'number' => $weekNumber,
          'pattern_id' => $this->id,
          'days' => $week['days']
        ]);
        $weekNumber++;
      }
    }
  }

  /**
   * Deletes all the weeks related to this WorkPattern
   */
  private function deleteWeeks()
  {
    $workWeekEntity = new CRM_HRLeaveAndAbsences_BAO_WorkWeek();
    $workWeekEntity->whereAdd('pattern_id = '. $this->id);
    $workWeekEntity->delete(true);
  }

  /**
   * Gets the maximum weight of all work patterns
   *
   * Returns 0 if there's no work pattern available
   *
   * @return int the maximum weight
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
   * Returns an array containing all the fields values for the
   * WorkPattern with the given ID, including its related
   * WorkWeeks and WorkDays.
   *
   * This method is mainly used by the WorkPattern form, so it
   * can get the data to fill its fields.
   *
   * An empty array is returned if it is not possible to load
   * the data.
   *
   * @param int $id The id of the WorkPattern to retrieve the values
   *
   * @return array An array containing the values
   */
  public static function getValuesArray($id) {
    try {
      $params = [
        'id' => $id,
        'api.WorkWeek.get' => [
          'pattern_id' => '$value.id',
          'api.WorkDay.get' => [
            'week_id' => '$value.id'
          ]
        ]
      ];
      $result = civicrm_api3('WorkPattern', 'getsingle', $params);

      $workWeeks = $result['api.WorkWeek.get']['values'];
      foreach($workWeeks as $i => $week) {
        $workWeeks[$i]['days'] = $week['api.WorkDay.get']['values'];
        unset($workWeeks[$i]['api.WorkDay.get']);
      }

      $result['weeks'] = $workWeeks;
      unset($result['api.WorkWeek.get']);
      return $result;

    } catch(CiviCRM_API3_Exception $ex) {
      return [];
    }
  }

  /**
   * Returns the leave_days amount for the given $date, based on the $startDate.
   *
   * If the $date is not greater than or equal the $startDate, it will return 0.
   *
   * @param \DateTime $date
   * @param \DateTime $startDate
   *
   * @return float
   */
  public function getLeaveDaysForDate(DateTime $date, DateTime $startDate) {
    $day = $this->getWorkDayforDate($date, $startDate);
    return isset($day['leave_days']) ? (float)$day['leave_days'] : 0;
  }

  /**
   * Returns the Work day type for the given date, based on the $startDate
   *
   * If the $date is not greater than or equal the $startDate, it will return 0.
   *
   * @param \DateTime $date
   * @param \DateTime $startDate
   *
   * @return int
   */
  public function getWorkDayTypeForDate(DateTime $date, DateTime $startDate) {
    $day = $this->getWorkDayforDate($date, $startDate);
    return $day['type'];
  }

  /**
   * This method returns the work day for the given date
   *
   * This method will rotate through the pattern's weeks to get the return value.
   * That is, starting from $startDate, if the $date falls on the first week, we
   * get the leave_days amount from the first week of the pattern; if it falls on
   * second week, we get it from the pattern's second week; if it's on the third
   * week, we rotate and get the value from the pattern's first week again and
   *
   * If the $date is not greater than or equal the $startDate, it will return 0.
   *
   * If the WorkPattern doesn’t have weeks, it will return 0.
   *
   * @param \DateTime $date
   * @param \DateTime $startDate
   *
   * @return array
   *   An array containing information about the work day
   */
  public function getWorkDayforDate(DateTime $date, DateTime $startDate) {
    if($date < $startDate) {
      return 0;
    }

    $weeks = $this->getWeeks();

    if(empty($weeks)) {
      return 0;
    }
    $dateDayOfTheWeek = $date->format('N');
    $week = $this->getWeekForDateFromStartDate($date, $startDate);

    foreach($week['days'] as $day) {
      if($day['day_of_the_week'] == $dateDayOfTheWeek) {
        return $day;
      }
    }
  }

  /**
   * Based on a given startDate, this method will calculate which of the work
   * pattern weeks should be used for the given date.
   *
   * Here it's how it works:
   * 1. First, to make the calculation easier, we adjust the $startDate to be
   * the monday of its week. Example, if the $startDate is 2016-07-30 (a
   * Saturday), it will be changed to 2016-07-25, the monday of the startDate's
   * week.
   * 2. Next, we calculate the number of weeks between the $startDate and the
   * $date.
   * 3. Based on the number of weeks on the work pattern and the number of weeks
   * between the $startDate and $date, we decide which of the pattern's week
   * to return. The patterns will rotate, that is, for the first week, we return
   * the first pattern's week, for second week, the second pattern's week, for
   * the third week, the first pattern's week and so on.
   *
   * @param \DateTime $date
   * @param \DateTime $startDate
   *
   * @return array
   *    The WorkPattern week to be used for the given $date
   */
  private function getWeekForDateFromStartDate(DateTime $date, DateTime $startDate) {
    $weeks = $this->getWeeks();

    $startDate = $this->shiftDateToLastMonday($startDate);

    $dateNumberOfWeek = floor($date->diff($startDate)->days / 7);
    $weekToUse   = $dateNumberOfWeek % count($weeks);

    return $weeks[$weekToUse];
  }

  /**
   * This is basically a non-static version of the getValuesArray() method,
   * which loads the data based on the ID of this WorkPattern.
   *
   * It also caches the fetched data, so we won't have performance problems if
   * it gets called multiple times (For example, when we need to get the
   * leave_days amount for multiple dates).
   *
   * @return array
   */
  private function getWeeks() {
    if($this->weeks == null) {
      $this->weeks = [];

      $valuesArray = self::getValuesArray($this->id);
      if(!empty($valuesArray['weeks'])) {
        $this->weeks = $valuesArray['weeks'];
      }
    }

    return $this->weeks;
  }

  /**
   * Based on the given $date, returns a new $date representing the monday on
   * the $date's week.
   *
   * Example, if the date is 2016-07-30 (a Saturday), it will be return 2016-07-25,
   * the monday of the date's week.
   *
   * @param \DateTime $date
   *
   * @return \DateTime
   */
  private function shiftDateToLastMonday(DateTime $date) {
    $isMonday = $date->format('N') == 1;

    if (!$isMonday) {
      $lastMonday = date('Y-m-d', strtotime('last monday', $date->getTimestamp()));
      $date  = new DateTime($lastMonday);
    }

    return $date;
  }

  /**
   * Unset the is_default flag for the default Work Pattern
   */
  private static function unsetDefaultWorkPatterns() {
    $tableName = self::getTableName();
    $query = "UPDATE {$tableName} SET is_default = 0 WHERE is_default = 1";
    CRM_Core_DAO::executeQuery($query);
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   */
  public function links() {
    $workWeekTable = CRM_HRLeaveAndAbsences_BAO_WorkWeek::getTableName();
    return [
      'id' => "{$workWeekTable}:pattern_id"
    ];
  }

  /**
   * Returns the type of this day for the contact whether its a Working Day, Non Working Day, Weekend or Public Holiday
   * The method first of all finds the valid work pattern for this contact for the given date
   * and also the effective start date of the contact work pattern
   * This information is then used to find the work day type
   *
   * @param int $contactId
   * @param \DateTime $date
   *
   * @return string
   *   Can be any of the following:
   *   - Public Holiday: When work day is a public holiday
   *   - Weekend: When work day is weekend
   *   - No: When work day is Non working day
   *   - Yes: When work day is working day or All day
   */
  public static function getWorkDayType($contactId, DateTime $date) {

    $workPattern = ContactWorkPattern::getWorkPattern($contactId, $date);
    $startDate = ContactWorkPattern::getStartDate($contactId, $date);
    $workPattern->getWorkDayTypeForDate($date, $startDate);

    if(!$workPattern || !$startDate) {
      return Workday::WORK_DAY_OPTION_NO;
    }

    $workDayTypeId = $workPattern->getWorkDayTypeForDate($date, $startDate);
    return $workDayTypeId;
  }


}
