<?php

trait CRM_HRCore_Upgrader_Steps_1002 {

  public function upgrade_1002() {
    $this->createMissIndividualPrefix();
    $this->sortIndividualPrefixes();
    $this->createFriendOfRelationshipType();

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
    // fetch all prefixes sorted by alphabetically ( by their labels )
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

  private function createFriendOfRelationshipType() {
    civicrm_api3('RelationshipType', 'create', array(
      'sequential' => 1,
      'name_a_b' => "Friend of",
      'name_b_a' => "Friend of",
      'contact_type_a' => "Individual",
      'contact_type_b' => "Individual",
      'label_a_b' => "Friend of",
      'label_b_a' => "Friend of",
    ));
  }

}