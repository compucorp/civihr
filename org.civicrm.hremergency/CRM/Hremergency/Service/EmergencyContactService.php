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
    $params = [1 => [$id, 'Integer']];
    $query = "DELETE FROM $this->tableName WHERE id = %1";
    $result = CRM_Core_DAO::executeQuery($query, $params);

    if (1 !== $result->affectedRows()) {
      $error = sprintf("Could not delete emergency contact with ID '%d'", $id);
      throw new API_Exception($error);
    }
  }

}
