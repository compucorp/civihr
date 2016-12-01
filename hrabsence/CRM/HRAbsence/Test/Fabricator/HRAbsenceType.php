<?php

class CRM_HRAbsence_Test_Fabricator_HRAbsenceType {

  private static $defaultParams = [
    'is_active' => 1,
  ];

  public static function fabricate($params = []) {
    if(empty($params['title'])) {
      $params['title'] = 'Absence Type ' . microtime();
    }

    if(empty($params['name'])) {
      $params['name'] = 'Absence Type ' . microtime();
    }

    $params = array_merge(self::$defaultParams, $params);

    return CRM_HRAbsence_BAO_HRAbsenceType::create($params);
  }

}
