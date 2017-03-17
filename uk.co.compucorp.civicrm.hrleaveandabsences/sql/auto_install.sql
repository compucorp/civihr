-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_absence_type
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_absence_type` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique AbsenceType ID',
     `title` varchar(127) NOT NULL   COMMENT 'The AbsenceType title. There cant be more than one entity with the same title',
     `weight` int unsigned NOT NULL   COMMENT 'The weight value is used to order the types on a list',
     `color` varchar(7) NOT NULL   COMMENT 'The color hex value (including the #) used to display this type on a calendar',
     `is_default` tinyint   DEFAULT 0 COMMENT 'There can only be one default entity at any given time',
     `is_reserved` tinyint   DEFAULT 0 COMMENT 'Reserved entities are used by internal calculations and cannot be deleted.',
     `max_consecutive_leave_days` int unsigned    ,
     `allow_request_cancelation` int unsigned NOT NULL   COMMENT 'Can only be one of the values defined in AbsenceType::REQUEST_CANCELATION_OPTIONS',
     `allow_overuse` tinyint   DEFAULT 0 ,
     `must_take_public_holiday_as_leave` tinyint   DEFAULT 0 ,
     `default_entitlement` int unsigned NOT NULL   COMMENT 'The number of days entitled for this type',
     `add_public_holiday_to_entitlement` tinyint   DEFAULT 0 ,
     `is_active` tinyint   DEFAULT 1 COMMENT 'Only enabled types can be requested',
     `allow_accruals_request` tinyint   DEFAULT 0 ,
     `max_leave_accrual` int unsigned    COMMENT 'Value is the number of days that can be accrued. Null means unlimited',
     `allow_accrue_in_the_past` tinyint   DEFAULT 0 ,
     `accrual_expiration_duration` int unsigned    COMMENT 'An amount of accrual_expiration_unit',
     `accrual_expiration_unit` int unsigned    COMMENT 'The unit (months or days) of accrual_expiration_duration of this type default expiry',
     `allow_carry_forward` tinyint   DEFAULT 0 ,
     `max_number_of_days_to_carry_forward` int unsigned    ,
     `carry_forward_expiration_duration` int unsigned    COMMENT 'An amount of carry_forward_expiration_unit',
     `carry_forward_expiration_unit` int unsigned    COMMENT 'The unit (months or days) of carry_forward_expiration_duration of this type default expiry',
     `is_sick` tinyint   DEFAULT 0 COMMENT 'A flag which is used to determine if this Absence Type can be used for a Sickness Request',
    PRIMARY KEY ( `id` ),
    UNIQUE INDEX `hrleaveandabsences_absence_type_title`(title)



)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * default absence types
-- *
-- *******************************************************/
INSERT INTO `civicrm_hrleaveandabsences_absence_type`(
  id,
  title,
  color,
  must_take_public_holiday_as_leave,
  default_entitlement,
  allow_request_cancelation,
  add_public_holiday_to_entitlement,
  allow_accruals_request,
  allow_carry_forward,
  max_number_of_days_to_carry_forward,
  carry_forward_expiration_duration,
  carry_forward_expiration_unit,
  is_reserved,
  is_default,
  weight
) VALUES (
  1,
  'Holiday / Vacation',
  '#151D2C',
  1,
  20,
  3,
  1,
  0,
  1,
  5,
  12,
  2, -- Months
  1,
  1,
  1
);

INSERT INTO `civicrm_hrleaveandabsences_absence_type`(
  id,
  title,
  color,
  must_take_public_holiday_as_leave,
  default_entitlement,
  allow_request_cancelation,
  allow_accruals_request,
  max_leave_accrual,
  accrual_expiration_duration,
  accrual_expiration_unit,
  is_reserved,
  weight
) VALUES (
  2,
  'TOIL',
  '#056780',
  0,
  0,
  3,
  1,
  5,
  3,
  2, -- months
  1,
  2
);

INSERT INTO `civicrm_hrleaveandabsences_absence_type`(
  id,
  title,
  color,
  must_take_public_holiday_as_leave,
  default_entitlement,
  allow_request_cancelation,
  allow_overuse,
  is_reserved,
  weight,
  is_sick
) VALUES (
  3,
  'Sick',
  '#B32E2E',
  0,
  0,
  1, -- no
  1,
  1,
  3,
  1
);

-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_notification_receiver
-- *
-- * A contact that will be notified of new leave requests of this type
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_notification_receiver` (
     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique NotificationReceiver ID',
     `type_id` int unsigned NOT NULL   COMMENT 'FK to AbsenceType',
     `contact_id` int unsigned NOT NULL   COMMENT 'FK to Contact',
    PRIMARY KEY ( `id` ),
    CONSTRAINT FK_civicrm_hrleaveandabsences_notification_receiver_type_id
      FOREIGN KEY (`type_id`) REFERENCES `civicrm_hrleaveandabsences_absence_type`(`id`) ON DELETE CASCADE,
    CONSTRAINT FK_civicrm_hrleaveandabsences_notification_receiver_contact_id
      FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_work_pattern
-- *
-- * This entity holds the basic description about a Work Pattern
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_work_pattern` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique WorkPattern ID',
     `label` varchar(127) NOT NULL   COMMENT 'The Work Pattern label\'s (name)',
     `description` varchar(255)    COMMENT 'A small description of the Work Pattern',
     `is_default` tinyint   DEFAULT 0 COMMENT 'There can only be one default entity at any given time',
     `is_active` tinyint   DEFAULT 1 COMMENT 'Only enabled Work Patterns can be used. The is_active name is used to follow Civi\'s conventions.',
     `weight` int unsigned NOT NULL   COMMENT 'The weight value is used to order the Work Patterns on a list',
    PRIMARY KEY ( `id` ),
    UNIQUE INDEX `work_pattern_unique_label`(label)


)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_work_week
-- *
-- * A Work Pattern can have multiple Work Weeks. A Work Week contains a set of Work Days that, together, make the Work Pattern
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_work_week` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique WorkWeek ID',
     `number` int unsigned NOT NULL   COMMENT 'Each Week of a Pattern has a unique and sequential number',
     `pattern_id` int unsigned NOT NULL   COMMENT 'The Work Pattern this Week belongs to',
    PRIMARY KEY ( `id` ),
    CONSTRAINT FK_civicrm_hrleaveandabsences_work_week_pattern_id
      FOREIGN KEY (`pattern_id`)
      REFERENCES `civicrm_hrleaveandabsences_work_pattern`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_work_day
-- *
-- * The specific details of day in a Work Week.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_work_day` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique WorkDay ID',
     `day_of_the_week` int unsigned NOT NULL   COMMENT 'A number between 1 and 7, following ISO-8601. 1 is Monday and 7 is Sunday',
     `type` varchar(512) NOT NULL   COMMENT 'The type of this day, according to the values on the Work Day Type Option Group',
     `time_from` char(5)    COMMENT 'The start time of this work day. This field is a char because CiviCRM can\'t handle TIME fields.',
     `time_to` char(5)    COMMENT 'The end time of this work day. This field is a char because CiviCRM can\'t handle TIME fields.',
     `break` decimal(20,2)    COMMENT 'The amount of break time (in hours) allowed for this day. ',
     `leave_days` int unsigned    COMMENT 'The proportion of a days leave that will be deducted if this day is taken as leave.',
     `number_of_hours` decimal(20,2)    COMMENT 'This is the number of hours between time_from and time_to minus break',
     `week_id` int unsigned NOT NULL   COMMENT 'The Work Week this Day belongs to',
    PRIMARY KEY ( `id` ),
    UNIQUE INDEX `unique_day_for_week`(week_id, day_of_the_week),
    CONSTRAINT FK_civicrm_hrleaveandabsences_work_day_week_id
      FOREIGN KEY (`week_id`)
      REFERENCES `civicrm_hrleaveandabsences_work_week`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * default work pattern
-- *
-- *******************************************************/
INSERT INTO civicrm_hrleaveandabsences_work_pattern(id, label, description, is_default, is_active, weight)
VALUES(1, 'Default 5 day week (London)', 'A standard 37.5 week', 1, 1, 1);

INSERT INTO civicrm_hrleaveandabsences_work_week(id, pattern_id, number)
VALUES(1, 1, 1);

INSERT INTO civicrm_hrleaveandabsences_work_day(week_id, day_of_the_week, type, time_from, time_to, break, leave_days, number_of_hours)
VALUES
  (1, '1', 2, '09:00', '17:30', 1, 1, 7.5),
  (1, '2', 2, '09:00', '17:30', 1, 1, 7.5),
  (1, '3', 2, '09:00', '17:30', 1, 1, 7.5),
  (1, '4', 2, '09:00', '17:30', 1, 1, 7.5),
  (1, '5', 2, '09:00', '17:30', 1, 1, 7.5),
  (1, '6', 3, NULL, NULL, NULL, NULL, NULL),
  (1, '7', 3, NULL, NULL, NULL, NULL, NULL);

-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_absence_period
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_absence_period` (

     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique AbsencePeriod ID',
     `title` varchar(127) NOT NULL   COMMENT 'The AbsencePeriod title. There cannot be more than one entity with the same title',
     `start_date` date NOT NULL   COMMENT 'The date this Absence Period starts',
     `end_date` date NOT NULL   COMMENT 'The date this Absence Period ends',
     `weight` int unsigned NOT NULL   COMMENT 'The weight value is used to order the types on a list',
    PRIMARY KEY ( `id` ),
    UNIQUE INDEX `unique_absence_period`(title)


)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_public_holiday
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_public_holiday` (
     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Public Holiday ID',
     `title` varchar(127) NOT NULL   COMMENT 'The Public Holiday title',
     `date` date NOT NULL   COMMENT 'The date of Public Holiday. There can\'t be more than one Public Holiday on the same date',
     `is_active` tinyint   DEFAULT 1 COMMENT 'Determines if Public Holiday entry is active. The is_active name is used to follow Civi\'s conventions',
    PRIMARY KEY ( `id` ),
    UNIQUE INDEX `unique_public_holiday`(date)
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_leave_period_entitlement
-- *
-- * A period entitlement for an specific set a contract and absence type
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_leave_period_entitlement` (


  `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Leave Period Entitlement ID',
  `period_id` int unsigned NOT NULL   COMMENT 'FK to AbsencePeriod',
  `type_id` int unsigned NOT NULL   COMMENT 'FK to AbsenceType',
  `contact_id` int unsigned NOT NULL   COMMENT 'FK to Contact (civicrm_contact)',
  `overridden` tinyint   DEFAULT false COMMENT 'Indicates if the entitlement was overridden',
  `comment` text    COMMENT 'The comment added by the user about the calculation for this entitlement',
  `comment_author_id` int unsigned    COMMENT 'FK to Contact. The contact that represents the user who added the comment to this entitlement',
  `comment_date` datetime    COMMENT 'The date and time the comment for this entitlement was added/updated',
  PRIMARY KEY ( `id` ),
  UNIQUE INDEX `unique_entitlement`(period_id, contact_id, type_id),
  CONSTRAINT FK_civicrm_hrlaa_leave_period_entitlement_period_id FOREIGN KEY (`period_id`) REFERENCES `civicrm_hrleaveandabsences_absence_period`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_hrlaa_leave_period_entitlement_type_id FOREIGN KEY (`type_id`) REFERENCES `civicrm_hrleaveandabsences_absence_type`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_hrlaa_leave_period_entitlement_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_hrlaa_leave_period_entitlement_comment_author_id FOREIGN KEY (`comment_author_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;

-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_leave_balance_change
-- *
-- * Store balance changes to a Leave Period Entitlement
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_leave_balance_change` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique LeaveBalanceChange ID',
     `type_id` int unsigned NOT NULL   COMMENT 'One of the values of the Leave Balance Type option group',
     `amount` decimal(20,2) NOT NULL  DEFAULT 0 COMMENT 'The amount of days this change in balance represents to the entitlement',
     `expiry_date` date    COMMENT 'Some balance changes can expire. This is the date it will expire.',
     `expired_balance_change_id` int unsigned    COMMENT 'FK to LeaveBalanceChange. This is only used for a balance change that represents expired days, and it will be related to the balance change that has expired.',
     `source_id` int unsigned    COMMENT 'Some balance changes are originated from an specific source (a leave request date, for example) and this field will have the ID of this source.' ,
     `source_type` varchar(20)    COMMENT 'Some balance changes are originated from an specific source (a leave request date, for example) and this field will have text string to indicate what is the source.' ,
    PRIMARY KEY ( `id` ),
    UNIQUE INDEX `unique_expiry_record`(expired_balance_change_id),
    INDEX `index_source_id`(source_id, source_type),
    CONSTRAINT FK_civicrm_hrlaa_leave_balance_change_expired_balance_change_id FOREIGN KEY (`expired_balance_change_id`) REFERENCES `civicrm_hrleaveandabsences_leave_balance_change`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;


-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_leave_request
-- *
-- * Leave Requests
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_leave_request` (

     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique LeaveRequest ID',
     `type_id` int unsigned NOT NULL   COMMENT 'FK to AbsenceType',
     `contact_id` int unsigned NOT NULL   COMMENT 'FK to Contact',
     `status_id` int unsigned NOT NULL   COMMENT 'One of the values of the Leave Request Status option group',
     `from_date` date NOT NULL   COMMENT 'The date the leave request starts.',
     `from_date_type` int unsigned NOT NULL   COMMENT 'One of the values of the Leave Request Day Type option group',
     `to_date` date NOT NULL   COMMENT 'The date the leave request ends',
     `to_date_type` int unsigned NOT NULL   COMMENT 'One of the values of the Leave Request Day Type option group',
     `sickness_reason` varchar(512)    COMMENT 'One of the values of the Sickness Reason option group',
     `sickness_required_documents` varchar(10)    COMMENT 'A list of values from the LeaveRequestRequiredDocument option group',
     `toil_duration` int unsigned    COMMENT 'The duration of the overtime work in minutes',
     `toil_to_accrue` int unsigned    COMMENT 'The amount of days accrued for this toil request',
     `toil_expiry_date` date    COMMENT 'The expiry date of this TOIL Request. When null, it means it never expires.',
     `request_type` varchar(20) NOT NULL   COMMENT 'The type of this request (leave, toil, sickness etc)',
     `is_deleted` tinyint   DEFAULT 0 COMMENT 'Whether this leave request has been deleted or not',
    PRIMARY KEY ( `id` ),
    CONSTRAINT FK_civicrm_hrlaa_leave_request_type_id FOREIGN KEY (`type_id`) REFERENCES `civicrm_hrleaveandabsences_absence_type`(`id`) ON DELETE CASCADE,
    CONSTRAINT FK_civicrm_hrlaa_leave_request_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_leave_request_date
-- *
-- * The individual dates of a leave request
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_leave_request_date` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique LeaveRequestDate ID',
     `date` date NOT NULL   COMMENT 'This date date',
     `leave_request_id` int unsigned NOT NULL   COMMENT 'FK to LeaveRequest',
    PRIMARY KEY ( `id` ),
    UNIQUE INDEX `unique_leave_request_date`(date, leave_request_id),
    CONSTRAINT FK_civicrm_hrlaa_leave_request_date_leave_request_id FOREIGN KEY (`leave_request_id`) REFERENCES `civicrm_hrleaveandabsences_leave_request`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_contact_work_pattern
-- *
-- * Represents the work patterns linked to an employee
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_contact_work_pattern` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique ContactWorkPattern ID',
     `contact_id` int unsigned NOT NULL   COMMENT 'FK to the Contact representing the employee',
     `pattern_id` int unsigned NOT NULL   COMMENT 'FK to the Work Pattern linked to an employee',
     `effective_date` date NOT NULL   COMMENT 'The date this work pattern will start to be considered active',
     `effective_end_date` date  COMMENT 'The date this work pattern will stop being considered active',
     `change_reason` varchar(512)  COMMENT 'One of the values of the Job Contract Revision Change Reason option group',
    PRIMARY KEY ( `id` ),
    UNIQUE INDEX `unique_pattern_per_effective_date`(contact_id, effective_date),
    CONSTRAINT FK_civicrm_hrlaa_contact_work_pattern_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,
    CONSTRAINT FK_civicrm_hrlaa_contact_work_pattern_pattern_id FOREIGN KEY (`pattern_id`) REFERENCES `civicrm_hrleaveandabsences_work_pattern`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- ********************************************************
-- *  Creates sample data
-- ********************************************************
SET foreign_key_checks = 0;
INSERT INTO `civicrm_hrjobcontract` VALUES (1,202,1,0);
INSERT INTO `civicrm_hrjobcontract_revision` VALUES (1,1,1,'2017-02-23 15:04:46','2017-02-23 15:04:47','2016-01-01','2016-01-01',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,1,1,0),(2,1,1,'2017-02-23 15:04:47','2017-02-23 15:04:48','2016-01-01','2016-01-01',NULL,NULL,1,NULL,NULL,2,NULL,NULL,1,1,0),(3,1,1,'2017-02-23 15:04:49','2017-02-23 15:04:49','2016-01-01','2016-01-01',NULL,NULL,1,NULL,3,2,NULL,NULL,1,1,0),(4,1,1,'2017-02-23 15:04:49','2017-02-23 15:04:50','2016-01-01','2016-01-01',NULL,NULL,1,NULL,3,2,4,NULL,1,1,0),(5,1,1,'2017-02-23 15:04:50','2017-02-23 15:04:50','2016-01-01','2016-01-01',NULL,NULL,1,5,3,2,4,NULL,1,1,0),(6,1,1,'2017-02-23 15:04:50','2017-02-23 15:04:50','2016-01-01','2016-01-01',NULL,NULL,1,5,3,2,4,6,1,1,0),(7,1,1,'2017-02-23 15:04:50','2017-02-23 15:04:50','2016-01-01',NULL,NULL,NULL,1,5,3,2,4,7,1,0,0);
INSERT INTO `civicrm_hrjobcontract_leave` VALUES (1,1,20,2,1),(2,2,0,2,0),(3,3,0,2,0);
INSERT INTO `civicrm_hrjobcontract_details` VALUES (1,NULL,NULL,NULL,NULL,'2016-01-01',NULL,NULL,0,NULL,0,NULL,NULL,1);
INSERT INTO `civicrm_hrjobcontract_health` VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,5);
INSERT INTO `civicrm_hrjobcontract_hour` VALUES (1,1,'8',0,NULL,NULL,1,1,3);
INSERT INTO `civicrm_hrjobcontract_pay` VALUES (1,NULL,0,0.00,NULL,NULL,0.00,1,NULL,NULL,NULL,NULL,NULL,4);
INSERT INTO `civicrm_hrjobcontract_pension` VALUES (1,0,0,0,NULL,0.00,NULL,6),(2,0,0,0,NULL,0.00,NULL,7);
INSERT INTO `civicrm_hrleaveandabsences_absence_period` VALUES (1,'2016','2016-01-01','2016-12-31',1),(2,'2017','2017-01-01','2017-12-31',2);
INSERT INTO `civicrm_hrleaveandabsences_public_holiday` VALUES (1,'New Year\'s Day','2016-01-01',1),(2,'Good Friday','2016-03-25',1),(3,'Easter Monday','2016-03-28',1),(4,'Early May bank holiday','2016-05-02',1),(5,'Spring bank holiday','2016-05-30',1),(6,'Summer bank holiday','2016-08-29',1),(7,'Boxing Day','2016-12-26',1),(8,'Christmas Day (substitue day)','2016-12-27',1),(9,'New Year\'s Day (substitute day)','2017-01-02',1),(10,'Good Friday','2017-04-14',1),(11,'Easter Monday','2017-04-17',1),(12,'Early May bank holiday','2017-05-01',1),(13,'Spring bank holiday','2017-05-29',1),(14,'Summer bank holiday','2017-08-28',1),(15,'Christmas day','2017-12-25',1),(16,'Boxing day','2017-12-26',1);
INSERT INTO `civicrm_hrleaveandabsences_leave_period_entitlement` VALUES (1,1,1,202,0,NULL,NULL,NULL),(2,1,2,202,0,NULL,NULL,NULL),(3,1,3,202,0,NULL,NULL,NULL),(4,2,1,202,0,NULL,NULL,NULL),(5,2,2,202,0,NULL,NULL,NULL),(6,2,3,202,0,NULL,NULL,NULL);
INSERT INTO `civicrm_hrleaveandabsences_leave_request` VALUES (1,1,202,2,'2016-01-01',1,'2016-01-01',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(2,1,202,2,'2016-03-25',1,'2016-03-25',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(3,1,202,2,'2016-03-28',1,'2016-03-28',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(4,1,202,2,'2016-05-02',1,'2016-05-02',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(5,1,202,2,'2016-05-30',1,'2016-05-30',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(6,1,202,2,'2016-08-29',1,'2016-08-29',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(7,1,202,2,'2016-12-26',1,'2016-12-26',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(8,1,202,2,'2016-12-27',1,'2016-12-27',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(9,1,202,2,'2017-01-02',1,'2017-01-02',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(10,1,202,2,'2017-04-14',1,'2017-04-14',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(11,1,202,2,'2017-04-17',1,'2017-04-17',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(12,1,202,2,'2017-05-01',1,'2017-05-01',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(13,1,202,2,'2017-05-29',1,'2017-05-29',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(14,1,202,2,'2017-08-28',1,'2017-08-28',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(15,1,202,2,'2017-12-25',1,'2017-12-25',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(16,1,202,2,'2017-12-26',1,'2017-12-26',1,NULL,NULL,NULL,NULL,NULL,'public_holiday',0),(17,1,202,6,'2016-01-30',1,'2016-02-01',1,NULL,NULL,NULL,NULL,NULL,'leave',0),(18,1,202,1,'2016-02-01',1,'2016-02-03',1,NULL,NULL,NULL,NULL,NULL,'leave',0),(19,1,202,1,'2016-08-17',1,'2016-08-25',1,NULL,NULL,NULL,NULL,NULL,'leave',0),(20,1,202,3,'2016-11-23',1,'2016-11-28',1,NULL,NULL,NULL,NULL,NULL,'leave',0),(21,3,202,5,'2016-06-03',1,'2016-06-13',1,'1',NULL,NULL,NULL,NULL,'sickness',0),(22,2,202,1,'2016-06-01',1,'2016-06-01',1,NULL,NULL,180,1,'2016-11-01','toil',0),(23,2,202,1,'2016-06-10',1,'2016-06-10',1,NULL,NULL,NULL,NULL,NULL,'leave',0),(24,2,202,1,'2016-10-20',1,'2016-10-20',1,NULL,NULL,200,1,'2016-11-01','toil',0),(25,2,202,4,'2016-12-15',1,'2016-12-15',1,NULL,NULL,360,2,'2016-12-31','toil',0),(26,1,202,5,'2017-01-01',1,'2017-01-10',1,NULL,NULL,NULL,NULL,NULL,'leave',0),(27,3,202,6,'2017-02-01',1,'2017-02-01',1,'2',NULL,NULL,NULL,NULL,'sickness',0),(28,1,202,1,'2017-02-01',1,'2017-02-05',1,NULL,NULL,NULL,NULL,NULL,'leave',0),(29,1,202,1,'2017-06-23',1,'2017-06-26',1,NULL,NULL,NULL,NULL,NULL,'leave',0),(30,1,202,3,'2017-10-01',1,'2017-10-07',1,NULL,NULL,NULL,NULL,NULL,'leave',0),(31,3,202,1,'2017-03-22',1,'2017-03-24',1,'1',NULL,NULL,NULL,NULL,'sickness',0),(32,2,202,1,'2017-04-25',1,'2017-04-25',1,NULL,NULL,180,1,'2017-05-25','toil',0),(33,2,202,3,'2017-05-15',1,'2017-05-15',1,NULL,NULL,NULL,NULL,NULL,'leave',0);
INSERT INTO `civicrm_hrleaveandabsences_leave_request_date` VALUES (1,'2016-01-01',1),(17,'2016-01-30',17),(18,'2016-01-31',17),(19,'2016-02-01',17),(20,'2016-02-01',18),(21,'2016-02-02',18),(22,'2016-02-03',18),(2,'2016-03-25',2),(3,'2016-03-28',3),(4,'2016-05-02',4),(5,'2016-05-30',5),(49,'2016-06-01',22),(38,'2016-06-03',21),(39,'2016-06-04',21),(40,'2016-06-05',21),(41,'2016-06-06',21),(42,'2016-06-07',21),(43,'2016-06-08',21),(44,'2016-06-09',21),(45,'2016-06-10',21),(50,'2016-06-10',23),(46,'2016-06-11',21),(47,'2016-06-12',21),(48,'2016-06-13',21),(23,'2016-08-17',19),(24,'2016-08-18',19),(25,'2016-08-19',19),(26,'2016-08-20',19),(27,'2016-08-21',19),(28,'2016-08-22',19),(29,'2016-08-23',19),(30,'2016-08-24',19),(31,'2016-08-25',19),(6,'2016-08-29',6),(51,'2016-10-20',24),(32,'2016-11-23',20),(33,'2016-11-24',20),(34,'2016-11-25',20),(35,'2016-11-26',20),(36,'2016-11-27',20),(37,'2016-11-28',20),(52,'2016-12-15',25),(7,'2016-12-26',7),(8,'2016-12-27',8),(53,'2017-01-01',26),(9,'2017-01-02',9),(54,'2017-01-02',26),(55,'2017-01-03',26),(56,'2017-01-04',26),(57,'2017-01-05',26),(58,'2017-01-06',26),(59,'2017-01-07',26),(60,'2017-01-08',26),(61,'2017-01-09',26),(62,'2017-01-10',26),(63,'2017-02-01',27),(64,'2017-02-01',28),(65,'2017-02-02',28),(66,'2017-02-03',28),(67,'2017-02-04',28),(68,'2017-02-05',28),(80,'2017-03-22',31),(81,'2017-03-23',31),(82,'2017-03-24',31),(10,'2017-04-14',10),(11,'2017-04-17',11),(83,'2017-04-25',32),(12,'2017-05-01',12),(84,'2017-05-15',33),(13,'2017-05-29',13),(69,'2017-06-23',29),(70,'2017-06-24',29),(71,'2017-06-25',29),(72,'2017-06-26',29),(14,'2017-08-28',14),(73,'2017-10-01',30),(74,'2017-10-02',30),(75,'2017-10-03',30),(76,'2017-10-04',30),(77,'2017-10-05',30),(78,'2017-10-06',30),(79,'2017-10-07',30),(15,'2017-12-25',15),(16,'2017-12-26',16);
INSERT INTO `civicrm_hrleaveandabsences_leave_balance_change` VALUES (1,3,-1.00,NULL,NULL,1,'leave_request_day'),(2,3,-1.00,NULL,NULL,2,'leave_request_day'),(3,3,-1.00,NULL,NULL,3,'leave_request_day'),(4,3,-1.00,NULL,NULL,4,'leave_request_day'),(5,3,-1.00,NULL,NULL,5,'leave_request_day'),(6,3,-1.00,NULL,NULL,6,'leave_request_day'),(7,3,-1.00,NULL,NULL,7,'leave_request_day'),(8,3,-1.00,NULL,NULL,8,'leave_request_day'),(9,3,-1.00,NULL,NULL,9,'leave_request_day'),(10,3,-1.00,NULL,NULL,10,'leave_request_day'),(11,3,-1.00,NULL,NULL,11,'leave_request_day'),(12,3,-1.00,NULL,NULL,12,'leave_request_day'),(13,3,-1.00,NULL,NULL,13,'leave_request_day'),(14,3,-1.00,NULL,NULL,14,'leave_request_day'),(15,3,-1.00,NULL,NULL,15,'leave_request_day'),(16,3,-1.00,NULL,NULL,16,'leave_request_day'),(17,1,20.00,NULL,NULL,1,'entitlement'),(18,3,8.00,NULL,NULL,1,'entitlement'),(19,2,5.00,'2016-04-01',NULL,1,'entitlement'),(20,2,-2.00,'2016-04-01',19,1,'entitlement'),(21,6,5.00,NULL,NULL,3,'entitlement'),(22,1,20.00,NULL,NULL,4,'entitlement'),(23,3,8.00,NULL,NULL,4,'entitlement'),(24,2,5.00,'2016-04-01',NULL,4,'entitlement'),(25,6,5.00,NULL,NULL,6,'entitlement'),(26,5,0.00,NULL,NULL,17,'leave_request_day'),(27,5,0.00,NULL,NULL,18,'leave_request_day'),(28,5,-1.00,NULL,NULL,19,'leave_request_day'),(29,5,-1.00,NULL,NULL,20,'leave_request_day'),(30,5,-1.00,NULL,NULL,21,'leave_request_day'),(31,5,-1.00,NULL,NULL,22,'leave_request_day'),(32,5,-1.00,NULL,NULL,23,'leave_request_day'),(33,5,-1.00,NULL,NULL,24,'leave_request_day'),(34,5,-1.00,NULL,NULL,25,'leave_request_day'),(35,5,0.00,NULL,NULL,26,'leave_request_day'),(36,5,0.00,NULL,NULL,27,'leave_request_day'),(37,5,-1.00,NULL,NULL,28,'leave_request_day'),(38,5,-1.00,NULL,NULL,29,'leave_request_day'),(39,5,-1.00,NULL,NULL,30,'leave_request_day'),(40,5,-1.00,NULL,NULL,31,'leave_request_day'),(41,5,-1.00,NULL,NULL,32,'leave_request_day'),(42,5,-1.00,NULL,NULL,33,'leave_request_day'),(43,5,-1.00,NULL,NULL,34,'leave_request_day'),(44,5,0.00,NULL,NULL,35,'leave_request_day'),(45,5,0.00,NULL,NULL,36,'leave_request_day'),(46,5,-1.00,NULL,NULL,37,'leave_request_day'),(47,5,-1.00,NULL,NULL,38,'leave_request_day'),(48,5,0.00,NULL,NULL,39,'leave_request_day'),(49,5,0.00,NULL,NULL,40,'leave_request_day'),(50,5,-1.00,NULL,NULL,41,'leave_request_day'),(51,5,-1.00,NULL,NULL,42,'leave_request_day'),(52,5,-1.00,NULL,NULL,43,'leave_request_day'),(53,5,-1.00,NULL,NULL,44,'leave_request_day'),(54,5,-1.00,NULL,NULL,45,'leave_request_day'),(55,5,0.00,NULL,NULL,46,'leave_request_day'),(56,5,0.00,NULL,NULL,47,'leave_request_day'),(57,5,-1.00,NULL,NULL,48,'leave_request_day'),(58,4,1.00,'2016-11-01',NULL,49,'leave_request_day'),(59,5,-1.00,NULL,NULL,50,'leave_request_day'),(60,4,1.00,'2016-11-01',NULL,51,'leave_request_day'),(61,4,-1.00,'2016-11-01',60,51,'leave_request_day'),(62,4,2.00,'2016-12-31',NULL,52,'leave_request_day'),(63,5,0.00,NULL,NULL,53,'leave_request_day'),(64,5,0.00,NULL,NULL,54,'leave_request_day'),(65,5,-1.00,NULL,NULL,55,'leave_request_day'),(66,5,-1.00,NULL,NULL,56,'leave_request_day'),(67,5,-1.00,NULL,NULL,57,'leave_request_day'),(68,5,-1.00,NULL,NULL,58,'leave_request_day'),(69,5,0.00,NULL,NULL,59,'leave_request_day'),(70,5,0.00,NULL,NULL,60,'leave_request_day'),(71,5,-1.00,NULL,NULL,61,'leave_request_day'),(72,5,-1.00,NULL,NULL,62,'leave_request_day'),(73,5,-1.00,NULL,NULL,63,'leave_request_day'),(74,5,-1.00,NULL,NULL,64,'leave_request_day'),(75,5,-1.00,NULL,NULL,65,'leave_request_day'),(76,5,-1.00,NULL,NULL,66,'leave_request_day'),(77,5,0.00,NULL,NULL,67,'leave_request_day'),(78,5,0.00,NULL,NULL,68,'leave_request_day'),(79,5,-1.00,NULL,NULL,69,'leave_request_day'),(80,5,0.00,NULL,NULL,70,'leave_request_day'),(81,5,0.00,NULL,NULL,71,'leave_request_day'),(82,5,-1.00,NULL,NULL,72,'leave_request_day'),(83,5,0.00,NULL,NULL,73,'leave_request_day'),(84,5,-1.00,NULL,NULL,74,'leave_request_day'),(85,5,-1.00,NULL,NULL,75,'leave_request_day'),(86,5,-1.00,NULL,NULL,76,'leave_request_day'),(87,5,-1.00,NULL,NULL,77,'leave_request_day'),(88,5,-1.00,NULL,NULL,78,'leave_request_day'),(89,5,0.00,NULL,NULL,79,'leave_request_day'),(90,5,-1.00,NULL,NULL,80,'leave_request_day'),(91,5,-1.00,NULL,NULL,81,'leave_request_day'),(92,5,-1.00,NULL,NULL,82,'leave_request_day'),(93,4,1.00,'2017-05-25',NULL,83,'leave_request_day'),(94,5,-1.00,NULL,NULL,84,'leave_request_day');
INSERT INTO `civicrm_relationship`(contact_id_a, contact_id_b, relationship_type_id) VALUES(202, 203, (SELECT rt.id FROM `civicrm_relationship_type` rt WHERE rt.name_a_b = 'has Leave Approved By'));
SET foreign_key_checks = 1;
