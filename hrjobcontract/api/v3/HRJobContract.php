<?php

require_once 'api/v3/jobcontract_utils.php';

/**
 * HRJobContract.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_h_r_job_contract_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * HRJobContract.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * HRJobContract.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * HRJobContract.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_get($params) {
    $returnFields = array();
    if (!empty($params['return'])) {
        if (is_array($params['return'])) {
            $returnFields = $params['return'];
        } else {
            $returnFields = explode(',', $params['return']);
        }
    }
    $contracts = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
    foreach ($contracts['values'] as $key => $contract)
    {
        $isCurrent = true;
        $contractDetails = civicrm_api3('HRJobDetails', 'get', array(
            'sequential' => 1,
            'jobcontract_id' => $contract['id'],
        ));
        $details = CRM_Utils_Array::first($contractDetails['values']);
        if (!empty($details['period_end_date']))
        {
            if ($details['period_end_date'] < date('Y-m-d'))
            {
                $isCurrent = false;
            }
        }
        $contracts['values'][$key]['is_current'] = (int)$isCurrent;
        
        foreach ($returnFields as $returnField) {
            if (!empty($details[$returnField])) {
                $contracts['values'][$key][$returnField] = $details[$returnField];
            }
        }
    }
    
    return $contracts;
}

/**
 * HRJobContract.deletecontract API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_deletecontract($params) {
  return _civicrm_hrjobcontract_api3_deletecontract($params);
}

/**
 * HRJobContract.deletecontract API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_deletecontractpermanently($params) {
  return _civicrm_hrjobcontract_api3_deletecontractpermanently($params);
}

/**
 * @see _civicrm_api3_generic_getlist_params.
 *
 * @param $request array
 */
function _civicrm_api3_h_r_job_contract_getlist_params(&$request) {
  $fieldsToReturn = array('contact_id', 'is_current', 'is_primary');
  if (!empty($request['return'])) {
      $fieldsToReturn = array_merge($fieldsToReturn, $request['return']);
  }
  $request['params']['return'] = array_unique(array_merge($fieldsToReturn, $request['extra']));
}

/**
 * @see _civicrm_api3_generic_getlist_output
 *
 * @param $result array
 * @param $request array
 *
 * @return array
 */
function _civicrm_api3_h_r_job_contract_getlist_output($result, $request) {
  $output = array();
  if (!empty($result['values'])) {
    foreach ($result['values'] as $row) {
      $data = $row;
      foreach ($request['extra'] as $field) {
        $data['extra'][$field] = isset($row[$field]) ? $row[$field] : NULL;
      }
      $output[] = $data;
    }
  }
  return $output;
}
