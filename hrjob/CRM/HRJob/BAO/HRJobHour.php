<?php

class CRM_HRJob_BAO_HRJobHour extends CRM_HRJob_DAO_HRJobHour {

  /**
   * Create a new HRJobHour based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRJob_DAO_HRJobHour|NULL
   *
  public static function create($params) {
    $className = 'CRM_HRJob_DAO_HRJobHour';
    $entityName = 'HRJobHour';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */
}
