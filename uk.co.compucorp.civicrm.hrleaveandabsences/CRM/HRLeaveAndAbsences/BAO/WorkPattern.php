<?php

class CRM_HRLeaveAndAbsences_BAO_WorkPattern extends CRM_HRLeaveAndAbsences_DAO_WorkPattern {

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

    if(empty($params['id'])) {
      $params['weight'] = self::getMaxWeight() + 1;
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

  private static function unsetDefaultWorkPatterns()
  {
    $tableName = self::getTableName();
    $query = "UPDATE {$tableName} SET is_default = 0 WHERE is_default = 1";
    CRM_Core_DAO::executeQuery($query);
  }

  public function links()
  {
    $workWeekTable = CRM_HRLeaveAndAbsences_BAO_WorkWeek::getTableName();
    return [
      'id' => "{$workWeekTable}:pattern_id"
    ];
  }
}
