<?php

trait CRM_HRCore_Upgrader_Steps_1002 {

  /**
   * Upgrader to create 'Miss' Individual prefix
   *
   * @return bool
   */
  public function upgrade_1002() {
    $missPrefix = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'individual_prefix',
      'name' => 'Miss.',
      'options' => ['limit' => 1],
    ]);

    if (empty($missPrefix['id'])) {
      civicrm_api3('OptionValue', 'create', [
        'option_group_id' => 'individual_prefix',
        'name' => 'Miss.',
        'label' => 'Miss.',
      ]);
    }

    return TRUE;
  }

}