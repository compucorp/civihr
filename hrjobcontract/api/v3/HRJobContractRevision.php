<?php

require_once 'jobcontract_utils.php';

/**
 * HRJobContractRevision.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_h_r_job_contract_revision_create_spec(&$spec) {
  $spec['jobcontract_id']['api.required'] = 1;
}

/**
 * HRJobContractRevision.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_revision_create($params) {
  $result = _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  $editorName = '';
  
  if (!empty($result['values'][0]['editor_uid'])) {
    $civiUser = civicrm_custom_user_profile_get_contact($result['values'][0]['editor_uid']);
    $editorName = $civiUser['sort_name'];
  }
  
  $result['values'][0]['editor_name'] = $editorName;
  
  return $result;
}

/**
 * HRJobContractRevision.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_revision_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * HRJobContractRevision.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_revision_get($params) {
    $revisions = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
    foreach ($revisions['values'] as $key => $revision)
    {
        $editorName = '';
        
        if (!empty($revision['editor_uid'])) {
            $civiUser = civicrm_custom_user_profile_get_contact($revision['editor_uid']);
            $editorName = $civiUser['sort_name'];
        }
        
        $revisions['values'][$key]['editor_name'] = $editorName;
    }
    return $revisions;
}

/**
 * HRJobContractRevision.getcurrentrevision API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_revision_getcurrentrevision($params) {
    if (empty($params['jobcontract_id']))
    {
        throw new API_Exception("Cannot get current revision: missing jobcontract_id value");
    }
    return _civicrm_hrjobcontract_api3_get_current_revision($params);
}

