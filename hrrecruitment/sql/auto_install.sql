-- /*******************************************************
-- *
-- * civicrm_hrvacancy
-- *
-- * Recruitment Vacancy.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrvacancy` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Recruitment Vacancy ID',
     `salary` varchar(127)    COMMENT 'Salary offered in vacancy',
     `position` varchar(127)    COMMENT 'Job Position offered in vacancy',
     `description` varchar(254)    COMMENT 'Description of vacancy',
     `benefits` varchar(254)    ,
     `requirements` varchar(254)    COMMENT 'Requirements of vacancy',
     `location` varchar(254)    COMMENT 'Location of vacancy',
     `is_template` tinyint   DEFAULT 0 COMMENT 'Whether the Vacancy has template',
     `status_id` int unsigned    COMMENT 'Status of Vacancy',
     `start_date` datetime    COMMENT 'Vacancy Start Date',
     `end_date` datetime    COMMENT 'Vacancy End Date',
     `created_date` datetime    COMMENT 'Vacancy Created Date',
     `created_id` int unsigned COMMENT 'FK to civicrm_contact, who created this vacancy'
,
    PRIMARY KEY ( `id` )
 
, CONSTRAINT FK_civicrm_vacancy_created_id FOREIGN KEY (`created_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrvacancy_stage
-- *
-- * Recruitment Vacancy Stages.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrvacancy_stage` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Vacancy Stage ID',
     `case_status_id` int unsigned NOT NULL   COMMENT 'Case Status ID',
     `vacancy_id` int unsigned NOT NULL   COMMENT 'FK to Vacancy ID',
     `weight` int unsigned     
,
    PRIMARY KEY ( `id` )
 
 
,          CONSTRAINT FK_civicrm_hrvacancy_stage_vacancy_id FOREIGN KEY (`vacancy_id`) REFERENCES `civicrm_hrvacancy`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrvacancy_permission
-- *
-- * Recruitment Vacancy Permissions.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrvacancy_permission` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Vacancy Permission ID',
     `contact_id` int unsigned NOT NULL   COMMENT 'FK to Contact ID',
     `vacancy_id` int unsigned NOT NULL   COMMENT 'FK to Vacancy ID',
     `permission` varchar(127)    COMMENT 'Permission of Vacancy' 
,
    PRIMARY KEY ( `id` )
 
 
,          CONSTRAINT FK_civicrm_hrvacancy_permission_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_hrvacancy_permission_vacancy_id FOREIGN KEY (`vacancy_id`) REFERENCES `civicrm_hrvacancy`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
