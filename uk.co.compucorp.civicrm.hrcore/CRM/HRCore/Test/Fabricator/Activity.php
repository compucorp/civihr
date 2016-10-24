<?php

class CRM_HRCore_Test_Fabricator_Activity {

  private static $defaultParams = [
    'subject' => "test activity",
    'activity_type_id' => "Open Case",
    'source_contact_id' => 1,
  ];

  public static function fabricate($params = []) {
    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3(
      'Activity',
      'create',
      $params
    );

    return array_shift($result['values']);
  }
}
