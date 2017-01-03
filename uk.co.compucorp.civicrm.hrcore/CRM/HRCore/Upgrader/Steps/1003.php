<?php

trait CRM_HRCore_Upgrader_Steps_1003 {

  public function upgrade_1003() {
    // Updates Display Preferences settings by
    // hiding Tags and Groups from contact related screens.
    $this->disableSettingOptions('contact_view_options', ['tag', 'group']);

    // Updates Address Fields settings by
    // hiding Postal Code Suffix, County, Latitude and Longitude .
    $this->disableSettingOptions('address_options', ['postal_code_suffix', 'county', 'geo_code_1', 'geo_code_2']);

    return TRUE;
  }

  /**
   * Disables a set of civicrm setting values
   * (e.g contact view options, address fields.. etc ) based
   * on provided criteria.
   *
   * @param string $settingKey
   * @param array $fieldsToDisable
   */
  private function disableSettingOptions($settingKey, $fieldsToDisable) {
    $currentSettings = civicrm_api3('Setting', 'get', [
      'sequential' => 1,
      'return' => [$settingKey],
    ]);

    $settingOptions = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'return' => ['value'],
      'option_group_id' => $settingKey,
      'name' => ['IN' => $fieldsToDisable],
    ]);

    $toDeleteOptions = [];
    if (!empty($settingOptions['values'])) {
      $toDeleteOptions = array_column($settingOptions['values'], 'value');
    }

    $newOptions = [];
    if(!empty($currentSettings['values'][$settingKey])) {
      $currentOptions = $currentSettings['values'][$settingKey];

      foreach ($currentOptions as $option) {
        if (!in_array($option, $toDeleteOptions)) {
          $newOptions[] = $option;
        }
      }
    }

    civicrm_api3('Setting', 'create', [
      'sequential' => 1,
      $settingKey => $newOptions,
    ]);
  }

}