<?php

class CRM_HRCore_Menu_CustomFields {

  /**
   * Returns menu Items for custom fields.
   *
   * @return array
   */
  public static function getItems() {
    $multipleCustomData = CRM_Core_BAO_CustomGroup::getMultipleFieldGroup();

    $menuItems = [];
    foreach ($multipleCustomData as $key => $value) {
      $menuItems[] = [
        'attributes' => [
          'label' => $value,
          'url' => 'civicrm/import/custom?reset=1&id='.$key,
          'permission' => 'access CiviCRM',
          'operator' => null,
        ]
      ];
    }

    return $menuItems;
  }
}
