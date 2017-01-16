<?php

class CRM_HRRecruitment_Test_Fabricator_HRVacancy {

  private static $defaultParams = [
    'position' => "test vacany",
    'start_date' => "2016-01-01",
    'end_date' => "",
  ];

  public static function fabricate($params = []) {
    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3(
      'HRVacancy',
      'create',
      $params
    );

    return array_shift($result['values']);
  }
}
