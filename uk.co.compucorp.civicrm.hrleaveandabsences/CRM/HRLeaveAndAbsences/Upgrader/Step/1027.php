<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1027 {

  /**
   * Creates the LeaveBalanceExpiryLog table if
   * it does not already exist.
   *
   * @return bool
   */
  public function upgrade_1027() {
    CRM_Core_DAO::executeQuery("
      CREATE TABLE IF NOT EXISTS `civicrm_hrleaveandabsences_leave_balance_change_expiry_log` (
           
           `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique LeaveBalanceChangeExpiryLog ID',
           `balance_change_id` int unsigned NOT NULL   COMMENT 'The expired balance change ID',
           `amount` decimal(20,2) NOT NULL  DEFAULT 0 COMMENT 'The expired balance amount',
           `source_id` int unsigned NOT NULL   COMMENT 'Expired Balance change source ID',
           `source_type` varchar(20) NOT NULL   COMMENT 'Expired Balance change source type',
           `expiry_date` date NOT NULL   COMMENT 'The balance change expiry date',
           `balance_type_id` int unsigned NOT NULL   COMMENT 'One of the values of the Leave Balance Type option group',
           `leave_date` date    COMMENT 'The Leave date of the expired balance change (i.e If it is a leave request balance change)',
           `leave_request_id` int unsigned    COMMENT 'The Leave Request ID linked to the expired balance change (i.e If it is a leave request balance change)',
           `created_date` datetime    COMMENT 'The date and time this log was created' ,
          PRIMARY KEY (`id`)
      )  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");

    return TRUE;
  }
}
