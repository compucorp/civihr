<?php

class CRM_HRCore_Test_Fabricator_Case {

  private static $defaultParams = [
    'subject' => 'test test',
    'case_type_id' => 'test_case_type',
    'contact_id' => 1,
    'creator_id' => 1,
  ];

  public static function fabricate($params = []) {
    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3(
      'Case',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

}
