<?php

  class HRJobPayFabricator {

    private static $default = [
      'sequential' => 1
    ];

    public static function fabricate($params) {
      if (!isset($params['jobcontract_id'])) {
        throw new Exception('Specify jobcontract_id value');
      }

      return civicrm_api3('HRJobPay', 'create', array_merge(self::$default, $params))['values'][0];
    }
  }
