<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.4                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2014                                |
+--------------------------------------------------------------------+
| This file is a part of CiviCRM.                                    |
|                                                                    |
| CiviCRM is free software; you can copy, modify, and distribute it  |
| under the terms of the GNU Affero General Public License           |
| Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
|                                                                    |
| CiviCRM is distributed in the hope that it will be useful, but     |
| WITHOUT ANY WARRANTY; without even the implied warranty of         |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
| See the GNU Affero General Public License for more details.        |
|                                                                    |
| You should have received a copy of the GNU Affero General Public   |
| License and the CiviCRM Licensing Exception along                  |
| with this program; if not, contact CiviCRM LLC                     |
| at info[AT]civicrm[DOT]org. If you have questions about the        |
| GNU Affero General Public License or the licensing of CiviCRM,     |
| see the CiviCRM license FAQ at http://civicrm.org/licensing        |
+--------------------------------------------------------------------+
*/

/**
 * HRJob.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_h_r_job_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
  $params['is_primary']['api.default'] = 0;
}

/**
 * HRJob.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * HRJob.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * HRJob.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * HRJob.Duplicate API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_h_r_job_duplicate_spec(&$spec) {
  $spec['magicword']['api.required'] = 1;
  $spec['magicword']['title'] = 'magicword';
}

/**
 * HRJob.Duplicate API
 *
 * @param array $params must include "id" of the original job, and may additionally include per-field overrides
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_duplicate($params) {
  $hrJobFields = civicrm_api3('HRJob', 'getfields', array());

  $originalGetResult = civicrm_api3('HRJob', 'get', array(
    'id' => $params['id'],
    'sequential' => 1,
  ));
  if ($originalGetResult['count'] != 1 || !is_array($originalGetResult['values'][0])) {
    throw new API_Exception('Cannot duplicate: Unknown original ID', 'duplciate_unknown_id');
  }

  $ignoreFields = array('id'); // Never copy or set this on new record
  $allowOverrideFields = array('is_primary'); // Only set if explicitly passed in $params
  $duplicateCreateParams = array();
  foreach ($hrJobFields['values'] as $hrJobField) {
    $fieldKey = $hrJobField['name'];
    if (in_array($fieldKey, $ignoreFields)) {
      continue;
    }
    if (isset($params[$fieldKey])) {
      $duplicateCreateParams[$fieldKey] = $params[$fieldKey];
    } elseif (isset($originalGetResult['values'][0][$fieldKey]) && !in_array($fieldKey, $allowOverrideFields)) {
      $duplicateCreateParams[$fieldKey] = $originalGetResult['values'][0][$fieldKey];
    }
  }
  $duplicateCreateParams['action'] = 'duplicate';
  $duplicateCreateResult = civicrm_api3('HRJob', 'create', $duplicateCreateParams);

  // Duplicate each of the sub-entities

  $subEntities = array(
    'HRJobPay',
    'HRJobHealth',
    'HRJobHour',
    'HRJobPension',
    'HRJobRole',
    'HRJobLeave',
  );
  foreach ($subEntities as $subEntity) {
    $originalSubGetResult = civicrm_api3($subEntity, 'get', array(
      'job_id' => $params['id'],
    ));
    foreach ($originalSubGetResult['values'] as $originalSubGetValue) {
      $duplicateSubCreateParams = $originalSubGetValue;
      unset($duplicateSubCreateParams['id']);
      $duplicateSubCreateParams['job_id'] = $duplicateCreateResult['id'];
      $v = civicrm_api3($subEntity, 'create', $duplicateSubCreateParams);
    }
  }

  // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($duplicateCreateResult['values'], $params, 'HRJob', 'duplicate');
}
