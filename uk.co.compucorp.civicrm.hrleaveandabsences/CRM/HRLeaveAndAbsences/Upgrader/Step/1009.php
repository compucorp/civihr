<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1009 {

  /**
   * Change from_date and to_date fields from date to datetime
   *
   * @return bool
   */
  public function upgrade_1009() {
    $leaveRequestTable = LeaveRequest::getTableName();
    $queries = [
      "ALTER TABLE {$leaveRequestTable} MODIFY from_date datetime NOT NULL COMMENT 'The date and time the leave request starts.';",
      "ALTER TABLE {$leaveRequestTable} MODIFY to_date datetime NOT NULL COMMENT 'The date and time the leave request ends';"
    ];

    foreach($queries as $query) {
      CRM_Core_DAO::executeQuery($query);
    }

    return true;
  }
}
