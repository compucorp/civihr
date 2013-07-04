<?php

class CRM_HRJob_BAO_HRJobPension extends CRM_HRJob_DAO_HRJobPension {

  /**
   * Create a new HRJobPension based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRJob_DAO_HRJobPension|NULL
   *
  public static function create($params) {
    $className = 'CRM_HRJob_DAO_HRJobPension';
    $entityName = 'HRJobPension';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */
}
