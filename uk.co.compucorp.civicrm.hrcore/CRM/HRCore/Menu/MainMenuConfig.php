<?php

use CRM_HRCore_ExtensionUtil as ExtensionUtil;

class CRM_HRCore_Menu_MainMenuConfig implements CRM_HRCore_Menu_Config {

  /**
   * Returns menu Items for Main navigation menu Items.
   *
   * @return array
   */
  public function getItems() {
    $configFile = CRM_Core_Resources::singleton()->getPath(ExtensionUtil::LONG_NAME, 'config/menu/main.php');

    return include $configFile;
  }
}
