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
 * HRJobContract.getlengthofservice
 *
 * Return a number of Length of Service in days for specific 'contact_id'.
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_getlengthofservice($params) {
  if (empty($params['contact_id'])) {
    throw new API_Exception(ts("Please specify 'contact_id' value."));
  }
  $result = CRM_Hrjobcontract_BAO_HRJobContract::getLengthOfService($params['contact_id']);
  return civicrm_api3_create_success($result, $params);
}

/**
 * HRJobContract.getlengthofserviceymd
 *
 * Return an object containing years, months and days of Length of Service
 * for specific 'contact_id'.
 * Useful for front-end part of Contact Summary block.
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_getlengthofserviceymd($params) {
  if (empty($params['contact_id'])) {
    throw new API_Exception(ts("Please specify 'contact_id' value."));
  }
  $result = CRM_Hrjobcontract_BAO_HRJobContract::getLengthOfServiceYmd($params['contact_id']);
  return civicrm_api3_create_success($result, $params);
}

/**
 * HRJobContract.updatelengthofservice
 *
 * Update Length of Service values for specific 'contact_id' or for every Contact
 * if $params['contact_id'] is not specified.
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_updatelengthofservice($params) {
  if (empty($params['contact_id'])) {
    $result = CRM_Hrjobcontract_BAO_HRJobContract::updateLengthOfServiceAllContacts();
  } else {
    $result = CRM_Hrjobcontract_BAO_HRJobContract::updateLengthOfService($params['contact_id']);
  }
  return civicrm_api3_create_success($result, $params);
}

/**
 * HRJobContract.getContractsWithDetailsInPeriod API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 */
function _civicrm_api3_h_r_job_contract_getcontractswithdetailsinperiod_spec(&$spec) {
  $spec['start_date'] = array(
    'name'         => 'start_date',
    'title'        => 'Start Date',
    'type'         => CRM_Utils_Type::T_DATE,
    'api.required' => 0,
  );

  $spec['end_date'] = array(
    'name'         => 'end_date',
    'title'        => 'End Date',
    'type'         => CRM_Utils_Type::T_DATE,
    'api.required' => 0,
  );

  $spec['contact_id'] = array(
    'name'         => 'contact_id',
    'title'        => 'Contact ID',
    'type'         => CRM_Utils_Type::T_INT,
    'api.required' => 0,
  );
}

/**
 * HRJobContract.getContractsWithDetailsInPeriod API
 *
 * @param array $params The accepted params are: start_date, end_date and contact_id
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_getcontractswithdetailsinperiod($params) {
  $startDate = null;
  $endDate = null;
  $contactID = null;

  if(!empty($params['start_date'])) {
    if(is_array($params['start_date'])) {
      return civicrm_api3_create_error('The start date parameter can only be used with the = operator');
    }
    $startDate = $params['start_date'];
  }

  if(!empty($params['end_date'])) {
    if(is_array($params['end_date'])) {
      return civicrm_api3_create_error('The end date parameter can only be used with the = operator');
    }
    $endDate = $params['end_date'];
  }

  if(!empty($params['contact_id'])) {
    $contactID = _civicrm_api3_h_r_job_contract_get_contacts_from_params($params);
  }

  $result = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod($startDate, $endDate, $contactID);
  return civicrm_api3_create_success($result, $params);
}

/**
 * Extracts the list of contactID's from the $params array
 *
 * @param array $params
 *
 * @return array
 */
function _civicrm_api3_h_r_job_contract_get_contacts_from_params($params) {
  if(empty($params['contact_id'])) {
    return [];
  }

  if(!is_array($params['contact_id'])) {
    return [$params['contact_id']];
  }

  if(!array_key_exists('IN', $params['contact_id'])) {
    throw new InvalidArgumentException('The contact_id parameter only supports the IN operator');
  }

  return $params['contact_id']['IN'];
}

/**
 * HRJobContract.getContactsWithContractsInPeriod API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 */
function _civicrm_api3_h_r_job_contract_getcontactswithcontractsinperiod_spec(&$spec) {
  $spec['start_date'] = array(
    'name'         => 'start_date',
    'title'        => 'Start Date',
    'type'         => CRM_Utils_Type::T_DATE,
    'api.required' => 0,
  );

  $spec['end_date'] = array(
    'name'         => 'end_date',
    'title'        => 'End Date',
    'type'         => CRM_Utils_Type::T_DATE,
    'api.required' => 0,
  );
}


/**
 * HRJobContract.getContactsWithContractsInPeriod API
 *
 * @param array $params The accepted params are: start_date and end_date
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_getcontactswithcontractsinperiod($params) {
  $startDate = null;
  $endDate = null;

  if(!empty($params['start_date'])) {
    if(is_array($params['start_date'])) {
      return civicrm_api3_create_error('The start date parameter can only be used with the = operator');
    }
    $startDate = $params['start_date'];
  }

  if(!empty($params['end_date'])) {
    if(is_array($params['end_date'])) {
      return civicrm_api3_create_error('The end date parameter can only be used with the = operator');
    }
    $endDate = $params['end_date'];
  }

  $result = CRM_Hrjobcontract_BAO_HRJobContract::getContactsWithContractsInPeriod($startDate, $endDate);
  return civicrm_api3_create_success($result, $params);
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


/**
 * HRJobContract.getcurrentcontract API
 *
 * @param array $params The accepted params are: contact_id
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_getcurrentcontract($params) {
  if (empty($params['contact_id']))  {
    throw new API_Exception('contact_id ' . ts("can't be empty."));
  }

  $contactID = (int) $params['contact_id'];
  $result = CRM_Hrjobcontract_BAO_HRJobContract::getCurrentContract($contactID);
  $return = null;
  if (!empty($result)) {
    $fields = ['contract_id', 'position', 'title', 'period_start_date', 'period_end_date', 'location'];
    foreach($fields as $field) {
      $return->$field = $result->$field;
    }
  }
  return civicrm_api3_create_success($return, $params);
}

/**
 * HRJobContract.getfulldetails API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 */
function _civicrm_api3_h_r_job_contract_getfulldetails_spec(&$spec) {
  $spec['jobcontract_id'] = array(
   'name' => 'jobcontract_id',
   'title' => 'Job Contract ID',
   'type' => 1,
   'api.required' => 1,
  );
}

/**
 * HRJobContract.getfulldetails API
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_h_r_job_contract_getfulldetails($params) {
  $currentRevision = _civicrm_hrjobcontract_api3_get_current_revision($params)['values'];

  return CRM_Hrjobcontract_BAO_HRJobContractRevision::fullDetails($currentRevision);
}
