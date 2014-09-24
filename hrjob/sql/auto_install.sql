DROP TABLE IF EXISTS `civicrm_hrjob_role`;
DROP TABLE IF EXISTS `civicrm_hrjob_pension`;
DROP TABLE IF EXISTS `civicrm_hrjob_leave`;
DROP TABLE IF EXISTS `civicrm_hrjob_hour`;
DROP TABLE IF EXISTS `civicrm_hrjob_health`;
DROP TABLE IF EXISTS `civicrm_hrjob_pay`;
DROP TABLE IF EXISTS `civicrm_hrjob`;

-- /*******************************************************
-- *
-- * civicrm_hrjob
-- *
-- * Job positions.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJob ID',
     `contact_id` int unsigned    COMMENT 'FK to Contact ID',
     `position` varchar(127)    COMMENT 'Internal name for the job (for HR)',
     `title` varchar(127)    COMMENT 'Negotiated name for the job',
     `funding_notes` text    ,
     `contract_type` varchar(63)    COMMENT 'Contract for employment, internship, etc.',
     `period_type` varchar(63)    COMMENT '.',
     `period_start_date` date    COMMENT 'First day of the job',
     `period_end_date` date    COMMENT 'Last day of the job',
     `notice_amount` double   DEFAULT 0 COMMENT 'Amount of time allocated for notice period from employer. Number part without the unit e.g 3 in 3 Weeks.',
     `notice_unit` varchar(63)    COMMENT 'Unit of a notice period from employer assigned to a quantity e.g Week in 3 Weeks.',
     `notice_amount_employee` double   DEFAULT 0 COMMENT 'Amount of time allocated for notice period from employee. Number part without the unit e.g 3 in 3 Weeks.',
     `notice_unit_employee` varchar(63)    COMMENT 'Unit of a notice period from employee assigned to a quantity e.g Week in 3 Weeks.',
     `location` varchar(127)    COMMENT 'Normal place of work',
     `is_primary` tinyint   DEFAULT 0 COMMENT 'Is this the primary?'
,
    PRIMARY KEY ( `id` )

    ,     INDEX `index_position`(
        position
  )
  ,     INDEX `index_title`(
        title
  )
  ,     INDEX `index_contract_type`(
        contract_type
  )
  ,     INDEX `index_period_type`(
        period_type
  )
  ,     INDEX `index_location`(
        location
  )
  ,     INDEX `index_is_primary`(
        is_primary
  )

,          CONSTRAINT FK_civicrm_hrjob_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrjob_pay
-- *
-- * Contract terms relating to compensation
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob_pay` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJobPay ID',
     `job_id` int unsigned NOT NULL   COMMENT 'FK to Job',
     `pay_scale` varchar(63)    COMMENT 'NJC pay scale, JNC pay scale, Soulbury Pay Agreement',
     `is_paid` int unsigned DEFAULT 0 COMMENT 'Paid, Unpaid, etc',
     `pay_amount` decimal(20,2)   DEFAULT 0 COMMENT 'Amount of currency paid for each unit of work (eg 40 per hour, 400 per day)',
     `pay_unit` varchar(63)    COMMENT 'Unit for expressing pay rate (e.g. amount per hour, amount per week)',
     `pay_currency` varchar(63)    COMMENT 'Unit for expressing pay currency',
     `pay_annualized_est` decimal(20,2)   DEFAULT 0 COMMENT 'Estimated Annual Pay',
     `pay_is_auto_est` tinyint   DEFAULT 1 COMMENT 'Is the estimate automatically calculated'
,
    PRIMARY KEY ( `id` )

    ,     UNIQUE INDEX `UI_job_id`(
        job_id
  )
  ,     INDEX `index_pay_scale`(
        pay_scale
  )
  ,     INDEX `index_is_paid`(
        is_paid
   )

,          CONSTRAINT FK_civicrm_hrjob_pay_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrjob_health
-- *
-- * Contract terms relating to healthcare
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob_health` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJobHealth ID',
     `job_id` int unsigned NOT NULL   COMMENT 'FK to Job',
     `provider` int unsigned    COMMENT 'FK to Contact ID for the organization or company which manages healthcare service',
     `plan_type` varchar(63)   COMMENT '.',
     `description` text    ,
     `dependents` text,
     `provider_life_insurance` int unsigned    COMMENT 'FK to Contact ID for the organization or company which manages life insurance service',
     `plan_type_life_insurance` varchar(63)    COMMENT '.',
     `description_life_insurance` text,
     `dependents_life_insurance` text
,
    PRIMARY KEY ( `id` )

    ,     UNIQUE INDEX `UI_job_id`(
        job_id
  )
  ,     INDEX `index_provider`(
        provider
  )
  ,     INDEX `index_plan_type`(
        plan_type
  )
  ,     INDEX `index_provider_life_insurance`(
        provider_life_insurance
  )
  ,     INDEX `index_plan_type_life_insurance`(
        plan_type_life_insurance
  )

,          CONSTRAINT FK_civicrm_hrjob_health_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE, CONSTRAINT `FK_civicrm_hrjob_health_provider` FOREIGN KEY (`provider`)  REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL, CONSTRAINT `FK_civicrm_hrjob_health_provider_life_insurance` FOREIGN KEY (`provider_life_insurance`)  REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrjob_hour
-- *
-- * Contract terms relating to hours of work
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob_hour` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJobHour ID',
     `job_id` int unsigned NOT NULL   COMMENT 'FK to Job',
     `hours_type` varchar(63)    COMMENT 'Full-Time, Part-Time, Casual',
     `hours_amount` double   DEFAULT 0 COMMENT 'Amount of time allocated for work (in given period)',
     `hours_unit` varchar(63)   COMMENT 'Period during which hours are allocated (eg 5 hours per day; 5 hours per week)',
     `hours_fte` double    COMMENT 'Typically, employment at 40 hr/wk is 1 FTE',
     `fte_num` int unsigned  DEFAULT 1 COMMENT '.',
     `fte_denom` int unsigned   DEFAULT 1  COMMENT '.'
,
    PRIMARY KEY ( `id` )

    ,     UNIQUE INDEX `UI_job_id`(
        job_id
  )
  ,     INDEX `index_hours_type`(
        hours_type
  )

,          CONSTRAINT FK_civicrm_hrjob_hour_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrjob_leave
-- *
-- * Contract terms relating to leave-entitlements
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob_leave` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJobLeave ID',
     `job_id` int unsigned NOT NULL   COMMENT 'FK to Job',
     `leave_type` int unsigned    COMMENT 'The purpose for which leave may be taken (sickness, vacation, etc)',
     `leave_amount` int unsigned    COMMENT 'The number of leave days'
,
    PRIMARY KEY ( `id` )

    ,     UNIQUE INDEX `UI_leave_type`(
        job_id
      , leave_type
  )

,          CONSTRAINT FK_civicrm_hrjob_leave_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrjob_pension
-- *
-- * Contract terms relating to pensions
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob_pension` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJobPension ID',
     `job_id` int unsigned NOT NULL   COMMENT 'FK to Job',
     `is_enrolled` tinyint   DEFAULT 0 ,
     `ee_contrib_pct` double   DEFAULT 0 COMMENT 'Employee Contribution Percentage',
     `er_contrib_pct` double   DEFAULT 0 COMMENT 'Employer Contribution Percentage',
     `pension_type` varchar(63) COMMENT 'Pension Type',
     `ee_contrib_abs` decimal(20,2)   DEFAULT 0 COMMENT 'Employee Contribution Absolute Amount',
     `ee_evidence_note` varchar(127)   COMMENT 'Employee evidence note'
,
    PRIMARY KEY ( `id` )

    ,     UNIQUE INDEX `UI_job_id`(
        job_id
  )
  ,     INDEX `index_is_enrolled`(
        is_enrolled
  )

,          CONSTRAINT FK_civicrm_hrjob_pension_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;



-- /*******************************************************
-- *
-- * civicrm_hrjob_role
-- *
-- * Semi-official job roles
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob_role` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJobRole ID',
     `job_id` int unsigned NOT NULL   COMMENT 'FK to Job',
     `title` varchar(127)    COMMENT 'Negotiated name for the role',
     `description` text    COMMENT 'Negotiated name for the role',
     `hours` double   DEFAULT 0 COMMENT 'Amount of time allocated for work (in a given week)',
     `role_hours_unit` varchar(63)    COMMENT 'Period during which hours are allocated (eg 5 hours per day; 5 hours per week)',
     `region` varchar(127)    ,
     `department` varchar(127)    ,
     `level_type` varchar(63)    COMMENT 'Junior manager, senior manager, etc.',
     `manager_contact_id` int unsigned    COMMENT 'FK to Contact ID',
     `functional_area` varchar(127)    ,
     `organization` varchar(127)    ,
     `cost_center` varchar(127)    ,
     `funder` varchar(127)    COMMENT 'FK to Contact ID',
     `percent_pay_funder` varchar(127)   DEFAULT 0 COMMENT 'Percentage of Pay Assigned to this funder',
     `percent_pay_role` int unsigned   DEFAULT 0 COMMENT 'Percentage of Pay Assigned to this Role',
     `location` varchar(127)    COMMENT 'Main work location'
,
    PRIMARY KEY ( `id` )

    ,     INDEX `index_title`(
        title
  )
  ,     INDEX `index_region`(
        region
  )
  ,     INDEX `index_department`(
        department
  )
  ,     INDEX `index_level_type`(
        level_type
  )
  ,     INDEX `index_functional_area`(
        functional_area
  )
  ,     INDEX `index_organization`(
        organization
  )
  ,     INDEX `index_cost_center`(
        cost_center
  )
  ,     INDEX `index_funder`(
        funder
  )
  ,     INDEX `index_location`(
        location
  )

,          CONSTRAINT FK_civicrm_hrjob_role_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_hrjob_role_manager_contact_id FOREIGN KEY (`manager_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

