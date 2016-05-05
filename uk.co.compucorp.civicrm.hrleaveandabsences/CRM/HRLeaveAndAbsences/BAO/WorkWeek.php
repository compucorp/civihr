<?php

class CRM_HRLeaveAndAbsences_BAO_WorkWeek extends CRM_HRLeaveAndAbsences_DAO_WorkWeek {

  /**
   * Create a new WorkWeek based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_WorkWeek|NULL
   *
   */
  public static function create($params) {
    $className = 'CRM_HRLeaveAndAbsences_DAO_WorkWeek';
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
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
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
  private static function getMaxWeekNumberForWorkPattern($workPatternId)
  {
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

  public function links()
  {
    $workPatternTable = CRM_HRLeaveAndAbsences_BAO_WorkPattern::getTableName();
    $workDayTable = CRM_HRLeaveAndAbsences_BAO_WorkDay::getTableName();
    return [
      'pattern_id' => "{$workPatternTable}:id",
      'id' => "{$workDayTable}:week_id"
    ];
  }
}
