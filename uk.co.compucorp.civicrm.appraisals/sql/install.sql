SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_appraisal_cycle`;
DROP TABLE IF EXISTS `civicrm_appraisal`;
DROP TABLE IF EXISTS `civicrm_appraisal_criteria`;

CREATE TABLE IF NOT EXISTS `civicrm_appraisal_cycle` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Appraisal ID',
    `name` varchar(256) DEFAULT NULL,
    `cycle_start_date` DATE DEFAULT NULL,
    `cycle_end_date` DATE DEFAULT NULL,
    `self_appraisal_due` DATE DEFAULT NULL,
    `manager_appraisal_due` DATE DEFAULT NULL,
    `grade_due` DATE DEFAULT NULL,
    `type_id` INT(10) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `civicrm_appraisal` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Appraisal ID',
    `appraisal_cycle_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Appraisal Cycle ID',
    `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
    `manager_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
    `self_appraisal_file_id` int(10) unsigned DEFAULT NULL COMMENT 'Relation to File ID',
    `manager_appraisal_file_id` int(10) unsigned DEFAULT NULL COMMENT 'Relation to File ID',
    `self_appraisal_due` DATE DEFAULT NULL,
    `manager_appraisal_due` DATE DEFAULT NULL,
    `grade_due` DATE DEFAULT NULL,
    `due_changed` TINYINT(1) UNSIGNED DEFAULT 0,
    `meeting_date` DATETIME DEFAULT NULL,
    `meeting_completed` TINYINT(1) UNSIGNED DEFAULT 0,
    `approved_by_employee` TINYINT(1) UNSIGNED DEFAULT 0,
    `grade` int(3) UNSIGNED DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `status_id` INT( 10 ) NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    INDEX `FK_civicrm_appraisal_appraisal_cycle_id` (appraisal_cycle_id ASC),
    INDEX `FK_civicrm_appraisal_contact_id` (contact_id ASC),
    INDEX `FK_civicrm_appraisal_manager_id` (manager_id ASC),
    CONSTRAINT `FK_civicrm_appraisal_appraisal_cycle_id`  FOREIGN KEY (`appraisal_cycle_id`)  REFERENCES `civicrm_appraisal_cycle` (`id`)  ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `FK_civicrm_appraisal_contact_id`  FOREIGN KEY (`contact_id`)  REFERENCES `civicrm_contact` (`id`)  ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `FK_civicrm_appraisal_manager_id`  FOREIGN KEY (`manager_id`)  REFERENCES `civicrm_contact` (`id`)  ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `civicrm_appraisal_criteria` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Appraisal Criteria ID',
    `value` INT(10) DEFAULT NULL COMMENT 'Grade value',
    `label` VARCHAR(64) DEFAULT NULL COMMENT 'Grade label',
    `is_active` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

SET FOREIGN_KEY_CHECKS=1;
