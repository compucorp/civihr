SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `civicrm_contactaccessrights_rights`;

-- /*******************************************************
-- *
-- * civicrm_contactaccessrights_rights
-- *
-- * Access rights for contacts
-- *
-- *******************************************************/
CREATE TABLE `civicrm_contactaccessrights_rights` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Rights ID',
     `contact_id` int unsigned    COMMENT 'FK to Contact',
     `entity_type` varchar(256)    COMMENT 'Type of entity the permission grants access to (basically, option group name).',
     `entity_id` int unsigned NOT NULL COMMENT 'ID of the entity the permission grants access to (basically, option value ID).'
,
    PRIMARY KEY ( `id` )


,          CONSTRAINT FK_civicrm_contactaccessrights_rights_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;