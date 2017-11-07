<?php

/**
 * EmergencyContact.delete API
 *
 * @param array $params
 *
 * @return array API result descriptor
 */
function civicrm_api3_emergency_contact_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * EmergencyContact.get API
 *
 * @param array $params
 *
 * @return array API result descriptor
 */
function civicrm_api3_emergency_contact_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
