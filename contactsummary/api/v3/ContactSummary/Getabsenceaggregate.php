<?php

/**
 * ContactSummary.GetAbsenceAggregate API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_contact_summary_GetAbsenceAggregate_spec(&$spec) {
  $spec['aggregate_type']['api.required'] = 0;
  $spec['absence_type']['api.required'] = 0;
  $spec['period_id']['api.required'] = 1;
}

/**
 * ContactSummary.GetAbsenceAggregate API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_contact_summary_GetAbsenceAggregate($params) {
  $aggregateType = !empty($params['aggregate_type'])
    ? $params['aggregate_type'] : CRM_Contactsummary_Utils_Aggregate::TYPE_AVERAGE;

  $absenceTypes = !empty($params['absence_types']) ? $params['absence_types'] : null;
  $periodId = !empty($params['period_id']) ? $params['period_id'] : 0;

  $numStaff = CRM_Hrjobcontract_BAO_HRJobContract::getStaffCount();
  $totalAbsences = CRM_Contactsummary_Utils_Absences::getTotalAbsences($absenceTypes, $periodId);

  $values = array('staff' => $numStaff, 'absences' => $totalAbsences);

  switch ($aggregateType) {
    case CRM_Contactsummary_Utils_Aggregate::TYPE_AVERAGE:
      // Break skipped intentionally
    default:
      $values['result'] = CRM_Contactsummary_Utils_Aggregate::getAverage($totalAbsences, $numStaff);
      break;
  }

  return civicrm_api3_create_success(array($values), $params, 'ContactSummary', 'GetAbsenceAggregate');
}

