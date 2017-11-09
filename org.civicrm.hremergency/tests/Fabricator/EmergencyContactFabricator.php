<?php

namespace Tests\CiviHR\HREmergency\Fabricator;

class EmergencyContactFabricator {

  /**
   * @param int $contactID
   *   The contact ID that the emergency contact is related to
   * @param string $name
   *   The name of the emergency contact
   *
   * @return array
   *   The created emergency contact
   */
  public static function fabricate($contactID, $name) {
    self::createEmergencyContact($contactID, $name);
    $allEmergencyContactData = self::getEmergencyContactData($contactID);
    $returnID = self::getMaxEmergencyContactID($allEmergencyContactData);

    $return = [
      'id' => $returnID,
      'entityID' => $contactID
    ];

    foreach ($allEmergencyContactData as $fieldID => $fieldValues) {
      $fieldName = self::getEmergencyContactFieldName($fieldID);
      $return[$fieldName] = $fieldValues[$returnID];
    }

    return $return;
  }

  /**
   * Creates the emergency contact with just the name field set
   *
   * @param int $contactID
   * @param string $name
   */
  private static function createEmergencyContact($contactID, $name) {
    $nameFieldID = self::getEmergencyContactFieldID('Name');
    $params = ['id' => $contactID, 'custom_' . $nameFieldID => $name];
    civicrm_api3('Contact', 'create', $params);
  }

  /**
   * Gets the values for all emergency contacts for a given contact ID
   *
   * @param int $contactID
   * @return array
   */
  private static function getEmergencyContactData($contactID) {
    $fields = self::getEmergencyContactFields();
    $fieldIDs = array_keys($fields);
    $params = array_map(function ($fieldID) {
      return sprintf('return_custom_%d', $fieldID);
    }, $fieldIDs);
    $params['entity_id'] = $contactID;
    $values = civicrm_api3('CustomValue', 'get', $params);

    return $values['values'];
  }

  /**
   * Gets the max emergency contact ID
   *
   * @param array $values
   * @return int
   */
  private static function getMaxEmergencyContactID($values) {
    $nameFieldID = self::getEmergencyContactFieldID('Name');
    $nameValues = $values[$nameFieldID];
    $emergencyContactIDs = array_filter(array_keys($nameValues), 'is_int');

    return max($emergencyContactIDs);
  }

  /**
   * Gets the ID for the given field name
   *
   * @param string $name
   *
   * @return int
   */
  private static function getEmergencyContactFieldID($name) {
    $fields = self::getEmergencyContactFields();
    $field = current(array_filter($fields, function ($field) use ($name) {
      return $field['name'] === $name;
    }));

    return $field['id'];
  }

  /**
   * Gets data on all custom fields for Emergency Contacts
   *
   * @return array
   */
  private static function getEmergencyContactFields() {
    static $fields;

    if (empty($fields)) {
      $fieldParams = ['custom_group_id' => "Emergency_Contacts"];
      $fields = civicrm_api3('CustomField', 'get', $fieldParams)['values'];
    }

    return $fields;
  }

  /**
   * Gets the name of an emergency contact custom field based on ID.
   *
   * @param int $id
   *
   * @return string
   */
  private static function getEmergencyContactFieldName($id) {
    $fields = self::getEmergencyContactFields();

    return $fields[$id]['name'];
  }
}
