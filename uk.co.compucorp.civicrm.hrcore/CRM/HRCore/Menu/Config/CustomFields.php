
<?php

class CRM_HRCore_Menu_Config_CustomFields {

  /**
   *  Returns menu Items for Custom fields Imports.
   *
   * @return array
   */
  public static function getItems() {
    $multipleCustomData = CRM_Core_BAO_CustomGroup::getMultipleFieldGroup();
    $menuItems = [];
    foreach ($multipleCustomData as $key => $value) {
      $label = 'Import' . ' ' . $value;
      $menuItems[$label] = [
        'url' => 'civicrm/import/custom?reset=1&id='.$key,
        'permission' => 'access CiviCRM',
      ];
    }
    return $menuItems;
  }
}
