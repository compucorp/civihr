<?php

class CRM_HRCore_Test_Fabricator_CustomField {

  private static $defaultParams = [
    'name' => 'test_custom_field',
    'html_type' => 'Text',
    'data_type' => 'String',
  ];

  public static function fabricate($params = []) {
    $params = array_merge(self::$defaultParams, $params);

    if (!isset($params['label'])) {
      $params['label'] = $params['name'];
    }

    $result = civicrm_api3('CustomField', 'create', $params);

    return array_shift($result['values']);
  }

}
