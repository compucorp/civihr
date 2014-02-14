DROP TABLE IF EXISTS `civicrm_hrabsence_entitlement`;
DROP TABLE IF EXISTS `civicrm_hrabsence_period`;
DROP TABLE IF EXISTS `civicrm_hrabsence_type`;

-- /*******************************************************
-- *
-- * civicrm_hrabsence_type
-- *
-- * Absence types.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrabsence_type` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Absence type ID',
     `name` varchar(127)    COMMENT 'Name of absence type',
     `title` varchar(127)    COMMENT 'Negotiated name for the Absence Type',
     `is_active` tinyint   DEFAULT 1 ,
     `allow_credits` tinyint   DEFAULT 0 ,
     `credit_activity_type_id` int unsigned NULL  DEFAULT null COMMENT 'FK to civicrm_option_value.id, that has to be valid, registered activity type.',
     `allow_debits` tinyint   DEFAULT 1 ,
     `debit_activity_type_id` int unsigned NULL  DEFAULT null COMMENT 'FK to civicrm_option_value.id, that has to be valid, registered activity type.' 
,
    PRIMARY KEY ( `id` )
 
    ,     INDEX `index_name`(
        name
  )
  ,     INDEX `index_title`(
        title
  )
  ,     INDEX `UI_credit_activity_type_id`(
        credit_activity_type_id
  )
  ,     INDEX `UI_debit_activity_type_id`(
        debit_activity_type_id
  )
  
 
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrabsence_period
-- *
-- * Absence period.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrabsence_period` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Absence period ID',
     `name` varchar(127)    COMMENT 'Name of absence period',
     `title` varchar(127)    COMMENT 'Negotiated name for the Absence period',
     `start_date` timestamp    COMMENT 'Absence Period Start Date',
     `end_date` timestamp    COMMENT 'Absence Period End Date' 
,
    PRIMARY KEY ( `id` )
 
    ,     INDEX `index_name`(
        name
  )
  ,     INDEX `index_title`(
        title
  )
  
 
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrabsence_entitlement
-- *
-- * Absence entitlement.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrabsence_entitlement` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Absence entitlement ID',
     `contact_id` int unsigned    COMMENT 'FK to Contact ID',
     `period_id` int unsigned    COMMENT 'FK to Absence Period ID',
     `type_id` int unsigned    COMMENT 'FK to Absence Type ID',
     `amount` double   DEFAULT 0 COMMENT 'Amount of absence entitlement.' 
,
    PRIMARY KEY ( `id` )
 
 
,          CONSTRAINT FK_civicrm_hrabsence_entitlement_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_hrabsence_entitlement_period_id FOREIGN KEY (`period_id`) REFERENCES `civicrm_hrabsence_period`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_hrabsence_entitlement_type_id FOREIGN KEY (`type_id`) REFERENCES `civicrm_hrabsence_type`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
