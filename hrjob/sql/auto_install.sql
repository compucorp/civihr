DROP TABLE IF EXISTS `civicrm_hrjob`;

-- /*******************************************************
-- *
-- * civicrm_hrjob
-- *
-- * HRJob information for a specific location.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJob ID',
     `contact_id` int unsigned    COMMENT 'FK to Contact ID',
     `is_primary` tinyint   DEFAULT 0 COMMENT 'Is this the primary?'
,
    PRIMARY KEY ( `id` )

    ,     INDEX `index_is_primary`(
        is_primary
  )

,          CONSTRAINT FK_civicrm_hrjob_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
