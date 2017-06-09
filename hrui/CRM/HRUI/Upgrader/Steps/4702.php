<?php

trait CRM_HRUI_Upgrader_Steps_4702 {

  /**
   * Upgrader to :
   * 1) Set CiviHR theme by updating the custom CSS URL
   * 2) Move Skype option to top of messenger services option group
   * 3) Rename 'website' to 'Social account'
   *
   * @return bool
   */
  public function upgrade_4702() {
    $this->up4702_setCustomCSSURL();
    $this->up4702_moveSkypeTop();
    $this->up4702_websiteToSocialAccountReplacement();

    return TRUE;
  }

  /**
   * Sets CiviHR theme by updating the custom CSS URL
   */
  private function up4702_setCustomCSSURL() {
    $customCSSPath = CRM_Core_Resources::singleton()
      ->getUrl('org.civicrm.shoreditch', 'css/custom-civicrm.css');

    if (!empty($customCSSPath)) {
      civicrm_api3('Setting', 'create', [
        'customCSSURL' => $customCSSPath,
      ]);
    }
  }

  /**
   * Moves Skype option value to the top of IM list
   */
  private function up4702_moveSkypeTop() {
    civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'instant_messenger_service',
      'name' => 'Skype',
      'api.OptionValue.create' => [
        'id' => '$value.id',
        'weight' => 0,
      ],
    ]);
  }

  /**
   * Replaces 'Website Type' option group label by 'Social Account Type'
   * and adds word replacement for 'Website', 'Website Type' and 'Add another website'
   */
  private function up4702_websiteToSocialAccountReplacement() {
    civicrm_api3('OptionGroup', 'get', [
      'name' => 'website_type',
      'api.OptionGroup.create' => [
        'id' => '$value.id',
        'title' => 'Social Account Type'
      ],
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
