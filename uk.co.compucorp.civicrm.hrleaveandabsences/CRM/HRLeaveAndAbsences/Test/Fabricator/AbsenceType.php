<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

class CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType extends
  CRM_HRLeaveAndAbsences_Test_Fabricator_SequentialTitle {

  private static $defaultParams = [
    'color'                     => '#000000',
    'default_entitlement'       => 20,
    'allow_request_cancelation' => 1,
    'allow_carry_forward'       => 1,
  ];

  public static function fabricate($params = []) {
    $params = array_merge(static::$defaultParams, $params);

    if(empty($params['title'])) {
      $params['title'] = static::nextSequentialTitle();
    }

    return AbsenceType::create($params);
  }

}
