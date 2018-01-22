<?php

class CRM_HRCore_Test_Fabricator_ContactType {

  private static $defaultParams = [
    'name' => 'TestType',
    'parent_id' => 'Individual',
  ];

  public static function fabricate($params = []) {
    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3(
      'ContactType',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

}
