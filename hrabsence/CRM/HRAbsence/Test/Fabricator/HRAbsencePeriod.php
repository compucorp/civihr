<?php

class CRM_HRAbsence_Test_Fabricator_HRAbsencePeriod {

  public static function fabricate($params = []) {
    if(empty($params['title'])) {
      $params['title'] = 'Absence Period ' . microtime();
    }

    return CRM_HRAbsence_BAO_HRAbsencePeriod::create($params);
  }

}
