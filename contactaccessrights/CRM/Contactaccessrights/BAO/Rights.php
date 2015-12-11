<?php

class CRM_Contactaccessrights_BAO_Rights extends CRM_Contactaccessrights_DAO_Rights {

  /**
   * Create a new Rights based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Contactaccessrights_DAO_Rights|NULL
   *
  public static function create($params) {
    $className = 'CRM_Contactaccessrights_DAO_Rights';
    $entityName = 'Rights';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */
}
