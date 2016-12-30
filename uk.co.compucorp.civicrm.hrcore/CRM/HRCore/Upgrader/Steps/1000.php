<?php

trait CRM_HRCore_Upgrader_Steps_1000 {

  public function upgrade_1000() {
    $this->downloadUKEnglish();
    $this->updateLocalisationSettings();
    $this->setCiviHRTheme();

    return TRUE;
  }

  /**
   * Downloads en_GB (UK english) localization file
   */
  private function downloadUKEnglish() {
    $localizationFileURL = "https://download.civicrm.org/civicrm-l10n-core/mo/en_GB/civicrm.mo";

    global $civicrm_root;
    $downloadPath = "{$civicrm_root}/l10n/en_GB/LC_MESSAGES/";

    if (!is_dir($downloadPath)) {
      mkdir($downloadPath, 0755, true);
    }

    file_put_contents($downloadPath, fopen($localizationFileURL, 'r'));
  }

  /**
   * Updates the default localization settings which includes :
   *   1- setting the default currency to GBP and adding it to
   *      enabled currencies list.
   *   2- setting the default date formats
   *   3- setting the default country to UK
   *   4- setting the system langagun to UK english (en_GB)
   */
  private function updateLocalisationSettings() {
    $settings = [
      'defaultCurrency' => 'GBP',
      'dateformatDatetime' => '%d/%m/%Y %l:%M %P',
      'dateformatFull' => '%d/%m/%Y',
      'dateformatFinancialBatch' => '%d/%m/%Y',
      'dateInputFormat' => 'dd/mm/yy',
      'lcMessages' => 'en_GB',
    ];

    // Get UK ID
    $ukCountry = civicrm_api3('Country', 'get', [
      'return' => array("id"),
      'iso_code' => "GB",
      'options' => ['limit' => 1],
    ]);
    if (!empty($ukCountry['id'])) {
      $settings['defaultContactCountry'] = $ukCountry['id'];
    }

    civicrm_api3('Setting', 'create', $settings);

    // Adds GBP to enabled currencies and sets it as a default one
    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => "currencies_enabled",
      'label' => "GBP (£)",
      'value' => "GBP",
      'is_default' => 1,
      'is_active' => 1,
    ]);
  }

  /**
   * Sets CiviHR theme by updating the custom CSS URL
   */
  private function setCiviHRTheme() {
    civicrm_api3('Setting', 'create', [
      'customCSSURL' => '[civicrm.root]/tools/extensions/civihr/org.civicrm.bootstrapcivicrm/css/custom-civicrm.css',
    ]);
  }

}