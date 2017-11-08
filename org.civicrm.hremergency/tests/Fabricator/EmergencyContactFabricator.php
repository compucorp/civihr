<?php

namespace Tests\CiviHR\HREmergency\Fabricator;

class EmergencyContactFabricator {

  /**
   * @param $entityID
   * @param $name
   *
   * @return int
   *   The created emergency contact ID
   */
  public static function fabricate($entityID, $name) {
    $fields = \CRM_Core_BAO_CustomField::getFields('Individual');
    $fields = array_filter($fields, function ($field) {
      return $field['groupTitle'] == 'Emergency Contacts' && $field['label'] === 'Name';
    });
    $nameFieldID = key($fields);

    $params = [
      'id' => $entityID,
      'custom_' . $nameFieldID => $name
    ];
    civicrm_api3('Contact', 'create', $params);

    $query = 'SELECT MAX(id) as max_id FROM civicrm_value_emergency_contacts_21';
    $result = \CRM_Core_DAO::executeQuery($query);
    $result->fetch();

    return $result->max_id;
  }
}
