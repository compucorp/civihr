<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1010 {

  /**
   * Adds the from_date_amount and to_date_amount fields to the
   * leave request table.
   *
   * @return bool
   */
  public function upgrade_1010() {
    $leaveRequestTable = LeaveRequest::getTableName();

    if(!SchemaHandler::checkIfFieldExists($leaveRequestTable, 'from_date_amount')) {
      $queries = [
        "ALTER TABLE {$leaveRequestTable} ADD from_date_amount decimal(20,2) COMMENT 'The balance change amount to be deducted for the leave request from date'",
        "ALTER TABLE {$leaveRequestTable} ADD to_date_amount decimal(20,2) COMMENT 'The balance change amount to be deducted for the leave request to date'",
      ];

      foreach($queries as $query) {
        CRM_Core_DAO::executeQuery($query);
      }
    }

    return true;
  }
}
