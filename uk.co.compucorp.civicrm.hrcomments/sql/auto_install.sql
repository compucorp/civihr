-- /*******************************************************
-- *
-- * civicrm_hrcomments_comment
-- *
-- * Comments
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrcomments_comment` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Comment ID',
     `entity_name` varchar(50) NOT NULL   COMMENT 'The Entity name associated with the comment',
     `entity_id` int unsigned NOT NULL   COMMENT 'The Entity ID associated with the comment',
     `text` text NOT NULL   COMMENT 'The comment field',
     `contact_id` int unsigned NOT NULL   COMMENT 'FK to the contact who made the comment',
     `created_at` datetime NOT NULL   COMMENT 'The date and time the comment was added',
     `is_deleted` tinyint   DEFAULT 0 COMMENT 'Whether this comment has been deleted or not',
    PRIMARY KEY ( `id` ),
    INDEX `index_entity_id_entity_name`(entity_id, entity_name),
    CONSTRAINT FK_civicrm_hrcomments_comment_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
