<?php

class CRM_HRCore_Menu_Config_StaffDirectory {

  /**
   *  Returns the Staff Directory menu Item.
   *
   * @return array
   */
  public static function getItems() {
    $result = civicrm_api3('OptionValue', 'getsingle', [
      'option_group_id' => 'custom_search',
      'name' => 'CRM_HRCore_Form_Search_StaffDirectory',
    ]);

    $searchDirectoryURL = '';
    if (!empty($result['value'])) {
      $searchDirectoryURL =
        "civicrm/contact/search/custom?csid={$result['value']}&force=1&reset=1&select_staff=current";
    }

    return ['url' => $searchDirectoryURL, 'separator' => 1];
  }
}
