<?php

trait CRM_HRCore_Upgrader_Steps_1000 {

  public function upgrade_1000() {
    $this->downloadUKEnglish();
    $this->updateLocalisationSettings();
    $this->setAvailableCountries();
    $this->setAvailableProvinces();
    $this->setCiviHRTheme();

    return TRUE;
  }

  /**
   * Downloads en_GB (UK english) localization file
   */
  private function downloadUKEnglish() {
    $localizationURL = "https://download.civicrm.org/civicrm-l10n-core/mo/en_GB/civicrm.mo";

    global $civicrm_root;
    $downloadPath = "{$civicrm_root}/l10n/en_GB/LC_MESSAGES/";

    if (!is_dir($downloadPath)) {
      mkdir($downloadPath, 0755, true);
    }

    file_put_contents($downloadPath, fopen($localizationURL, 'r'));
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

    $currenciesToEnable = [
      ['GBP (£)','GBP', 1],
      ['EUR (€)','EUR', 0],
    ];

    foreach ($currenciesToEnable as $currency) {
      civicrm_api3('OptionValue', 'create', [
        'option_group_id' => "currencies_enabled",
        'label' => $currency[0],
        'value' => $currency[1],
        'is_default' => $currency[2],
        'is_active' => 1,
      ]);
    }
  }

  /**
   * Sets Available Countries
   */
  private function setAvailableCountries() {
    $countriesList = civicrm_api3('Country', 'get', array(
      'sequential' => 1,
      'return' => array("id"),
      'options' => array('limit' => 0),
    ));

    $countriesIDs = array_column($countriesList['values'], 'id');
    unset($countriesList);

    civicrm_api3('Setting', 'create', array(
      'countryLimit' => $countriesIDs,
    ));
  }

  /**
   * Sets Available Provinces
   */
  private function setAvailableProvinces() {
    $tableName = CRM_Core_DAO_StateProvince::getTableName();

    $provincesIDs = [];
    $query = CRM_Core_DAO::executeQuery("SELECT id FROM {$tableName}");

    while($query->fetch()) {
      $provincesIDs[] = $query->id;
    }

    civicrm_api3('Setting', 'create', array(
      'provinceLimit' => $provincesIDs,
    ));
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