<?php

class CRM_HRCore_Test_Fabricator_CustomGroup {

  private static $defaultParams = [
    'name' => 'test_custom_group',
    'title' => 'Test Custom Group',
    'extends' => 'Individual'
  ];

  public static function fabricate($params = []) {
    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3(
      'CustomGroup',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

}
