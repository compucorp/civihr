<?php

/**
 * Activity.GetAbsences API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_activity_getabsences_spec(&$spec) {
  //$spec['magicword']['api.required'] = 1;
}

/**
 * Activity.GetAbsences API
 *
 * This is a variation on Activity.get with additional filtering behavior suitable for activities.
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_activity_getabsences($params) {
  // list of subordinate API calls to make
  $subParamss = array($params);

  // Accept a list of act types; or else use list of all absence act types
  // Summary: $subParamss = $subParamss * ($params['activity_type_id'] || default-activity-types)
  if (!isset($params['activity_type_id'])) {
    $params['activity_type_id'] = CRM_HRAbsence_BAO_HRAbsenceType::getActivityTypes();
  }
  else {
    $params['activity_type_id'] = (array) $params['activity_type_id'];
  }
  $newParamss = array();
  foreach ($params['activity_type_id'] as $activity_type_id) {
    foreach ($subParamss as $subParams) {
      $newParams = $subParams;
      $newParams['activity_type_id'] = $activity_type_id;
      $newParamss[] = $newParams;
    }
  }
  $subParamss = $newParamss;

  // Accept a list of periods - convert to activity_date_time filters
  // Summary: $subParamss = $subParamss * convert_period_to_datefilter($params['period_id'])
  if (isset($params['period_id'])) {
    $params['period_id'] = (array) $params['period_id'];

    $newParamss = array();
    foreach ($params['period_id'] as $period_id) {
      $period = civicrm_api3('HRAbsencePeriod', 'getsingle', array(
        'id' => $period_id,
      ));
      foreach ($subParamss as $subParams) {
        $newParams = $subParams;
        $newParams['filter.activity_date_time_low'] = preg_replace('/[ \-:\.]/', '', $period['start_date']);
        $newParams['filter.activity_date_time_high'] = preg_replace('/[ \-:\.]/', '', $period['end_date']);
        $newParamss[] = $newParams;
      } // each params
    } // each period
    $subParamss = $newParamss;
  }

  // Execute nested API call for each combination of period/activity-type
  $results = array();
  foreach ($subParamss as $subParams) {
    $result = civicrm_api3('Activity', 'get', $subParams);
    $results = array_merge($results, $result['values']);
  }

  // The return results are exactly the same as regular activities. Future developers: be warned
  // that modifying the outputs from Activity.get may cause brain-damage.
  return civicrm_api3_create_success($results, $params, 'Activity', 'getAbsences');
}
