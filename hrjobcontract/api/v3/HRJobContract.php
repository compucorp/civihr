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

function civicrm_api3_h_r_job_contract_getlengthofservice($params) {
  if (empty($params['contact_id'])) {
    throw new API_Exception(ts("Please specify 'contact_id' value."));
  }
  $result = CRM_Hrjobcontract_BAO_HRJobContract::getLengthOfService($params['contact_id']);
  return civicrm_api3_create_success($result, $params);
}

function civicrm_api3_h_r_job_contract_updatelengthofservice($params) {
  if (empty($params['contact_id'])) {
    $result = CRM_Hrjobcontract_BAO_HRJobContract::updateLengthOfServiceAllContacts();
  } else {
    $result = CRM_Hrjobcontract_BAO_HRJobContract::updateLengthOfService($params['contact_id']);
  }
  return civicrm_api3_create_success($result, $params);
}

/**
 * HRJobContract.getactivecontracts API
 *
 * @param array $params The accepted params are: start_date and end_date
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_contract_getactivecontracts($params) {
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
  $result = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContracts($startDate, $endDate);
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
 * [$buildQuery description]
 * @var [type]
 */
function buildQuery($entities, $revision) {
  $query = ['select' => [], 'from' => [], 'where' => []];

  /**
   * [$entityAlias description]
   * @var [type]
   */
  $entityAlias = function ($entity) {
    return substr($entity, 0, 3);
  };

  foreach ($entities as $entity) {
    $class = "CRM_Hrjobcontract_BAO_HRJob" . ucfirst($entity);
    $fields = array_column($class::fields(), 'name');

    foreach ($fields as $field) {
      $query['select'][] = "{$entityAlias($entity)}.{$field} as {$entity}__{$field}";
    }

    $query['from'][] = "civicrm_hrjobcontract_{$entity} {$entityAlias($entity)}";
    $query['where'][] = "{$entityAlias($entity)}.jobcontract_revision_id = " . $revision["{$entity}_revision_id"];
  }

  return sprintf(
    "SELECT %s FROM %s WHERE %s ",
    join(', ', $query['select']),
    join(', ', $query['from']),
    join(' AND ', $query['where'])
  );
};

/**
 * [normalizeResult description]
 * @param  [type] $result [description]
 * @return [type]         [description]
 */
function normalizeResult($result) {
  $normalized = [];

  foreach ($result as $key => $value) {
    if (strpos($key, '_') == 0) { continue; }

    list($entity, $field) = explode('__', $key);
    $normalized[$entity][$field] = isJSON($value) ? json_decode($value) : $value;
  }

  return $normalized;
};

/**
 * [isJSON description]
 * @param  [type]  $string [description]
 * @return boolean         [description]
 */
function isJSON($string){
  return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
}

/**
 * HRJobContract.getfulldetails API
 *
 * @param array $params The accepted params are: jobcontract_id
 * @return array
 */
function civicrm_api3_h_r_job_contract_getfulldetails($params) {
  $fullDetails = [];
  $currentRevision = _civicrm_hrjobcontract_api3_get_current_revision($params)['values'];

  $result = CRM_Core_DAO::executeQuery(buildQuery(['details', 'hour', 'pay', 'health', 'pension'], $currentRevision));
  $result->fetch();
  $fullDetails = normalizeResult($result);

  $result = CRM_Core_DAO::executeQuery(buildQuery(['leave'], $currentRevision));
  while ($result->fetch()) {
    $fullDetails['leave'][] = normalizeResult($result)['leave'];
  }

  return $fullDetails;
}
