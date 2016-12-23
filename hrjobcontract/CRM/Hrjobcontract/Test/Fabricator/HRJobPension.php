<?php

use CRM_Hrjobcontract_Test_Fabricator_BaseAPIFabricator as BaseAPIFabricator;

class CRM_Hrjobcontract_Test_Fabricator_HRJobPension extends BaseAPIFabricator {
  
  /**
   * Fabricates Pension entity, using given parameters.  If pension type does not 
   * exist, new option value is created to be used as pension type.
   * 
   * @param array $params
   *   Array of values to be used on creation of Pension for given contract
   */
  public static function fabricate($params) {
    if (!empty($params['pension_type'])) {
      $pensionTypeResult = civicrm_api3('OptionValue', 'get', [
        'sequential' => 1,
        'option_group_id' => 'hrjc_pension_type',
        'value' => $params['pension_type'],
      ]);
      
      if ($pensionTypeResult['count'] == 0) {
        self::createPensionType($params['pension_type']);
      }
    }
    
    return parent::fabricate($params);
  }
  
  private static function createPensionType($value) {
    $result = civicrm_api3('OptionValue', 'create', [
      'value' => $value, 
      'name' => 'Test Pension Type ' . mt_rand(1000, 9000), 
      'option_group_id' => 'hrjc_pension_type'
    ]);
    return array_shift($result['values']);
  }
}
