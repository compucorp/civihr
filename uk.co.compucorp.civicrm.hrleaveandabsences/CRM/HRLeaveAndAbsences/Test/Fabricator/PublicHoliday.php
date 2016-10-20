<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;

class CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHoliday extends
  CRM_HRLeaveAndAbsences_Test_Fabricator_SequentialTitle {

  public static function fabricate($params = []) {
    if(empty($params['title'])) {
      $params['title'] = static::nextSequentialTitle();
    }

    return PublicHoliday::create($params);
  }
}
