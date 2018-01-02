<?php

use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;

class CRM_Hrjobroles_Test_Helper_OptionValuesHelper {

  /**
   * Creates Option Values used by this extension's tests
   */
  public static function createSampleOptionGroupsAndValues()  {
    $optionGroupsValuesList = [
      'hrjc_location' => 'amman',
      'hrjc_region' => 'south amman',
      'hrjc_department' => 'amman devs',
      'hrjc_level_type' => 'guru',
      'cost_centres' => 'abdali'
    ];

    foreach($optionGroupsValuesList as $optionGroup => $optionValue) {
      OptionValueFabricator::fabricate([
        'option_group_id' => $optionGroup,
        'name' => $optionValue,
        'value' => $optionValue
      ]);
    }
  }
}
