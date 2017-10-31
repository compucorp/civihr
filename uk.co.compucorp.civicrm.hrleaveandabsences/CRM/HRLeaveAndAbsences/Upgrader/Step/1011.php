<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1011 {

  /**
   * Change from_date_type and to_date_type fields to be non required.
   *
   * @return bool
   */
  public function upgrade_1011() {
    $leaveRequestTable = LeaveRequest::getTableName();
    $queries = [
      "ALTER TABLE {$leaveRequestTable} MODIFY from_date_type int unsigned COMMENT 'One of the values of the Leave Request Day Type option group'",
      "ALTER TABLE {$leaveRequestTable} MODIFY to_date_type int unsigned   COMMENT 'One of the values of the Leave Request Day Type option group'"
    ];

    foreach($queries as $query) {
      CRM_Core_DAO::executeQuery($query);
    }

    return true;
  }
}
