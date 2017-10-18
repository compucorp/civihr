<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1004 {

  /**
   * Adds the type field to the Leave Request Date table
   *
   * @return bool
   */
  public function upgrade_1004() {
    $leaveRequestDateTable = LeaveRequestDate::getTableName();
    if(!SchemaHandler::checkIfFieldExists($leaveRequestDateTable, 'type')) {
      CRM_Core_DAO::executeQuery("
        ALTER TABLE {$leaveRequestDateTable} 
        ADD type VARCHAR(512) NULL 
        COMMENT 'The type of this day, according to the values on the Leave Request Day Types Option Group'");
    }

    return true;
  }
}
