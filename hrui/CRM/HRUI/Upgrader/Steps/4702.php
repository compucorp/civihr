<?php

trait CRM_HRUI_Upgrader_Steps_4702 {

  /**
   * Upgrader to :
   * 1) Set CiviHR theme by updating the custom CSS URL
   * 2) Sort Individual Prefixes alphabetically
   * 3) Rename website to 'Social account'
   *
   * @return bool
   */
  public function upgrade_4702() {
    $this->up4702_setCustomCSSURL();
    $this->up4702_sortIndividualPrefixes();
    $this->up4702_moveSkypeTop();
    $this->up4702_websiteToSocialAccountReplacement();

    return TRUE;
  }

  private function up4702_setCustomCSSURL() {
    $customCSSPath = CRM_Core_Resources::singleton()->getUrl('org.civicrm.shoreditch', 'css/custom-civicrm.css');

    if (!empty($customCSSPath)) {
      civicrm_api3('Setting', 'create', [
        'customCSSURL' => $customCSSPath,
      ]);
    }
  }

  /**
   * Sorts Individual Prefixes alphabetically
   */
  private function up4702_sortIndividualPrefixes() {
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
   * Moves Skype option value to the top of IM list
   */
  private function up4702_moveSkypeTop() {
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
  private function up4702_websiteToSocialAccountReplacement() {
    civicrm_api3('OptionGroup', 'get', [
      'name' => 'website_type',
      'api.OptionGroup.create' => ['id' => '$value.id', 'title' => 'Social Account Type'],
    ]);

    $wordsToReplace = [
      ['Website', 'Social Account'],
      ['Website Type', 'Social Account Type'],
      ['Add another website', 'Add another social account']
    ];

    foreach ($wordsToReplace as $word) {
      civicrm_api3('WordReplacement', 'create', [
        'find_word' => $word[0],
        'replace_word' => $word[1]
      ]);
    }
  }

}
