<?php

use CRM_HRCore_Menu_Config_CustomFields as CustomFields;
use CRM_HRCore_ExtensionUtil as ExtensionUtil;

class CRM_HRCore_Menu_Config_Import {

  /**
   * Returns the Import related menu Items
   *
   * @return array
   */
  public static function getItems() {
    $importFile = CRM_Core_Resources::singleton()->getPath(ExtensionUtil::LONG_NAME, 'config/menu/import.php');
    $staticImportItems = include $importFile;
    $customFieldImports = CustomFields::getItems();

    return array_merge($staticImportItems, $customFieldImports);
  }
}
