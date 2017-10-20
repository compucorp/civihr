<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1007 {
  /**
   * Creates the LeavePeriodEntitlementLog table if
   * it does not already exist.
   *
   * @return bool
   */
  public function upgrade_1007() {
    CRM_Core_DAO::executeQuery("
      CREATE TABLE IF NOT EXISTS `civicrm_hrleaveandabsences_leave_period_entitlement_log` (
      
      
           `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique LeavePeriodEntitlementLog ID',
           `entitlement_id` int unsigned NOT NULL   COMMENT 'FK to LeavePeriodEntitlement',
           `editor_id` int unsigned NOT NULL   COMMENT 'FK to Contact. The contact that represents the user who made changes to this entitlement',
           `entitlement_amount` decimal(20,2) NOT NULL   COMMENT 'The entitlement amount for this Period Entitlement until created_date value',
           `comment` text   COMMENT 'The comment added by the user about the calculation for this entitlement',
           `created_date` datetime   COMMENT 'The date and time this entitlement was updated',
          PRIMARY KEY (`id`),
          CONSTRAINT FK_civicrm_hrlaa_leave_period_entitlement_log_entitlement_id FOREIGN KEY (`entitlement_id`) REFERENCES `civicrm_hrleaveandabsences_leave_period_entitlement`(`id`) ON DELETE CASCADE,
          CONSTRAINT FK_civicrm_hrlaa_leave_period_entitlement_log_editor_id FOREIGN KEY (`editor_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
      )  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;");

    return true;
  }
}
