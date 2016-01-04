<?php

/**
 * Rights.GetLocations API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_rights_GetLocations_spec(&$spec) {
  $spec['contact_id'] = [
    'api.required' => 1,
    'title' => 'Contact ID',
    'name' => 'contact_id'
  ];
}

/**
 * Rights.GetLocations API
 * E.g. Headquarters, Home.
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_rights_GetLocations($params) {
  try {
    $bao = new CRM_Contactaccessrights_BAO_Rights();
    $rightType = new CRM_Contactaccessrights_Utils_RightType_Location();
    $locationRights = $bao->getRightsByType($rightType, $params['contact_id']);

    return civicrm_api3_create_success($locationRights, $params, 'Rights', 'GetLocations');
  } catch (CRM_Extension_Exception $e) {
    throw new API_Exception('An error has occurred', $e->getCode());
  }
}

