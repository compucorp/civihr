<?php

  class HRJobContractFabricator {

    private static $default = [
      'sequential' => 1
    ];

    private static $defaultDetails = [
      'period_start_date' => '2016-01-01'
    ];

    public static function fabricate($params, $detailsParams) {
      if (!isset($params['contact_id'])) {
        throw new Exception('Specify contact_id value');
      }

      $contract = civicrm_api3('HRJobContract', 'create', array_merge(self::$default, $params))['values'][0];

      if (isset($detailsParams)) {
        self::fabricateDetails($contract['id'], $detailsParams);
      }

      return $contract;
    }

    private static function fabricateDetails($contractId, $params) {
      civicrm_api3('HRJobDetails', 'create', array_merge(['jobcontract_id' => $contractId], self::$defaultDetails, $params));
    }
  }
