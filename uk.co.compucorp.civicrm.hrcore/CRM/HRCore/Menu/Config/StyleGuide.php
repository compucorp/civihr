<?php

use CRM_HRCore_Helper_ExtensionHelper as ExtensionHelper;

class CRM_HRCore_Menu_Config_StyleGuide {

  /**
   * Returns menu Items for Style guide Menu.
   *
   * @return array
   */
  public static function getItems() {
    $menuItems = [];

    if (!ExtensionHelper::isExtensionEnabled('org.civicrm.styleguide')) {
      return $menuItems;
    }

    foreach (Civi::service('style_guides')->getAll() as $styleGuide) {
      $label = $styleGuide['label'];
      // We need to check this because Civi has translated Bootstrap-CiviCRM to
      // Bootstrap-CiviHR for the label making this to be same as the other style guide
      // item having Bootstrap-CiviHR as its original label and we can't have
      // two menu items with same label.
      if ($styleGuide['name'] == 'bootstrap-civicrm') {
        $label = 'Bootstrap-CiviCRM';
      }

      $menuItems[$label] = [
        'url' => 'civicrm/styleguide/' . $styleGuide['name'],
        'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
        'operator' => 'AND'
      ];
    }

    return $menuItems;
  }
}
