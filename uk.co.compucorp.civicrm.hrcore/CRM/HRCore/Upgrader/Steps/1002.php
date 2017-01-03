<?php

trait CRM_HRCore_Upgrader_Steps_1002 {

  public function upgrade_1002() {
    $this->createMissIndividualPrefix();
    $this->sortIndividualPrefixes();

    return TRUE;
  }

  /**
   * Create 'Miss.' Individual Prefix
   */
  private function createMissIndividualPrefix() {
    $missPrefix = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'individual_prefix',
      'name' => 'Miss.',
      'options' => ['limit' => 0],
    ]);

    if (empty($missPrefix['id'])) {
      civicrm_api3('OptionValue', 'create', [
        'option_group_id' => 'individual_prefix',
        'name' => 'Miss.',
        'label' => 'Miss.',
      ]);
    }
  }

  /**
   * Sort Individual Prefixes alphabetically
   */
  private function sortIndividualPrefixes() {
    // fetch all prefixes sorted alphabetically ( by their labels )
    $prefixes = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'return' => ['id'],
      'option_group_id' => 'individual_prefix',
      'options' => ['limit' => 0, 'sort' => 'label asc']
    ]);

    // update the prefixes weight
    $weight = 1;
    if (!empty($prefixes['values'])) {
      foreach($prefixes['values'] as $prefix) {
        civicrm_api3('OptionValue', 'create', [
          'id' => $prefix['id'],
          'weight' => $weight++
        ]);
      }
    }
  }

}