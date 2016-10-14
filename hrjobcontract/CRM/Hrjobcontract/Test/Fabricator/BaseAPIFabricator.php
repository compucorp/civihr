<?php

abstract class CRM_Hrjobcontract_Test_Fabricator_BaseAPIFabricator {

  protected static $defaultParams = [
    'sequential' => 1
  ];

  protected static function getEntityName() {
    return '';
  }

  public static function fabricate($params) {
    if (!isset($params['jobcontract_id'])) {
      throw new Exception('Specify jobcontract_id value');
    }

    $result = civicrm_api3(
      static::getEntityName(),
      'create',
      array_merge(self::$defaultParams, $params)
    );

    return $result['values'][0];
  }
}
