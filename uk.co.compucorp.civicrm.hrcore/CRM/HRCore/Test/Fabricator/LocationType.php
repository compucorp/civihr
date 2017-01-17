<?php

class CRM_HRCore_Test_Fabricator_LocationType {

  private static $defaultParams = [
    'name' => 'test location type',
    'display_name' => 'test location type',
    'is_active'   => 1
  ];

  public static function fabricate($params =[]) {
    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3(
      'LocationType',
      'create',
      $params
    );

    return array_shift($result['values']);
  }
}
