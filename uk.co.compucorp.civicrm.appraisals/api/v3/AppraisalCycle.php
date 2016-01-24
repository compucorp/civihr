<?php

/**
 * AppraisalCycle.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_appraisal_cycle_create_spec(&$spec) {
}

/**
 * AppraisalCycle.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * AppraisalCycle.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * AppraisalCycle.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

////// Custom API calls

/**
 * AppraisalCycle.getpreviouscycleid API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_getpreviouscycleid($params) {
  if (empty($params['manager_id'])) {
    throw new API_Exception(ts("Please specify 'manager_id' value."));
  }
  $values = CRM_Appraisals_BAO_AppraisalCycle::getPreviousCycleId((int)$params['manager_id']);
  return civicrm_api3_create_success($values, $params, 'AppraisalCycle', 'getpreviouscycleid');
}

/**
 * AppraisalCycle.getcurrentcycleid API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_getcurrentcycleid($params) {
  if (empty($params['manager_id'])) {
    throw new API_Exception(ts("Please specify 'manager_id' value."));
  }
  $values = CRM_Appraisals_BAO_AppraisalCycle::getCurrentCycleId((int)$params['manager_id']);
  return civicrm_api3_create_success($values, $params, 'AppraisalCycle', 'getcurrentcycleid');
}

/**
 * AppraisalCycle.getcurrentcyclestatus API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_getcurrentcyclestatus($params) {
  if (empty($params['manager_id'])) {
    throw new API_Exception(ts("Please specify 'manager_id' value."));
  }
  $values = CRM_Appraisals_BAO_AppraisalCycle::getCurrentCycleStatus((int)$params['manager_id']);
  return civicrm_api3_create_success($values, $params, 'AppraisalCycle', 'getcurrentcyclestatus');
}

/**
 * AppraisalCycle.getcycleaveragegrade API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_getcycleaveragegrade($params) {
  if (empty($params['cycle_id'])) {
    throw new API_Exception(ts("Please specify 'cycle_id' value."));
  }
  $managerId = !empty($params['manager_id']) ? (int)$params['manager_id'] : null;
  $values = CRM_Appraisals_BAO_AppraisalCycle::getCycleAverageGrade((int)$params['cycle_id'], $managerId);
  return civicrm_api3_create_success($values, $params, 'AppraisalCycle', 'getcycleaveragegrade');
}

/**
 * AppraisalCycle.getcurrentcycleaveragegrade API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_getcurrentcycleaveragegrade($params) {
  if (empty($params['manager_id'])) {
    throw new API_Exception(ts("Please specify 'manager_id' value."));
  }
  $values = CRM_Appraisals_BAO_AppraisalCycle::getCurrentCycleAverageGrade((int)$params['manager_id']);
  return civicrm_api3_create_success($values, $params, 'AppraisalCycle', 'getcurrentcycleaveragegrade');
}

/**
 * AppraisalCycle.getcurrentcycleaveragegrade API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_getpreviouscycleaveragegrade($params) {
  if (empty($params['manager_id'])) {
    throw new API_Exception(ts("Please specify 'manager_id' value."));
  }
  $values = CRM_Appraisals_BAO_AppraisalCycle::getPreviousCycleAverageGrade((int)$params['manager_id']);
  return civicrm_api3_create_success($values, $params, 'AppraisalCycle', 'getpreviouscycleaveragegrade');
}

/**
 * AppraisalCycle.getallcycleids API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_getallcycleids($params) {
  $managerId = !empty($params['manager_id']) ? (int)$params['manager_id'] : null;
  $contactId = !empty($params['contact_id']) ? (int)$params['contact_id'] : null;
  $values = CRM_Appraisals_BAO_AppraisalCycle::getAllCycleIds($managerId, $contactId);
  return civicrm_api3_create_success($values, $params, 'AppraisalCycle', 'getallcycleids');
}

/**
 * AppraisalCycle.getallcyclesaveragegrade API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_getallcyclesaveragegrade($params) {
  if (empty($params['manager_id'])) {
    throw new API_Exception(ts("Please specify 'manager_id' value."));
  }
  $values = CRM_Appraisals_BAO_AppraisalCycle::getAllCyclesAverageGrade((int)$params['manager_id']);
  return civicrm_api3_create_success($values, $params, 'AppraisalCycle', 'getallcyclesaveragegrade');
}

/**
 * AppraisalCycle.getstatusoverview API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_getstatusoverview($params) {
  $current_date = !empty($params['current_date']) ? $params['current_date'] : false;
  $cycles_ids   = !empty($params['cycles_ids'])   ? $params['cycles_ids']   : false;
  $start_date   = !empty($params['start_date'])   ? $params['start_date']   : false;
  $end_date     = !empty($params['end_date'])     ? $params['end_date']     : false;

  $values = CRM_Appraisals_BAO_AppraisalCycle::getStatusOverview($current_date, $cycles_ids, $start_date, $end_date);
  return civicrm_api3_create_success($values, $params, 'AppraisalCycle', 'getstatusoverview');
}

/**
 * AppraisalCycle.getappraisalsperstep API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appraisal_cycle_getappraisalsperstep($params) {
  if (empty($params['appraisal_cycle_id'])) {
    throw new API_Exception(ts("Please specify 'appraisal_cycle_id' value."));
  }
  $values = CRM_Appraisals_BAO_AppraisalCycle::getAppraisalsPerStep($params['appraisal_cycle_id'], !empty($params['include_appraisals']) ? $params['include_appraisals'] : false);
  return civicrm_api3_create_success($values, $params, 'AppraisalCycle', 'getappraisalsperstep');
}
