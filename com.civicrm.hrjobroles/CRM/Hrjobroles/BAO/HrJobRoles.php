<?php

class CRM_Hrjobroles_BAO_HrJobRoles extends CRM_Hrjobroles_DAO_HrJobRoles {

  /**
   * Create a new HrJobRoles based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Hrjobroles_DAO_HrJobRoles|NULL
   */
  public static function create($params) {
    $entityName = 'HrJobRoles';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new static();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  public static function importableFields() {
    return static::import();
  }
}
