DROP TABLE IF EXISTS `civicrm_hrjobcontract_details`;
DROP TABLE IF EXISTS `civicrm_hrjobcontract_role`;
DROP TABLE IF EXISTS `civicrm_hrjobcontract_pension`;
DROP TABLE IF EXISTS `civicrm_hrjobcontract_leave`;
DROP TABLE IF EXISTS `civicrm_hrjobcontract_hour`;
DROP TABLE IF EXISTS `civicrm_hrjobcontract_health`;
DROP TABLE IF EXISTS `civicrm_hrjobcontract_pay`;
DROP TABLE IF EXISTS `civicrm_hrjobcontract_revision`;
DROP TABLE IF EXISTS `civicrm_hrjobcontract`;


-- /*******************************************************
-- *
-- * civicrm_hrjobcontract
-- *
-- * Base job contract table
-- *
-- *******************************************************/

CREATE TABLE IF NOT EXISTS `civicrm_hrjobcontract` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique HRJobContract ID',
    `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
    `is_primary` tinyint(4) DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `index_is_primary` (`is_primary`),
    INDEX `FK_civicrm_hrjobcontract_contact_id` (contact_id ASC),
    CONSTRAINT `FK_civicrm_hrjobcontract_contact_id`  FOREIGN KEY (`contact_id`)  REFERENCES `civicrm_contact` (`id`)  ON DELETE NO ACTION  ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


-- /*******************************************************
-- *
-- * civicrm_hrjobcontract_revision
-- *
-- * Revision table
-- *
-- *******************************************************/

CREATE TABLE IF NOT EXISTS `civicrm_hrjobcontract_revision` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `jobcontract_id` int(10) unsigned NOT NULL,
    `created_date` datetime DEFAULT NULL,
    `modified_date` datetime DEFAULT NULL,
    `effective_date` DATE NULL DEFAULT NULL,
    `change_reason` INT(3) NULL DEFAULT NULL,
    `status` tinyint(4) DEFAULT NULL,
    `details_revision_id` int(10) unsigned DEFAULT NULL,
    `health_revision_id` int(10) unsigned DEFAULT NULL,
    `hour_revision_id` int(10) unsigned DEFAULT NULL,
    `leave_revision_id` int(10) unsigned DEFAULT NULL,
    `pay_revision_id` int(10) unsigned DEFAULT NULL,
    `pension_revision_id` int(10) unsigned DEFAULT NULL,
    `role_revision_id` int(10) unsigned DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `index_jobcontract_id` (`jobcontract_id`),
    KEY `index_details_revision_id` (`details_revision_id`), 
    KEY `index_health_revision_id` (`health_revision_id`), 
    KEY `index_hour_revision_id` (`hour_revision_id`), 
    KEY `index_leave_revision_id` (`leave_revision_id`), 
    KEY `index_pay_revision_id` (`pay_revision_id`), 
    KEY `index_pension_revision_id` (`pension_revision_id`), 
    KEY `index_role_revision_id` (`role_revision_id`),
    CONSTRAINT `FK_civicrm_hrjobcontract_revision_jobcontract_id` FOREIGN KEY (`jobcontract_id`) REFERENCES `civicrm_hrjobcontract` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


-- /*******************************************************
-- *
-- * civicrm_hrjobcontract_details
-- *
-- * Contract terms relating to job contract details
-- *
-- *******************************************************/

CREATE TABLE IF NOT EXISTS `civicrm_hrjobcontract_details` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `position` varchar(127) DEFAULT NULL,
    `title` varchar(127) DEFAULT NULL,
    `funding_notes` text,
    `contract_type` varchar(63) DEFAULT NULL,
    `period_start_date` date DEFAULT NULL,
    `period_end_date` date DEFAULT NULL,
    `notice_amount` double DEFAULT '0',
    `notice_unit` varchar(63) DEFAULT NULL,
    `notice_amount_employee` double DEFAULT '0',
    `notice_unit_employee` varchar(63) DEFAULT NULL,
    `location` varchar(127) DEFAULT NULL,
    `jobcontract_revision_id` int(10) unsigned DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `index_position` (`position`),
    KEY `index_title` (`title`),
    KEY `index_contract_typ` (`contract_type`),
    KEY `index_location` (`location`),
    KEY `index_jobcontract_revision_id` (`jobcontract_revision_id`),
    CONSTRAINT `FK_civicrm_hrjobcontract_details_contract_revision_id` FOREIGN KEY (`jobcontract_revision_id`) REFERENCES `civicrm_hrjobcontract_revision` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


-- /*******************************************************
-- *
-- * civicrm_hrjobcontract_pay
-- *
-- * Contract terms relating to compensation
-- *
-- *******************************************************/

CREATE TABLE `civicrm_hrjobcontract_pay` (
    `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJobPay ID',
    `pay_scale` varchar(63)    COMMENT 'NJC pay scale, JNC pay scale, Soulbury Pay Agreement',
    `is_paid` int unsigned DEFAULT 0 COMMENT 'Paid, Unpaid, etc',
    `pay_amount` decimal(20,2)   DEFAULT 0 COMMENT 'Amount of currency paid for each unit of work (eg 40 per hour, 400 per day)',
    `pay_unit` varchar(63)    COMMENT 'Unit for expressing pay rate (e.g. amount per hour, amount per week)',
    `pay_currency` varchar(63)    COMMENT 'Unit for expressing pay currency',
    `pay_annualized_est` decimal(20,2)   DEFAULT 0 COMMENT 'Estimated Annual Pay',
    `pay_is_auto_est` tinyint   DEFAULT 1 COMMENT 'Is the estimate automatically calculated',
    `jobcontract_revision_id` INT(10) UNSIGNED DEFAULT NULL,
    PRIMARY KEY ( `id` ),
    INDEX `index_pay_scale`(pay_scale),
    INDEX `index_is_paid`(is_paid),
    INDEX `index_jobcontract_revision_id` (jobcontract_revision_id ASC),
    CONSTRAINT `FK_civicrm_hrjobcontract_pay_jobcontract_revision_id`  FOREIGN KEY (`jobcontract_revision_id`)  REFERENCES `civicrm_hrjobcontract_revision` (`id`)  ON DELETE NO ACTION  ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


