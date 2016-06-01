<?php

class CRM_HRLeaveAndAbsences_BAO_PublicHoliday extends CRM_HRLeaveAndAbsences_DAO_PublicHoliday {

  /**
   * Create a new PublicHoliday based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_PublicHoliday|NULL
   **/
  public static function create($params) {
    $className = 'CRM_HRLeaveAndAbsences_DAO_PublicHoliday';
    $entityName = 'PublicHoliday';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $transaction = new CRM_Core_Transaction();
    $instance->save();
    $transaction->commit();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

}
