<?php

trait CRM_HRCore_Upgrader_Steps_1028 {

  /**
   * Removes Instant Messenger options
   *
   * @return bool
   */
  public function upgrade_1028() {
    $this->up1028_deleteOrDisableSocialAccounts([
      'Yahoo',
      'MSN',
      'AIM',
      'Jabber',
    ]);

    return TRUE;
  }

  /**
   * Removes some instant messenger options if not used, and disable it if used
   */
  private function up1028_deleteOrDisableSocialAccounts($accounts) {
    $socialAccounts = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'instant_messenger_service',
      'name' => ['IN' => $accounts],
    ]);

    $socialAccounts = $socialAccounts['values'];
    foreach ($socialAccounts as $optionValueId => $optionValue) {
      $socialAccountIsUsed = civicrm_api3('Im', 'get', [
        'provider_id' => $optionValue['name'],
      ]);
      if ($socialAccountIsUsed['count'] == 0) {
        $this->up1028_deleteSocialAccount($optionValueId);
      }
      else {
        $this->up1028_disableSocialAccount($optionValueId);
      }
    }
  }

  /**
   * @param int $optionValueId
   */
  private function up1028_deleteSocialAccount($optionValueId) {
    civicrm_api3('OptionValue', 'delete', [
      'id' => $optionValueId,
    ]);
  }

  /**
   * @param int $optionValueId
   */
  private function up1028_disableSocialAccount($optionValueId) {
    civicrm_api3('OptionValue', 'create', [
      'id' => $optionValueId,
      'is_active' => 0,
    ]);
  }

}
