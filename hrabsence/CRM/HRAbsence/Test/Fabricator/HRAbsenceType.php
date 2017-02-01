<?php

class CRM_HRAbsence_Test_Fabricator_HRAbsenceType {
  /**
   * Array of default parameters
   * @var array 
   */
  private static $defaultParams = [
    'is_active' => 1,
  ];
  
  /**
   * Helper method that builds parameters array combining given parameters with
   * default parameters.
   * 
   * @param arry $params
   *   Given array of parameters
   * @return array
   *   Result of merging default parameters with given parameters
   */
  private static function buildParamsArray($params) {
    if(empty($params['title'])) {
      $params['title'] = 'Absence Type ' . microtime();
    }

    if(empty($params['name'])) {
      $params['name'] = 'Absence Type ' . microtime();
    }

    return array_merge(self::$defaultParams, $params);
  }
  
  /**
   * Absence Type fabricator method, uses BAO directly
   * 
   * @param array $params
   *   Parameters to be passed to BAO to create absence type
   * @return CRM_HRAbsence_BAO_HRAbsenceType
   */
  public static function fabricate($params = []) {
    return CRM_HRAbsence_BAO_HRAbsenceType::create(self::buildParamsArray($params));
  }
  
  /**
   * Absence Type fabricator method that uses API and returns values of created
   * record.
   * 
   * @param array $params
   *   Parameters to be passed to API call to create absence type
   * @return array
   *   Values of created absence type
   */
  public static function fabricateUsingAPI($params = []) {
    $params['sequential'] = 1;
    $result = civicrm_api3('HRAbsenceType', 'create', self::buildParamsArray($params));
    return array_shift($result['values']);
  }

}
