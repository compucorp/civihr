<?php

class CRM_Hremergency_Service_EmergencyContactService {

  /**
   * @var string
   */
  private $tableName = 'civicrm_value_emergency_contacts_21';

  /**
   * @var array
   */
  private $fieldMapping = [];

  /**
   * Finds an emergency contact record by ID
   *
   * @param int $id
   *
   * @return array|NULL
   */
  public function find($id) {
    $selectQuery = "SELECT * FROM $this->tableName WHERE id = %1";
    $params = [1 => [$id, 'Integer']];
    $emergencyContacts = CRM_Core_DAO::executeQuery($selectQuery, $params);
    $emergencyContacts = $emergencyContacts->fetchAll();

    if (1 !== count($emergencyContacts)) {
      return NULL;
    }

    $emergencyContact = current($emergencyContacts);

    return $this->replaceColumnsWithName($emergencyContact);
  }

  /**
   * Deletes an emergency contact by ID
   *
   * @param int $id
   */
  public function delete($id) {
    $dao = CRM_Core_BAO_CustomGroup::class;
    $groupID = CRM_Core_DAO::getFieldValue($dao, 'Emergency_Contacts', 'id', 'name');
    CRM_Core_BAO_CustomValue::deleteCustomValue($id, $groupID);
  }

  /**
   * Replaces database column keys in the contact data with their field name.
   *
   * @param array $emergencyContact
   *
   * @return array
   */
  private function replaceColumnsWithName($emergencyContact) {
    if (empty($this->fieldMapping)) {
      $params = ['custom_group_id' => 'Emergency_Contacts'];
      $fields = civicrm_api3('CustomField', 'get', $params);
      $this->fieldMapping = $fields['values'];
    }

    foreach ($this->fieldMapping as $fieldInfo) {
      $column = $fieldInfo['column_name'];
      $name = $fieldInfo['name'];
      if (array_key_exists($column, $emergencyContact)) {
        $emergencyContact[$name] = $emergencyContact[$column];
        unset($emergencyContact[$column]);
      }
    }

    return $emergencyContact;
  }

}
