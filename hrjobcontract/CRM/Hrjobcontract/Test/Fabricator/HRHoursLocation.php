<?php

class CRM_Hrjobcontract_Test_Fabricator_HRHoursLocation {

  private static $defaultParams = [
    'location' => 'test location',
    'standard_hours' => 40,
    'periodicity' => "Week",
    'sequential'   => 1
  ];

  public static function fabricate($params = []) {
    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3(
      'HRHoursLocation',
      'create',
      $params
    );

    return array_shift($result['values']);
  }
}
