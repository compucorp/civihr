<?php

trait CRM_HRCore_Upgrader_Steps_1024 {

  /**
   * Remove Social Account Options and reorder de remaining
   */
  public function upgrade_1024() {
    $this->up1024_removeSocialAccountOptions([
      'Work',
      'MySpace',
      'Vine',
      'Google+',
      'Snapchat',
      'Tumblr',
    ]);
    $this->up1024_reorderSocialAccountOptions([
      'LinkedIn',
      'Twitter',
      'Facebook',
    ]);

    return TRUE;
  }

  /**
   * Removes Some Social Account Options
   */
  private function up1024_removeSocialAccountOptions($accounts) {
    $socialAccounts = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'website_type',
      'name' => ['IN' => $accounts],
    ]);

    $socialAccounts = $socialAccounts['values'];
    foreach ($socialAccounts as $optionValueId => $optionValue) {
      civicrm_api3('OptionValue', 'delete', [
        'id' => $optionValueId,
      ]);
    }
  }

  /**
   * Reorder Social Accounts
   */
  private function up1024_reorderSocialAccountOptions($accounts) {
    $socialAccounts = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'website_type',
      'name' => ['IN' => $accounts],
    ]);

    $socialAccounts = $socialAccounts['values'];
    foreach ($socialAccounts as $optionValueId => $optionValue) {
      switch ($optionValue['name']) {
        case 'LinkedIn':
          $newWeight = 1;
          break;

        case 'Twitter':
          $newWeight = 2;
          break;

        case 'Facebook':
          $newWeight = 3;
          break;
      }
      civicrm_api3('OptionValue', 'create', [
        'id' => $optionValueId,
        'weight' => $newWeight,
      ]);
    }
  }

}
