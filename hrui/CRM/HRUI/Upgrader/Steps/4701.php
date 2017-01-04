<?php

trait CRM_HRUI_Upgrader_Steps_4701 {

  /**
   * Upgrader to :
   * 1) Set CiviHR theme by updating the custom CSS URL
   * 2) Sort Individual Prefixes alphabetically
   * 3) Rename website to 'Social account'
   *
   * @return bool
   */
  public function upgrade_4701() {
    $this->up4701_setCustomCSSURL();
    $this->up4701_sortIndividualPrefixes();
    $this->up4701_moveSkypeTop();
    $this->up4701_websiteToSocialAccountReplacement();

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

  /**
   * Moves Skype option value to the top
   * of IM list
   */
  private function up4701_moveSkypeTop() {
    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => 'instant_messenger_service',
      'name' => 'Skype',
      'weight' => 0,
    ]);
  }

  /**
   * Replaces 'Website Type' option group label by 'Social Account Type'
   * and adds word replacement for 'Website', 'Website Type' and 'Add another website'
   */
  private function up4701_websiteToSocialAccountReplacement() {
    civicrm_api3('OptionGroup', 'get', [
      'name' => "website_type",
      'api.OptionGroup.create' => ['id' => '$value.id', 'title' => 'Social Account Type'],
    ]);

    $wordsToReplace = [
      ['Website', 'Social Account'],
      ['Website Type', 'Social Account Type'],
      ['Add another website', 'Add another social account'],
    ];

    foreach ($wordsToReplace as $word) {
      civicrm_api3('WordReplacement', 'create', [
        'find_word' => $word[0],
        'replace_word' => $word[1],
      ]);
    }
  }

}
