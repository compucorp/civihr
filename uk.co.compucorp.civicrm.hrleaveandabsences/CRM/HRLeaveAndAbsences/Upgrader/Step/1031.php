<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1031 {

  /**
   * Creates the LeaveRequestCalendarFeedConfig table if
   * it does not already exist.
   *
   * @return bool
   */
  public function upgrade_1031() {
    CRM_Core_DAO::executeQuery("
      CREATE TABLE IF NOT EXISTS `civicrm_hrleaveandabsences_calendar_feed_config` (
      
           `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique LeaveRequestCalendarFeedConfig ID',
           `title` varchar(127) NOT NULL   COMMENT 'The Calendar Feed Config Title. There cannot be more than one entity with the same title',
           `timezone` varchar(50) NOT NULL   COMMENT 'The Calendar Feed Config Timezone',
           `hash` varchar(32) NOT NULL   COMMENT 'The Calendar Feed Config Hash. Should be unique per config',
           `composed_of` text NOT NULL   COMMENT 'The components of the leave request feed, e.g leave types, departments',
           `visible_to` text NOT NULL   COMMENT 'Who gets to view the feed link e.g staff in certain departments or location',
           `is_active` tinyint NOT NULL  DEFAULT 1 COMMENT 'Whether the feed is active or not',
           `created_date` datetime NOT NULL   COMMENT 'The date and time this Calendar Feed Config was created',
          PRIMARY KEY (`id`),     
          UNIQUE INDEX `unique_calendar_title`(title),
          UNIQUE INDEX `unique_calendar_hash`(hash)
      )  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;");

    return TRUE;
  }
}
