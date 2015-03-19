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
 * HRJobPay.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_h_r_job_pay_create_spec(&$spec) {
}

/**
 * HRJobPay.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_pay_create($params) {
  $result = _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  if (empty($result['is_error'])) {
    $row = CRM_Utils_Array::first($result['values']);
    $revision_id = $row['jobcontract_revision_id'];
    
    $revision = civicrm_api3('HRJobContractRevision', 'get', array(
      'sequential' => 1,
      'id' => $revision_id,
      'options' => array('limit' => 1),
    ));
    
    //CRM_Hrjobcontract_Estimator::updateEstimatesByRevision($revision);
  }
  return $result;
}

/**
 * HRJobPay.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_pay_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * HRJobPay.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_h_r_job_pay_get_spec(&$spec) {
}

/**
 * HRJobPay.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_pay_get($params) {
  _civicrm_hrjobcontract_api3_set_current_revision($params, _civicrm_get_table_name(_civicrm_api3_get_BAO(__FUNCTION__)));
  return _civicrm_hrjobcontract_api3_custom_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
