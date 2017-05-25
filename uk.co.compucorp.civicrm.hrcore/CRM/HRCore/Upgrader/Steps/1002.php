<?php

trait CRM_HRCore_Upgrader_Steps_1002 {

  /**
   * Upgrader to create 'Miss' Individual prefix
   *
   * @return bool
   */
  public function upgrade_1002() {

    $this->up1002_createMissPrefix();
    $this->up1002_sortIndividualPrefixes();

    return TRUE;
  }

  /**
   * Creates Miss Prefix if it doesn't exist.  If it finds duplicates, it
   * deletes them.
   */
  private function up1002_createMissPrefix() {
    $missPrefix = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => 'individual_prefix',
      'name' => 'Miss',
      'options' => ['limit' => 0],
    ]);

    if ($missPrefix['count'] == 0) {
      civicrm_api3('OptionValue', 'create', [
        'option_group_id' => 'individual_prefix',
        'name' => 'Miss',
        'label' => 'Miss',
      ]);
    }
    elseif ($missPrefix['count'] > 1) {
      // Skip value at position 0, delete the rest.
      for ($i = 1; $i < $missPrefix['count']; $i++) {
        civicrm_api3('OptionValue', 'delete', [
          'id' => $missPrefix['values'][$i]['id']
        ]);
      }
    }
  }

  /**
   * Sorts Individual Prefixes alphabetically
   */
  private function up1002_sortIndividualPrefixes() {
    // fetch all prefixes sorted alphabetically ( by their labels )
    // hence ('sort' => 'label asc').
    $prefixes = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'return' => ['id'],
      'option_group_id' => 'individual_prefix',
      'options' => ['limit' => 0, 'sort' => 'label asc']
    ]);

    // update prefix weights
    $weight = 1;
    if (!empty($prefixes['values'])) {
      foreach ($prefixes['values'] as $prefix) {
        civicrm_api3('OptionValue', 'create', [
          'id' => $prefix['id'],
          'weight' => $weight++
        ]);
      }
    }
  }
}
