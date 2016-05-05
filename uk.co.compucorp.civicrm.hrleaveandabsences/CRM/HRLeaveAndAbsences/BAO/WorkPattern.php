<?php

class CRM_HRLeaveAndAbsences_BAO_WorkPattern extends CRM_HRLeaveAndAbsences_DAO_WorkPattern {

  /**
   * Create a new WorkPattern based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_WorkPattern|NULL
   *
   */
  public static function create($params) {
    $className = 'CRM_HRLeaveAndAbsences_DAO_WorkPattern';
    $entityName = 'WorkPattern';
    $hook = empty($params['id']) ? 'create' : 'edit';

    if(isset($params['is_default']) && $params['is_default']) {
      self::unsetDefaultWorkPatterns();
    }

    if(empty($params['id'])) {
      $params['weight'] = self::getMaxWeight() + 1;
    }

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
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
