<?php

class CRM_HRLeaveAndAbsences_BAO_NotificationReceiver extends CRM_HRLeaveAndAbsences_DAO_NotificationReceiver {

  /**
   * Create a new NotificationReceiver based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_NotificationReceiver|NULL
   **/
  public static function create($params) {
    $className = 'CRM_HRLeaveAndAbsences_DAO_NotificationReceiver';
    $entityName = 'NotificationReceiver';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }
}
