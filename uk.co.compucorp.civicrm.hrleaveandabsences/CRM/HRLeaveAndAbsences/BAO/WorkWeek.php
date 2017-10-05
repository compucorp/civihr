<?php

use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;
use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_Exception_InvalidWorkWeekException as InvalidWorkWeekException;

class CRM_HRLeaveAndAbsences_BAO_WorkWeek extends CRM_HRLeaveAndAbsences_DAO_WorkWeek {

  /**
   * Create a new WorkWeek based on array-data.
   *
   * This method can handle related days. For that, you should pass
   * the data as:
   *
   * <code>
   * $params = [
   *  ...
   *  'days' => [
   *    ['type' => 2, 'day_of_the_week' => 1, 'time_from' => '09:00', ...],
   *    ...
   *  ],
   *  ...
   * ];
   * </code>
   *
   * It's important to note that, when passing the days in the params
   * array, it must contain EXACTLY 7 days
   *
   * @param array $params key-value pairs
   *
   * @return \CRM_HRLeaveAndAbsences_DAO_WorkWeek|NULL
   * @throws \Exception
   */
  public static function create($params) {
    $entityName = 'WorkWeek';
    $hook = empty($params['id']) ? 'create' : 'edit';

    // The number is always automatically generated on create
    unset($params['number']);

    if(empty($params['id'])) {
      $params['number'] = self::getMaxWeekNumberForWorkPattern($params['pattern_id']) + 1;
    }

    if(!empty($params['id'])) {
      unset($params['pattern_id']);
    }

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);

    $transaction = new CRM_Core_Transaction();
    try {
      $instance->save();
      if(!empty($params['days'])) {
        $instance->saveDays($params['days']);
      }
      $transaction->commit();
    } catch(Exception $e) {
      $transaction->rollback();
      throw $e;
    }

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Saves this WorkWeek's related Days.
   *
   * To make things easier, we delete all the existing weeks's days before
   * adding the new ones. Since this is called from inside of a transaction,
   * if we get any errors, the transaction will be rolled back and the deleted
   * days will be restored.
   *
   * A week must have EXACTLY 7 days.
   *
   * @param array $days An array of days. It must contain exactly 7 days
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidWorkWeekException
   */
  private function saveDays($days) {
    if(!$this->id) {
      throw new InvalidWorkWeekException(ts('It is not possible to add days to an non-existing Work Week'));
    }

    if(!is_array($days) || count($days) != 7) {
      throw new InvalidWorkWeekException(ts('A Work Week must contain EXACTLY 7 days'));
    }

    $this->deleteDays();
    $dayOfTheWeek = 1;
    foreach($days as $day) {
      $workDayParams = [
        'week_id' => $this->id,
        'day_of_the_week' => $dayOfTheWeek,
        'type' => $day['type']
      ];

      if($day['type'] == WorkDay::getWorkingDayTypeValue()) {
        $workDayParams['time_from'] = $day['time_from'];
        $workDayParams['time_to'] = $day['time_to'];
        $workDayParams['break'] = $day['break'];
        $workDayParams['leave_days'] = $day['leave_days'];
        $workDayParams['number_of_hours'] = $day['number_of_hours'];
      }

      WorkDay::create($workDayParams);
      $dayOfTheWeek++;
    }
  }

  /**
   * Deletes all the dasy related to this WorkWeek
   */
  private function deleteDays() {
    $workWeekEntity = new WorkDay();
    $workWeekEntity->whereAdd('week_id = '. $this->id);
    $workWeekEntity->delete(true);
  }

  /**
   * Gets the maximum week number for the given Work Pattern.
   *
   * If the Work Pattern hasn't any Work Week it returns 0
   *
   * @param int $workPatternId The ID of the WorkPattern
   * @return int the maximum week number
   *
   */
  private static function getMaxWeekNumberForWorkPattern($workPatternId) {
    $tableName = self::getTableName();
    $query = "
      SELECT MAX(number) as max_number
      FROM {$tableName}
      WHERE pattern_id = {$workPatternId}";
    $dao = CRM_Core_DAO::executeQuery($query);
    if($dao->fetch() && !is_null($dao->max_number)) {
      return $dao->max_number;
    }

    return 0;
  }

  /**
   * Declares the relationship between work patterns and work days
   *
   * @return array
   */
  public function links() {
    $workPatternTable = WorkPattern::getTableName();
    $workDayTable = WorkDay::getTableName();
    return [
      'pattern_id' => "{$workPatternTable}:id",
      'id' => "{$workDayTable}:week_id"
    ];
  }
}