-- /*******************************************************
-- *
-- * civicrm_hrjobcontract_health
-- *
-- * Contract terms relating to healthcare
-- *
-- *******************************************************/

CREATE TABLE `civicrm_hrjobcontract_health` (
    `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJobHealth ID',
    `provider` int unsigned    COMMENT 'FK to Contact ID for the organization or company which manages healthcare service',
    `plan_type` varchar(63)   COMMENT '.',
    `description` text    ,
    `dependents` text,
    `provider_life_insurance` int unsigned    COMMENT 'FK to Contact ID for the organization or company which manages life insurance service',
    `plan_type_life_insurance` varchar(63)    COMMENT '.',
    `description_life_insurance` text,
    `dependents_life_insurance` text,
    `jobcontract_revision_id` INT(10) UNSIGNED DEFAULT NULL,
    PRIMARY KEY ( `id` ),
    INDEX `index_provider`(provider),
    INDEX `index_plan_type`(plan_type),
    INDEX `index_provider_life_insurance`(provider_life_insurance),
    INDEX `index_plan_type_life_insurance`(plan_type_life_insurance),
    INDEX `index_jobcontract_revision_id` (jobcontract_revision_id ASC),
    CONSTRAINT `FK_civicrm_hrjobcontract_health_provider` FOREIGN KEY (`provider`)  REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL,
    CONSTRAINT `FK_civicrm_hrjobcontract_health_provider_life_insurance` FOREIGN KEY (`provider_life_insurance`)  REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL,
    CONSTRAINT `FK_civicrm_hrjobcontract_health_jobcontract_revision_id` FOREIGN KEY (`jobcontract_revision_id`)  REFERENCES `civicrm_hrjobcontract_revision` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


-- /*******************************************************
-- *
-- * civicrm_hrjobcontract_hour
-- *
-- * Contract terms relating to hours of work
-- *
-- *******************************************************/

CREATE TABLE `civicrm_hrjobcontract_hour` (
    `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJobHour ID',
    `hours_type` varchar(63)    COMMENT 'Full-Time, Part-Time, Casual',
    `hours_amount` double   DEFAULT 0 COMMENT 'Amount of time allocated for work (in given period)',
    `hours_unit` varchar(63)   COMMENT 'Period during which hours are allocated (eg 5 hours per day; 5 hours per week)',
    `hours_fte` double    COMMENT 'Typically, employment at 40 hr/wk is 1 FTE',
    `fte_num` int unsigned  DEFAULT 1 COMMENT '.',
    `fte_denom` int unsigned   DEFAULT 1  COMMENT '.',
    `jobcontract_revision_id` INT(10) UNSIGNED DEFAULT NULL,
    PRIMARY KEY ( `id` ),
    INDEX `index_hours_type`(hours_type),
    INDEX `index_jobcontract_revision_id` (jobcontract_revision_id ASC),
    CONSTRAINT `FK_civicrm_hrjobcontract_hour_jobcontract_revision_id` FOREIGN KEY (`jobcontract_revision_id`)  REFERENCES `civicrm_hrjobcontract_revision` (`id`)  ON DELETE NO ACTION  ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


-- /*******************************************************
-- *
-- * civicrm_hrjobcontract_leave
-- *
-- * Contract terms relating to leave-entitlements
-- *
-- *******************************************************/

CREATE TABLE `civicrm_hrjobcontract_leave` (
    `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJobLeave ID',
    `leave_type` int unsigned    COMMENT 'The purpose for which leave may be taken (sickness, vacation, etc)',
    `leave_amount` int unsigned    COMMENT 'The number of leave days',
    `jobcontract_revision_id` INT(10) UNSIGNED DEFAULT NULL,
    PRIMARY KEY ( `id` ),
    INDEX `index_jobcontract_revision_id` (jobcontract_revision_id ASC),
    CONSTRAINT `FK_civicrm_hrjobcontract_leave_jobcontract_revision_id`  FOREIGN KEY (`jobcontract_revision_id`)  REFERENCES `civicrm_hrjobcontract_revision` (`id`)  ON DELETE NO ACTION  ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


-- /*******************************************************
-- *
-- * civicrm_hrjobcontract_pension
-- *
-- * Contract terms relating to pensions
-- *
-- *******************************************************/

CREATE TABLE `civicrm_hrjobcontract_pension` (
    `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJobPension ID',
    `is_enrolled` tinyint   DEFAULT 0 ,
    `ee_contrib_pct` double   DEFAULT 0 COMMENT 'Employee Contribution Percentage',
    `er_contrib_pct` double   DEFAULT 0 COMMENT 'Employer Contribution Percentage',
    `pension_type` varchar(63) COMMENT 'Pension Type',
    `ee_contrib_abs` decimal(20,2)   DEFAULT 0 COMMENT 'Employee Contribution Absolute Amount',
    `ee_evidence_note` varchar(127)   COMMENT 'Employee evidence note',
    `jobcontract_revision_id` INT(10) UNSIGNED DEFAULT NULL,
    PRIMARY KEY ( `id` ),
    INDEX `index_is_enrolled`(is_enrolled),
    INDEX `index_jobcontract_revision_id` (jobcontract_revision_id ASC),
    CONSTRAINT `FK_civicrm_hrjobcontract_pension_jobcontract_revision_id`  FOREIGN KEY (`jobcontract_revision_id`)  REFERENCES `civicrm_hrjobcontract_revision` (`id`)  ON DELETE NO ACTION  ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


-- /*******************************************************
-- *
-- * civicrm_hrjobcontract_role
-- *
-- * Semi-official job roles
-- *
-- *******************************************************/

CREATE TABLE `civicrm_hrjobcontract_role` (
    `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJobRole ID',
    `title` varchar(127)    COMMENT 'Negotiated name for the role',
    `description` text    COMMENT 'Negotiated name for the role',
    `hours` double   DEFAULT 0 COMMENT 'Amount of time allocated for work (in a given week)',
    `role_hours_unit` varchar(63)    COMMENT 'Period during which hours are allocated (eg 5 hours per day; 5 hours per week)',
    `region` varchar(127),
    `department` varchar(127),
    `level_type` varchar(63)    COMMENT 'Junior manager, senior manager, etc.',
    `manager_contact_id` int unsigned    COMMENT 'FK to Contact ID',
    `functional_area` varchar(127),
    `organization` varchar(127),
    `cost_center` varchar(127),
    `funder` varchar(127)    COMMENT 'FK to Contact ID',
    `percent_pay_funder` varchar(127)   DEFAULT 0 COMMENT 'Percentage of Pay Assigned to this funder',
    `percent_pay_role` int unsigned   DEFAULT 0 COMMENT 'Percentage of Pay Assigned to this Role',
    `location` varchar(127)    COMMENT 'Main work location',
    `jobcontract_revision_id` INT(10) UNSIGNED DEFAULT NULL,
    PRIMARY KEY ( `id` ),
    INDEX `index_title`(title),
    INDEX `index_region`(region),
    INDEX `index_department`(department),
    INDEX `index_level_type`(level_type),
    INDEX `index_functional_area`(functional_area),
    INDEX `index_organization`(organization),
    INDEX `index_cost_center`(cost_center),
    INDEX `index_funder`(funder),
    INDEX `index_location`(location),
    INDEX `index_jobcontract_revision_id` (jobcontract_revision_id ASC),
    CONSTRAINT FK_civicrm_hrjobcontract_role_manager_contact_id FOREIGN KEY (`manager_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL,
    CONSTRAINT `FK_civicrm_hrjobcontract_role_jobcontract_revision_id`  FOREIGN KEY (`jobcontract_revision_id`)  REFERENCES `civicrm_hrjobcontract_revision` (`id`)  ON DELETE NO ACTION  ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

