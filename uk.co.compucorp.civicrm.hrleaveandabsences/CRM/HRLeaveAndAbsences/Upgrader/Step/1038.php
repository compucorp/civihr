<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1038 {
  public function upgrade_1038() {
    $this->up1038_createLeaveRequestNoteField();

    return TRUE;
  }

  /**
   * Creates a leave request note field if it has not already been defined.
   */
  private function up1038_createLeaveRequestNoteField() {
    $leaveRequestTableName = LeaveRequest::getTableName();
    $isFieldDefined = SchemaHandler::checkIfFieldExists($leaveRequestTableName, 'notes');

    if ($isFieldDefined) {
      return;
    }

    CRM_Core_DAO::executeQuery("
      ALTER TABLE `{$leaveRequestTableName}`
      ADD `notes` TEXT DEFAULT NULL
    ");
  }
}
