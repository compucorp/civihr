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
      $pensionTypeResult = civicrm_api3('Contact', 'get', [
        'sequential' => 1,
        'contact_type' => 'Organization',
        'contact_sub_type' => 'Pension_Provider',
        'organization_name' => $params['pension_type'],
      ]);

      if ($pensionTypeResult['count'] == 0) {
        $pensionTypeResult = self::createPensionType($params['pension_type']);
      }
      
      $params['pension_type'] = $pensionTypeResult['id'];
    }

    return parent::fabricate($params);
  }
  
  /**
   * Creates a new pension provider.
   * 
   * @param string $value
   *   Organization name.
   * 
   * @return array
   *   Result of creating new provider.
   */
  private static function createPensionType($value) {
    $result = civicrm_api3('Contact', 'create', [
      'contact_type' => 'Organization',
      'contact_sub_type' => 'pension_provider',
      'organization_name' => $value,
    ]);

    return array_shift($result['values']);
  }
}
