<?php

trait CRM_HRCore_Upgrader_Steps_1025 {

  /**
   * Removes Instant Messenger options
   */
  public function upgrade_1025() {
    $this->up1025_removeInstantMessengerOptions([
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
  private function up1025_removeInstantMessengerOptions($accounts) {
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
        civicrm_api3('OptionValue', 'delete', [
          'id' => $optionValueId,
        ]);
      }
      else {
        civicrm_api3('OptionValue', 'create', [
          'id' => $optionValueId,
          'is_active' => 0,
        ]);
      }
    }
  }

}
