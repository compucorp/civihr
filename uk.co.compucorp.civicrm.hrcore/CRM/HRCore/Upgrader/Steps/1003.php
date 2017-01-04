<?php

trait CRM_HRCore_Upgrader_Steps_1003 {

  /**
   * Upgrader to set default viewing configurations
   * for Tags, Groups and Address fields.
   *
   * @return bool
   */
  public function upgrade_1003() {
    // hiding Tags and Groups from contact related screens.
    $this->up1003_disableSettingOptions('contact_view_options', ['tag', 'group']);

    // hiding Postal Code Suffix, County, Latitude and Longitude from address.
    $this->up1003_disableSettingOptions('address_options', ['postal_code_suffix', 'county', 'geo_code_1', 'geo_code_2']);

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
  private function up1003_disableSettingOptions($settingKey, $fieldsToDisable) {
    // fetch current settings
    $currentSettings = civicrm_api3('Setting', 'get', [
      'sequential' => 1,
      'return' => [$settingKey],
      'options' => ['limit' => 1],
    ]);

    // fetch required setting option values
    $settingOptions = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'return' => ['value'],
      'option_group_id' => $settingKey,
      'name' => ['IN' => $fieldsToDisable],
    ]);

    // put all options to uncheck ( delete ) in array
    $toDeleteOptions = [];
    if (!empty($settingOptions['values'])) {
      $toDeleteOptions = array_column($settingOptions['values'], 'value');
    }

    // put all current checked settings except the ones we
    // want to remove in an array
    $newOptions = [];
    if(!empty($currentSettings['values'][0][$settingKey])) {
      $currentOptions = $currentSettings['values'][0][$settingKey];

      foreach ($currentOptions as $option) {
        if (!in_array($option, $toDeleteOptions)) {
          $newOptions[] = $option;
        }
      }
    }

    // update the required settings
    if (!empty($newOptions)) {
      civicrm_api3('Setting', 'create', [
        'sequential' => 1,
        $settingKey => $newOptions,
      ]);
    }
  }

}