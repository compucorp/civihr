<?php

/**
 * Appraisal.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_appraisal_create_spec(&$spec) {
}

/**
 * Appraisal.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Appraisal.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Appraisal.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

function civicrm_api3_appraisal_tags($params) {
    $values = array();

    $values['department'] = CRM_Core_OptionGroup::values('hrjc_department', false, false, false, null, 'label', true, false, 'id');
    $values['level_type'] = CRM_Core_OptionGroup::values('hrjc_level_type', false, false, false, null, 'label', true, false, 'id');
    $values['region'] = CRM_Core_OptionGroup::values('hrjc_region', false, false, false, null, 'label', true, false, 'id');
    $values['location'] = CRM_Core_OptionGroup::values('hrjc_location', false, false, false, null, 'label', true, false, 'id');

    return civicrm_api3_create_success($values, $params, 'Appraisal', 'tags');
}

function civicrm_api3_appraisal_filter($params) {
    return CRM_Appraisals_BAO_Appraisal::filter($params);
}

/*
 * Appraisal Reminder on demand.
 */
function civicrm_api3_appraisal_sendreminder($params) {
    
    if (empty($params['id'])) {
        throw new API_Exception(ts("Please specify Appraisal 'id' value."));
    }
    
    $result = CRM_Appraisals_Reminder::sendReminder((int)$params['id'], isset($params['notes']) ? $params['notes'] : '', true);
    return civicrm_api3_create_success($result, $params, 'appraisal', 'sendreminder');
}
