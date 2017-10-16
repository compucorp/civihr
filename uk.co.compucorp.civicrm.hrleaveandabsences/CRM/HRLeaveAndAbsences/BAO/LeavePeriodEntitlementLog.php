<?php
use CRM_HRLeaveAndAbsences_ExtensionUtil as E;

class CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlementLog extends CRM_HRLeaveAndAbsences_DAO_LeavePeriodEntitlementLog {

  /**
   * Create a new LeavePeriodEntitlementLog based on array-data
   *
   * @param array $params
   *
   * @return CRM_HRLeaveAndAbsences_DAO_LeavePeriodEntitlementLog|NULL
   */
  public static function create($params) {
    $entityName = 'LeavePeriodEntitlementLog';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }
}
