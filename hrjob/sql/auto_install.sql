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
     `is_tied_to_funding` tinyint   DEFAULT 0 ,
     `contract_type` varchar(63)    COMMENT 'Contract for employment, internship, etc.',
     `seniority` varchar(63)    COMMENT 'Junior manager, senior manager, etc.',
     `period_type` enum('Temporary', 'Permanent')    COMMENT '.',
     `period_start_date` date    COMMENT 'First day of the job',
     `period_end_date` date    COMMENT 'Last day of the job',
     `manager_contact_id` int unsigned    COMMENT 'FK to Contact ID',
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
  ,     INDEX `index_seniority`(
        seniority
  )
  ,     INDEX `index_period_type`(
        period_type
  )
  ,     INDEX `index_is_primary`(
        is_primary
  )
  
,          CONSTRAINT FK_civicrm_hrjob_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_hrjob_manager_contact_id FOREIGN KEY (`manager_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL  
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
     `pay_grade` varchar(63)    COMMENT 'Paid, Unpaid, etc',
     `pay_amount` decimal(20,2) NOT NULL  DEFAULT 0 COMMENT 'Amount of currency paid for each unit of work (eg 40 per hour, 400 per day)',
     `pay_unit` enum('Hour', 'Day', 'Week', 'Month', 'Year') NOT NULL   COMMENT 'Unit for expressing pay rate (e.g. amount per hour, amount per week)' 
,
    PRIMARY KEY ( `id` )
 
    ,     UNIQUE INDEX `UI_job_id`(
        job_id
  )
  ,     INDEX `index_pay_grade`(
        pay_grade
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
     `provider` varchar(63)    COMMENT 'The organization or company which manages healthcare service',
     `plan_type` enum('Family', 'Individual')    COMMENT '.',
     `description` text    ,
     `dependents` text     
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
  
,          CONSTRAINT FK_civicrm_hrjob_health_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE  
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
     `hours_amount` decimal(20,2) NOT NULL  DEFAULT 0 COMMENT 'Amount of time allocated for work (in given period)',
     `hours_unit` enum('Day', 'Week', 'Month', 'Year') NOT NULL   COMMENT 'Period during which hours are allocated (eg 5 hours per day; 5 hours per week)',
     `hours_fte` decimal(20,2)    COMMENT 'Typically, employment at 40 hr/wk is 1 FTE' 
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
     `leave_type` varchar(63)    COMMENT 'The purpose for which leave may be taken (sickness, vacation, etc)',
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
     `contrib_pct` decimal(20,2)   DEFAULT 0 COMMENT '??' 
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
     `hours` decimal(20,2)   DEFAULT 0 COMMENT 'Amount of time allocated for work (in a given week)',
     `region` varchar(127)    ,
     `department` varchar(127)    ,
     `manager_contact_id` int unsigned    COMMENT 'FK to Contact ID',
     `functional_area` varchar(127)    ,
     `organization` varchar(127)    ,
     `cost_center` varchar(127)     
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
  ,     INDEX `index_functional_area`(
        functional_area
  )
  ,     INDEX `index_organization`(
        organization
  )
  ,     INDEX `index_cost_center`(
        cost_center
  )
  
,          CONSTRAINT FK_civicrm_hrjob_role_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_hrjob_role_manager_contact_id FOREIGN KEY (`manager_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL  
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
