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
  $spec['target_contact_id'] = array(
    'name' => 'target_id',
    'title' => 'Activity Target',
    'type' => 1,
    'FKClassName' => 'CRM_Activity_DAO_ActivityContact',
  );
  $spec['period_id'] = array(
    'name' => 'period_id',
    'title' => 'Absence Period',
    'type' => 1,
    'FKClassName' => 'CRM_HRAbsence_DAO_HRAbsencePeriod',
  );
  $spec['activity_type_id'] = array(
    'name' => 'activity_type_id',
    'title' => 'Activity Type',
    'type' => 1,
  );
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
  // activity_type_id: int|string|array(int|string)
  // period_id: int|array(int)
  // target_contact_id: int

  $activityTypes = CRM_Core_PseudoConstant::activityType();

  // ****** Defaults ******

  if (!isset($params['activity_type_id'])) {
    $params['activity_type_id'] = CRM_HRAbsence_BAO_HRAbsenceType::getActivityTypes();
  }

  // ****** Build query ******

  $select = new CRM_HRAbsence_DGWDIHTWT('civicrm_activity request');
  $select
    ->select('request.*')
    ->groupBy('request.id')
    ->join('absence', 'INNER JOIN civicrm_activity absence ON (absence.source_record_id = request.id AND absence.activity_type_id = #typeId)',
    array(
      '#typeId' => array_search('Absence', $activityTypes)
    ));

  if (!empty($params['period_id'])) {
    $periodIds = (array) $params['period_id'];
    $dateExprs = array(); // array(string $sqlExpression)
    foreach ($periodIds as $periodId) {
      $period = civicrm_api3('HRAbsencePeriod', 'getsingle', array(
        'id' => $periodId,
      ));
      $dateExprs[] = $select->interpolate('min(absence.activity_date_time) between @start and @end', array(
        '@start' => $period['start_date'],
        '@end' => $period['end_date'],
      ));
    }
    $select->having(implode(' or ', $dateExprs));
  }

  if (!empty($params['activity_type_id'])) {
    $typeIds = (array) $params['activity_type_id'];
    foreach (array_keys($typeIds) as $key) {
      if (!is_numeric($typeIds[$key])) {
        $typeIds[$key] = array_search($typeIds[$key], $activityTypes);
        if ($typeIds[$key] === FALSE) {
          throw new API_Exception("Invalid activity type");
        }
      }
    }
    $select->where('request.activity_type_id IN (#typeIds)', array(
      '#typeIds' => $typeIds
    ));
  }

  if (!empty($params['target_contact_id'])) {
    $activityContactTypes = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
    $select->join('tgt',
      'INNER JOIN civicrm_activity_contact tgt
        ON tgt.activity_id = request.id
        AND tgt.record_type_id = #tgt
        AND tgt.contact_id IN (#targetIds)',
      array(
        '#tgt' => CRM_Utils_Array::key('Activity Targets', $activityContactTypes),
        '#targetIds' => (array) $params['target_contact_id'],
      )
    );
  }

  // ****** Execute query ******

  $entity = _civicrm_api3_get_BAO(__FUNCTION__);
  $bao = CRM_Core_DAO::executeQuery($select->toSQL(), array(), TRUE, 'CRM_Activity_BAO_Activity');
  $activities = _civicrm_api3_dao_to_array($bao, $params, FALSE, $entity, FALSE);
  $activities = _civicrm_api3_activity_get_formatResult($params, $activities);
  return civicrm_api3_create_success($activities, $params, $entity, 'getAbsences');
}
