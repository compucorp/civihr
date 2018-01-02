<?php

use CRM_Hrjobroles_BAO_HrJobRoles as HrJobRoles;

class CRM_Hrjobroles_Test_Fabricator_HrJobRoles {

  protected static $defaultParams = [
    'sequential' => 1
  ];

  /**
   * @param array $params
   *  An array of params that will be passed to the civicrm API
   *
   * @return array
   *  The entity values as they are returned by the API call
   *
   * @throws \Exception
   */
  public static function fabricate($params) {
    $params = array_merge(self::$defaultParams, $params);
    return HrJobRoles::create($params);
  }
}
