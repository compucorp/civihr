<?php

/**
 * HRCoreInfo.getversion API specification.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_h_r_core_info_getversion_spec(&$spec) {
}

/**
 * HRCoreInfo.getversion API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_h_r_core_info_getversion($params) {
  return civicrm_api3_create_success(CRM_HRCore_Info::getVersion(), $params, 'HRCoreInfo', 'getversion');
}
