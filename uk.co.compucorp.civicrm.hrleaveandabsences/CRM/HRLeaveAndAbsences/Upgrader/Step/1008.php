<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1008 {

  /**
   * Rename the comment_author_id and comment_date column to
   * editor_id and created_date respectively.
   *
   * @return bool
   */
  public function upgrade_1008() {
    $periodEntitlementTable = LeavePeriodEntitlement::getTableName();

    if(SchemaHandler::checkIfFieldExists($periodEntitlementTable, 'comment_author_id')) {
      $queries = [
        "ALTER TABLE {$periodEntitlementTable} DROP FOREIGN KEY FK_civicrm_hrlaa_leave_period_entitlement_comment_author_id;",
        "ALTER TABLE {$periodEntitlementTable} CHANGE comment_author_id editor_id int unsigned COMMENT 'FK to Contact. The contact that represents the user who made changes to this entitlement'",
        "ALTER TABLE {$periodEntitlementTable} CHANGE comment_date created_date datetime COMMENT 'The date and time this entitlement was added/updated'",
        "ALTER TABLE {$periodEntitlementTable} ADD CONSTRAINT FK_civicrm_hrlaa_leave_period_entitlement_editor_id FOREIGN KEY (editor_id) REFERENCES civicrm_contact(id) ON DELETE CASCADE;",
      ];

      foreach($queries as $query) {
        CRM_Core_DAO::executeQuery($query);
      }
    }

    return true;
  }
}
