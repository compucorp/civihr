<?php

class CRM_Hrjobcontract_Test_Fabricator_HRPayScale {

  private static $defaultParams = [
    'pay_scale' => 'test scale',
    'currency' => "USD",
    'amount' => "35000.00",
    'periodicity' => "Year",
    'sequential'   => 1
  ];

  public static function fabricate($params =[]) {
    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3(
      'HRPayScale',
      'create',
      $params
    );

    return array_shift($result['values']);
  }
}
