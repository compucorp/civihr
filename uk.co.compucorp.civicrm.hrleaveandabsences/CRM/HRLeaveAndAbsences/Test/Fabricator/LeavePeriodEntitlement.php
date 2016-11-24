<?php

use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;

class CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement extends
  CRM_HRLeaveAndAbsences_Test_Fabricator_SequentialTitle  {

  public static function fabricate($params = [], $loadAfterSave = false) {
    $params = array_merge(static::getDefaultParams(), $params);

    if(empty($params['title'])) {
      $params['title'] = static::nextSequentialTitle();
    }

    $leaveEntitlementPeriod = LeavePeriodEntitlement::create($params);

    if($loadAfterSave) {
      $leaveEntitlementPeriod = LeavePeriodEntitlement::findById($leaveEntitlementPeriod->id);
    }

    return $leaveEntitlementPeriod;
  }

  private static function getDefaultParams() {
    return [
      'type_id' => 1,
      'period_id' => 1,
      'contact_id' => 1
    ];
  }


}
