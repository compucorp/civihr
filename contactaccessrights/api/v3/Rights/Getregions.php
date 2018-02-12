<?php

/**
 * Rights.GetRegions API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_rights_GetRegions_spec(&$spec) {
  $spec['contact_id'] = [
    'api.required' => 1,
    'title' => 'Contact ID',
    'name' => 'contact_id'
  ];
}

/**
 * Rights.GetRegions API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_rights_GetRegions($params) {
  try {
    $contactRightsService = new CRM_Contactaccessrights_Service_ContactRights();
    $regionRights = $contactRightsService->getContactRightsByRegions($params['contact_id']);

    return civicrm_api3_create_success($regionRights, $params, 'Rights', 'GetRegions');
  } catch (CRM_Extension_Exception $e) {
    throw new API_Exception('An error has occurred', $e->getCode());
  }
}

