<?php

class CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate extends CRM_HRLeaveAndAbsences_DAO_LeaveRequestDate {

  /**
   * Create a new LeaveRequestDate based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_LeaveRequestDate|NULL
   */
  public static function create($params) {
    $entityName = 'LeaveRequestDate';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Returns an array of LeaveRequestDate instances related to the given
   * LeaveRequest ID.
   *
   * @param int $leaveRequestID
   *  The ID of the LeaveRequest to get the Dates
   *
   * @return CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate[]
   */
  public static function getDatesForLeaveRequest($leaveRequestID) {
    $leaveRequestDateTable = self::getTableName();
    $leaveRequestTable = CRM_HRLeaveAndAbsences_BAO_LeaveRequest::getTableName();

    $query = "
      SELECT lrd.*
      FROM {$leaveRequestDateTable} lrd
      INNER JOIN {$leaveRequestTable} lr
        ON lrd.leave_request_id = lr.id
      WHERE lrd.leave_request_id = %1
      AND lr.is_deleted = 0
      ORDER BY lrd.date
    ";

    $params = [
      1 => [$leaveRequestID, 'Integer'],
    ];

    $dates = [];

    $result = CRM_Core_DAO::executeQuery($query, $params, true, self::class);
    while($result->fetch()) {
      $dates[] = clone $result;
    }

    return $dates;
  }

  /**
   * Deletes all the LeaveRequestDates related to the given LeaveRequest ID.
   *
   * @param int $leaveRequestID
   *  The ID of the LeaveRequest from which the dates will be deleted
   */
  public static function deleteDatesForLeaveRequest($leaveRequestID) {
    $dao = new self();
    $dao->leave_request_id = (int)$leaveRequestID;
    $dao->delete();
  }
}
