<?php

/**
 * EmergencyContact.delete API
 *
 * @param array $params
 *
 * @return array API result descriptor
 */
function civicrm_api3_emergency_contact_delete($params) {
  civicrm_api3_verify_mandatory($params, NULL, ['id']);
  $id = $params['id'];

  $contactID = CRM_Core_Session::getLoggedInContactID();
  // todo permissions

  $query = 'DELETE FROM civicrm_value_emergency_contacts_21 WHERE id = %1';
  $result = CRM_Core_DAO::executeQuery($query, [1 => [$id, 'Integer']]);

  if (1 !== $result->affectedRows()) {
    $error = sprintf('Emergency contact with ID \'%d\' not found', $id);
    return civicrm_api3_create_error($error);
  }

  return civicrm_api3_create_success();
}
