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
}
