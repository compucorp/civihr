<?php

class CRM_HRCore_Test_Fabricator_UFMatch {

  private static $defaultParams = [
    'uf_id' => 999,
    'uf_name' => 'johndoe@test.com',
    'contact_id' => "user_contact_id",
  ];

  public static function fabricate($params = []) {
    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3(
      'UFMatch',
      'create',
      $params
    );

    return array_shift($result['values']);
  }
}
