<?php

/**
 * Contact.DeleteEmergencyContact API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 */
function _civicrm_api3_contact_deleteemergencycontact_spec(&$spec) {
  $spec['id']['api.required'] = 1;
}

/**
 * Contact.DeleteEmergencyContact API
 *
 * @param array $params
 * @return array API result descriptor
 */
function civicrm_api3_contact_deleteemergencycontact($params) {
  civicrm_api3_verify_mandatory($params, NULL, ['id']);

  $service = Civi::container()->get('emergency_contact.service');
  $contactID = CRM_Core_Session::getLoggedInContactID();
  $id = $params['id'];
  $emergencyContact = $service->find($id);

  if (!$emergencyContact) {
    $error = sprintf('Could not find emergency contact with ID \'%d\'', $id);
    throw new API_Exception($error);
  }

  // Check permissions
  if ($emergencyContact['entity_id'] != $contactID) {
    $error = 'Only an emergency contacts\' relation can delete them';
    throw new API_Exception($error);
  }

  $service->delete($id);

  return civicrm_api3_create_success();
}
