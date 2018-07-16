<?php

trait CRM_HRCore_Upgrader_Steps_1028 {

  /**
   * Relabel Line Manager Relationship
   */
  public function upgrade_1028() {
    $this->up1028_uncheckSomeContactEditingOptions();

    return TRUE;
  }

  /**
   * ReLabeling Line Manager Relationship
   */
  private function up1028_uncheckSomeContactEditingOptions() {
    $contactEditOptions = Civi::settings()->get('contact_edit_options');
    $contactEditOptions = explode(CRM_CORE_DAO::VALUE_SEPARATOR, $contactEditOptions);

    $contactEditOptions = $this->up1028_uncheckSuffix($contactEditOptions, 17);
    $contactEditOptions = $this->up1028_uncheckInstantMessenger($contactEditOptions, 9);
    $contactEditOptions = $this->up1028_uncheckSocialAccounts($contactEditOptions, 11);

    $finalEditionOptions = implode(CRM_CORE_DAO::VALUE_SEPARATOR, $contactEditOptions);

    Civi::settings()->set('contact_edit_options', $finalEditionOptions);
  }

  private function up1028_uncheckSuffix(&$contactEditingOptions, $settingNumber) {
    $contacts = $result = civicrm_api3('Contact', 'get', [
      'suffix_id' => ['IS NOT NULL' => 1],
    ]);
    if ($contacts['count'] != 0) {
      return $contactEditingOptions;
    }

    return array_diff($contactEditingOptions, [$settingNumber]);

  }

  private function up1028_uncheckInstantMessenger(&$contactEditingOptions, $settingNumber) {
    $result = civicrm_api3('Im', 'get', [
    ]);
    if ($result['count'] != 0) {
      return $contactEditingOptions;
    }

    return array_diff($contactEditingOptions, [$settingNumber]);
  }

  private function up1028_uncheckSocialAccounts(&$contactEditingOptions, $settingNumber) {
    $result = civicrm_api3('Website', 'get', [
    ]);
    if ($result['count'] != 0) {
      return $contactEditingOptions;
    }
    return array_diff($contactEditingOptions, [$settingNumber]);

  }


}
