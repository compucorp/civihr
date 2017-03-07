<?php

class CRM_HRCore_Test_Fabricator_OptionValue {

  private static $defaultParams = [
    'name' => 'test option',
    'sequential' => 1
  ];

  public static function fabricate($params = []) {
    $params['value'] = empty($params['value']) ? mt_rand(1000, 9000) : $params['value'];

    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3(
      'OptionValue',
      'create',
      $params
    );

    return array_shift($result['values']);
  }
}
