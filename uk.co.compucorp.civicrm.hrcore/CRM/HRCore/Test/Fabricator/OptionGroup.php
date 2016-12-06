<?php

class CRM_HRCore_Test_Fabricator_OptionGroup {

  private static $defaultParams = [
    'name' => 'test_option_group',
    'title' => 'test option group',
    'is_active' => 1,
    'sequential' => 1
  ];

  public static function fabricate($params = []) {

    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3('OptionGroup', 'create', $params);

    return array_shift($result['values']);
  }
}
