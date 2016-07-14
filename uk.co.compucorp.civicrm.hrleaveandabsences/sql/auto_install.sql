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
     `accrual_expiration_unit` int unsigned    COMMENT 'The unit (year, month, etc) of accrual_expiration_duration of this type default expiry',
     `allow_carry_forward` tinyint   DEFAULT 0 ,
     `max_number_of_days_to_carry_forward` int unsigned    ,
     `carry_forward_expiration_duration` int unsigned    COMMENT 'An amount of carry_forward_expiration_unit',
     `carry_forward_expiration_unit` int unsigned    COMMENT 'The unit (year, month, etc) of carry_forward_expiration_duration of this type default expiry',
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
  1,
  3, -- Years
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
  weight
) VALUES (
  3,
  'Sick',
  '#B32E2E',
  0,
  0,
  1, -- no
  1,
  1,
  3
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
     `type` int unsigned NOT NULL   COMMENT 'The type of this day: yes (working day), no (non working day), weekend',
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
  (1, 1, 2, '09:00', '17:30', 1, 1, 7.5),
  (1, 2, 2, '09:00', '17:30', 1, 1, 7.5),
  (1, 3, 2, '09:00', '17:30', 1, 1, 7.5),
  (1, 4, 2, '09:00', '17:30', 1, 1, 7.5),
  (1, 5, 2, '09:00', '17:30', 1, 1, 7.5),
  (1, 6, 3, NULL, NULL, NULL, NULL, NULL),
  (1, 7, 3, NULL, NULL, NULL, NULL, NULL);

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
-- * civicrm_hrleaveandabsences_entitlement
-- *
-- * A proposed entitlement for an specific set of contract, absence type and absence period
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_entitlement` (

     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Entitlement ID',
     `period_id` int unsigned NOT NULL   COMMENT 'FK to AbsencePeriod',
     `type_id` int unsigned NOT NULL   COMMENT 'FK to AbsenceType',
     `contract_id` int unsigned NOT NULL   COMMENT 'FK to HRJobContract',
     `proposed_entitlement` decimal(20,2) NOT NULL   COMMENT 'The number of days proposed for this entitlement',
     `pro_rata` decimal(20,2)   DEFAULT 0 COMMENT 'The pro rata calculated for this entitlement period',
     `overridden` tinyint   DEFAULT false COMMENT 'Indicates if the proposed_entitlement was overridden',
     `comment` text    COMMENT 'The comment added by the user about the calculation for this entitlement',
     `comment_author_id` int unsigned  COMMENT 'FK to Contact. The contact that represents the used the added the comment to this entitlement',
     `comment_updated_at` datetime    COMMENT 'The date and time the comment for this entitlement was added/updated',
    PRIMARY KEY ( `id` ),
    UNIQUE INDEX `unique_entitlement`(period_id, contract_id, type_id),
    CONSTRAINT FK_civicrm_hrleaveandabsences_entitlement_period_id FOREIGN KEY (`period_id`) REFERENCES `civicrm_hrleaveandabsences_absence_period`(`id`) ON DELETE CASCADE,
    CONSTRAINT FK_civicrm_hrleaveandabsences_entitlement_type_id FOREIGN KEY (`type_id`) REFERENCES `civicrm_hrleaveandabsences_absence_type`(`id`) ON DELETE CASCADE,
    CONSTRAINT FK_civicrm_hrleaveandabsences_entitlement_comment_author_id FOREIGN KEY (`comment_author_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_hrleaveandabsences_brought_forward
-- *
-- * Store Brought Forward information
-- *
-- *******************************************************/
CREATE TABLE `civicrm_hrleaveandabsences_brought_forward` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique BroughtForward ID',
     `entitlement_id` int unsigned NOT NULL   COMMENT 'FK to Entitlement',
     `expiration_date` date    COMMENT 'The date the brought forward will expired (or has expired, if balance is negative)',
     `balance` decimal(20,2) NOT NULL   COMMENT 'The amount of days this brought forward represents on the balance. Can be negative to represent expired days'
,
    PRIMARY KEY ( `id` )


,          CONSTRAINT FK_civicrm_hrleaveandabsences_brought_forward_entitlement_id FOREIGN KEY (`entitlement_id`) REFERENCES `civicrm_hrleaveandabsences_entitlement`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
