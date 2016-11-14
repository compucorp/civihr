<?php

class CRM_Hrjobcontract_Test_Fabricator_HRJobContract {

  private static $defaultParams = [
    'sequential' => 1
  ];

  private static $defaultDetailsParams = [
    'period_start_date' => '2016-01-01'
  ];

  public static function fabricate($params, $detailsParams = null) {
    $contract = self::fabricateContract($params);

    if (!empty($detailsParams)) {
      self::fabricateDetails($contract['id'], $detailsParams);
    }

    return $contract;
  }

  private static function fabricateContract($params) {
    if (!isset($params['contact_id'])) {
      throw new Exception('Specify contact_id value');
    }

    $result = civicrm_api3(
      'HRJobContract',
      'create',
      array_merge(self::$defaultParams, $params)
    );

    return array_shift($result['values']);
  }

  private static function fabricateDetails($contractId, $params) {
    civicrm_api3(
      'HRJobDetails',
      'create',
      array_merge(
        ['jobcontract_id' => $contractId],
        self::$defaultDetailsParams,
        $params
      )
    );
  }
}
