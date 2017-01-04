<?php

trait CRM_HRUI_Upgrader_Steps_4701 {

  /**
   * Upgrader to :
   * 1) Set CiviHR theme by updating the custom CSS URL
   * 2) Sort Individual Prefixes alphabetically
   *
   * @return bool
   */
  public function upgrade_4701() {
    $this->up4701_setCustomCSSURL();
    $this->up4701_sortIndividualPrefixes();

    return TRUE;
  }

  private function up4701_setCustomCSSURL() {
    $bootstrapcivicrmDirectory = CRM_Core_Resources::singleton()->getPath('org.civicrm.bootstrapcivicrm', 'css/custom-civicrm.css');

    if (!empty($bootstrapcivicrmDirectory)) {
      civicrm_api3('Setting', 'create', [
        'customCSSURL' => $bootstrapcivicrmDirectory,
      ]);
    }
  }

  /**
   * Sorts Individual Prefixes alphabetically
   */
  private function up4701_sortIndividualPrefixes() {
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
      foreach($prefixes['values'] as $prefix) {
        civicrm_api3('OptionValue', 'create', [
          'id' => $prefix['id'],
          'weight' => $weight++
        ]);
      }
    }
  }

}