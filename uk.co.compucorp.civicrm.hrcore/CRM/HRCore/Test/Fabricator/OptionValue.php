<?php

class CRM_HRCore_Test_Fabricator_OptionValue {

  private static $defaultParams = [
    'name' => 'test option',
    'sequential' => 1
  ];

  public static function fabricate($groupName, $params = []) {
    $params['option_group_id'] = $groupName;
    $params['value'] = mt_rand(1000, 9000);

    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3(
      'OptionValue',
      'create',
      $params
    );

    return array_shift($result['values']);
  }
}
