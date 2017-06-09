<?php

trait CRM_HRUI_Upgrader_Steps_4700 {

  /**
   * Create (Import Custom Fields) menu items
   *
   * @return bool
   */
  public function upgrade_4700() {
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_navigation WHERE name = 'jobImport'");

    $contactsNavID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');
    $importContactWeight = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Import Contacts', 'weight', 'name');
    $params = [
      'name' => 'import_custom_fields',
      'label' => ts('Import Custom Fields'),
      'url' => 'civicrm/import/custom?reset=1',
      'parent_id' => $contactsNavID,
      'is_active' => TRUE,
      'weight' => $importContactWeight,
      'permission' => 'access CiviCRM',
      'domain_id' => CRM_Core_Config::domainID(),
    ];

    $navigation = new CRM_Core_DAO_Navigation();
    $navigation->copyValues($params);
    $navigation->save();

    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }

}