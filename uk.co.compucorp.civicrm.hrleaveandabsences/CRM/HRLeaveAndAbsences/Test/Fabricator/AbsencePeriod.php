<?php

use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;

class CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod extends
  CRM_HRLeaveAndAbsences_Test_Fabricator_SequentialTitle {

  public static function fabricate($params = [], $loadAfterSave = false) {
    $params = array_merge(static::getDefaultParams(), $params);

    if(empty($params['title'])) {
      $params['title'] = static::nextSequentialTitle();
    }

    $absencePeriod = AbsencePeriod::create($params);

    if($loadAfterSave) {
      $absencePeriod = AbsencePeriod::findById($absencePeriod->id);
    }

    return $absencePeriod;
  }

  private static function getDefaultParams() {
    return [
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day'))
    ];
  }

}
