<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1017 {

  /**
   * Before PCHR-3094, changing the Absence Type of a Leave Request to
   * another one with a different calculation unit would result in the
   * respective record in the database having non-mandatory fields with
   * wrong values. For example, if it was changed from an Absence Type in
   * days, to another one in hours, the from_date_type and to_date_type
   * (which are required only for Leave Requests in days) would still
   * keep the old values and this would cause validation issues that
   * prevented the Leave Request from being updated.
   *
   * This upgrader searches for any Leave Request in that situation and
   * sets the optional fields (according to the calculation unit) as NULL.
   *
   * @return bool
   */
  public function upgrade_1017() {
    $calculationUnits = array_flip(AbsenceType::buildOptions('calculation_unit', 'validate'));

    $leaveRequestTable = LeaveRequest::getTableName();
    $absenceTypeTable = AbsenceType::getTableName();

    $query = "
    SELECT a.calculation_unit, lr.id
      FROM {$leaveRequestTable} lr
      INNER JOIN {$absenceTypeTable} a ON lr.type_id = a.id
    WHERE
      (a.calculation_unit = %1 AND (lr.from_date_amount IS NOT NULL OR lr.to_date_amount IS NOT NULL ))
      OR
      (a.calculation_unit = %2 AND (lr.from_date_type IS NOT NULL OR lr.to_date_type IS NOT NULL ))
    ";

    $params = [
      1 => [$calculationUnits['days'], 'Integer'],
      2 => [$calculationUnits['hours'], 'Integer'],
    ];

    $recordToUpdate = CRM_Core_DAO::executeQuery($query, $params);

    while ($recordToUpdate->fetch()) {
      $leaveRequest = new LeaveRequest();
      $leaveRequest->id = $recordToUpdate->id;

      switch ($recordToUpdate->calculation_unit) {

        case $calculationUnits['days']:
          $leaveRequest->from_date_amount = 'null';
          $leaveRequest->to_date_amount = 'null';
          break;

        case $calculationUnits['hours']:
          $leaveRequest->from_date_type = 'null';
          $leaveRequest->to_date_type = 'null';
          break;
      }

      // Since this is just fixing a data issue rather than a normal update,
      // we don't need the hooks to be triggered
      $callHooks = FALSE;
      $leaveRequest->save($callHooks);
    }

    return TRUE;
  }
}
