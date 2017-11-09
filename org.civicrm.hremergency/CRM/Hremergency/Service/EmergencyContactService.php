<?php

class CRM_Hremergency_Service_EmergencyContactService {

  /**
   * @var string
   */
  private $tableName = 'civicrm_value_emergency_contacts_21';

  /**
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

    return current($emergencyContacts);
  }

  /**
   * @param int $id
   */
  public function delete($id) {
    $dao = CRM_Core_BAO_CustomGroup::class;
    $groupID = CRM_Core_DAO::getFieldValue($dao, 'Emergency_Contacts', 'id', 'name');
    CRM_Core_BAO_CustomValue::deleteCustomValue($id, $groupID);
  }

}
