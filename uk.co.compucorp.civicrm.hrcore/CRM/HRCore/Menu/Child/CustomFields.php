
<?php

class CRM_HRCore_Menu_Child_CustomFields {

  /**
   *  Returns menu Items for Custom fields Menu.
   *
   * @return array
   */
  public static function getItems() {
    $multipleCustomData = CRM_Core_BAO_CustomGroup::getMultipleFieldGroup();
    $menuItems = [];
    foreach ($multipleCustomData as $key => $value) {
      $menuItems[$value] = [
        'url' => 'civicrm/import/custom?reset=1&id='.$key,
        'permission' => 'access CiviCRM',
      ];
    }
    return $menuItems;
  }
}
