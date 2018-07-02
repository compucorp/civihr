<?php

trait CRM_HRCore_Upgrader_Steps_1024 {

  /**
   * Removes social account options and reorder the remaining
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
   * Removes some social account options if not used, and disable it if used
   */
  private function up1024_removeSocialAccountOptions($accounts) {
    $socialAccounts = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'website_type',
      'name' => ['IN' => $accounts],
    ]);

    $socialAccounts = $socialAccounts['values'];
    foreach ($socialAccounts as $optionValueId => $optionValue) {
      $socialAccountIsUsed = civicrm_api3('Website', 'get', [
        'website_type_id' => $optionValue['name'],
        'url' => ['IS NOT NULL' => 1],
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
