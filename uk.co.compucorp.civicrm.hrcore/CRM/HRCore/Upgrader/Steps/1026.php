<?php

trait CRM_HRCore_Upgrader_Steps_1026 {

  /**
   * Set Default Gender Options
   */
  public function upgrade_1026() {
    $this->up1026_setDefaultGenderOptions();
    $this->up1026_disableDefaultGenderOptions('Prefer not to say');

    return TRUE;
  }

  /**
   * Changes The Order of Individual Prefixes
   */
  private function up1026_setDefaultGenderOptions() {
    $optionValues = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'gender',
    ]);

    $optionValues = $optionValues['values'];
    foreach ($optionValues as $optionValueId => $optionValue) {
      switch ($optionValue['name']) {
        case 'Male':
          $newWeight = 1;
          break;

        case 'Female':
          $newWeight = 2;
          break;

        case 'Other':
          $newWeight = 3;
          break;
      }
      civicrm_api3('OptionValue', 'create', [
        'id' => $optionValueId,
        'weight' => $newWeight,
      ]);
    }
  }

  private function up1026_disableDefaultGenderOptions($genderOption) {
    civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'gender',
      'name' => $genderOption,
      'api.OptionValue.create' => ['id' => '$value.id', 'is_active' => 0],
    ]);
  }

}
