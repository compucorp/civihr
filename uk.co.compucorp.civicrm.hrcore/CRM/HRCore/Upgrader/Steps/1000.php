<?php

/**
 * Class CRM_HRCore_Upgrader_Steps_1000
 */
trait CRM_HRCore_Upgrader_Steps_1000 {

  /**
   * Upgrader to set default Localisation related Settings
   *
   * @return bool
   */
  public function upgrade_1000() {
    $this->up1000_downloadUKEnglish();
    $this->up1000_updateLocalisationSettings();
    $this->up1000_setAvailableCountries();
    $this->up1000_setAvailableProvinces();

    return TRUE;
  }

  /**
   * Downloads en_GB (UK english) localization file
   *
   * @return boolean|null
   */
  private function up1000_downloadUKEnglish() {
    $localizationURL = "https://download.civicrm.org/civicrm-l10n-core/mo/en_GB/civicrm.mo";

    global $civicrm_root;
    $downloadPath = "{$civicrm_root}/l10n/en_GB/LC_MESSAGES/";

    if (file_exists("{$downloadPath}civicrm.mo")) {
      return null;
    }

    if (!is_dir($downloadPath)) {
      mkdir($downloadPath, 0755, true);
    }

    file_put_contents($downloadPath, fopen($localizationURL, 'r'));

    return TRUE;
  }

  /**
   * Updates the default localization settings which includes :
   *   1- setting the default currency to GBP and adding it to
   *      enabled currencies list.
   *   2- setting the default date formats
   *   3- setting the default country to UK
   *   4- setting the system language to UK english (en_GB)
   */
  private function up1000_updateLocalisationSettings() {
    $settings = [
      'defaultCurrency' => 'GBP',
      'dateformatDatetime' => '%d/%m/%Y %l:%M %P',
      'dateformatFull' => '%d/%m/%Y',
      'dateformatFinancialBatch' => '%d/%m/%Y',
      'dateInputFormat' => 'dd/mm/yy',
      'lcMessages' => 'en_GB',
    ];

    // Get UK Country ID
    $ukCountry = civicrm_api3('Country', 'get', [
      'return' => ['id'],
      'iso_code' => 'GB',
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
        'option_group_id' => 'currencies_enabled',
        'label' => $currency[0],
        'value' => $currency[1],
        'is_default' => $currency[2],
        'is_active' => 1,
      ]);
    }
  }

  /**
   * Sets Available Countries to 'all countries'
   */
  private function up1000_setAvailableCountries() {
    $countriesList = civicrm_api3('Country', 'get',[
      'sequential' => 1,
      'return' => ['id'],
      'options' => ['limit' => 0],
    ]);

    if (!empty($countriesList['values'])) {
      $countriesIDs = array_column($countriesList['values'], 'id');
      unset($countriesList);

      civicrm_api3('Setting', 'create', [
        'countryLimit' => $countriesIDs,
      ]);
    }
  }

  /**
   * Sets Available Provinces to 'all provinces'
   */
  private function up1000_setAvailableProvinces() {
    // ToDo : Fetch Provinces via API after upgrading to newer civicrm version since it is not available on civicrm 4.7.9
    $tableName = CRM_Core_DAO_StateProvince::getTableName();
    $query = CRM_Core_DAO::executeQuery("SELECT id FROM {$tableName}");

    $provincesIDs = [];
    while($query->fetch()) {
      $provincesIDs[] = $query->id;
    }

    if (!empty($provincesIDs)) {
      civicrm_api3('Setting', 'create', [
        'provinceLimit' => $provincesIDs,
      ]);
    }
  }

}