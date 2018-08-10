<?php

trait CRM_HRCore_Upgrader_Steps_1026 {

  /**
   * Changes the Order Of Individual Prefix
   */
  public function upgrade_1026() {
    $this->up1026_changeOrderOfIndividualPrefix();

    return TRUE;
  }

  /**
   * Changes The Order of Individual Prefixes
   */
  private function up1026_changeOrderOfIndividualPrefix() {
    $optionValues = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'individual_prefix',
    ]);

    $optionValues = $optionValues['values'];
    foreach ($optionValues as $optionValueId => $optionValue) {
      switch ($optionValue['name']) {
        case 'Mr.':
          $newWeight = 1;
          break;

        case 'Mrs.':
          $newWeight = 2;
          break;

        case 'Ms.':
          $newWeight = 3;
          break;

        case 'Miss':
          $newWeight = 4;
          break;

        case 'Dr.':
          $newWeight = 5;
          break;
      }
      civicrm_api3('OptionValue', 'create', [
        'id' => $optionValueId,
        'weight' => $newWeight,
      ]);
    }
  }

}
