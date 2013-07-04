DROP TABLE IF EXISTS `civicrm_hrjob_role`;
DROP TABLE IF EXISTS `civicrm_hrjob_pension`;
DROP TABLE IF EXISTS `civicrm_hrjob_leave`;
DROP TABLE IF EXISTS `civicrm_hrjob_hour`;
DROP TABLE IF EXISTS `civicrm_hrjob_health`;
DROP TABLE IF EXISTS `civicrm_hrjob_comp`;
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
     `position` varchar(127)    COMMENT 'HR internal name for the job',
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
        position
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
-- * civicrm_hrjob_comp
-- *
-- * Contract terms relating to compensation
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob_comp` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJob ID',
     `contact_id` int unsigned    COMMENT 'FK to Contact',
     `job_id` int unsigned    COMMENT 'FK to Job' 
,
    PRIMARY KEY ( `id` )
 
 
,          CONSTRAINT FK_civicrm_hrjob_comp_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_hrjob_comp_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrjob_health
-- *
-- * Contract terms relating to healthcare
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob_health` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJob ID',
     `contact_id` int unsigned    COMMENT 'FK to Contact',
     `job_id` int unsigned    COMMENT 'FK to Job' 
,
    PRIMARY KEY ( `id` )
 
 
,          CONSTRAINT FK_civicrm_hrjob_health_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_hrjob_health_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrjob_hour
-- *
-- * Contract terms relating to hours of work
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob_hour` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJob ID',
     `contact_id` int unsigned    COMMENT 'FK to Contact',
     `job_id` int unsigned    COMMENT 'FK to Job' 
,
    PRIMARY KEY ( `id` )
 
 
,          CONSTRAINT FK_civicrm_hrjob_hour_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_hrjob_hour_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrjob_leave
-- *
-- * Contract terms relating to leave-entitlements
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob_leave` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJob ID',
     `contact_id` int unsigned    COMMENT 'FK to Contact',
     `job_id` int unsigned    COMMENT 'FK to Job' 
,
    PRIMARY KEY ( `id` )
 
 
,          CONSTRAINT FK_civicrm_hrjob_leave_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_hrjob_leave_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrjob_pension
-- *
-- * Contract terms relating to pensions
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob_pension` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJob ID',
     `contact_id` int unsigned    COMMENT 'FK to Contact',
     `job_id` int unsigned    COMMENT 'FK to Job' 
,
    PRIMARY KEY ( `id` )
 
 
,          CONSTRAINT FK_civicrm_hrjob_pension_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_hrjob_pension_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrjob_role
-- *
-- * Semi-official job roles
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrjob_role` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique HRJob ID',
     `contact_id` int unsigned    COMMENT 'FK to Contact',
     `job_id` int unsigned    COMMENT 'FK to Job' 
,
    PRIMARY KEY ( `id` )
 
 
,          CONSTRAINT FK_civicrm_hrjob_role_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_hrjob_role_job_id FOREIGN KEY (`job_id`) REFERENCES `civicrm_hrjob`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
