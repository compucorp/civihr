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
      $pension = CRM_Core_BAO_OptionValue::ensureOptionValueExists([
        'option_group_id' => 'hrjc_pension_provider',
        'name' => $params['pension_type'],
        'label' => $params['pension_type'],
        'is_active' => TRUE,
      ]);

      $params['pension_type'] = $pension['value'];
    }

    return parent::fabricate($params);
  }
}
