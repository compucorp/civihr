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
     `is_enabled` tinyint   DEFAULT 1 COMMENT 'Only enabled types can be requested',
     `allow_accruals_request` tinyint   DEFAULT 0 ,
     `max_leave_accrual` int unsigned    COMMENT 'Value is the number of days that can be accrued. Null means unlimited',
     `allow_accrue_in_the_past` tinyint   DEFAULT 0 ,
     `accrual_expiration_duration` int unsigned    COMMENT 'An amount of accrual_expiration_unit',
     `accrual_expiration_unit` int unsigned    COMMENT 'The unit (year, month, etc) of accrual_expiration_duration of this type default expiry',
     `allow_carry_forward` tinyint   DEFAULT 0 ,
     `max_number_of_days_to_carry_forward` int unsigned    ,
     `carry_forward_expiration_duration` int unsigned    COMMENT 'An amount of carry_forward_expiration_unit',
     `carry_forward_expiration_unit` int unsigned    COMMENT 'The unit (year, month, etc) of carry_forward_expiration_duration of this type default expiry',
     `carry_forward_expiration_date` varchar(5)    COMMENT 'If expiration_unit + expiration_duration is not informed, this should be. Its a date in the format dd-mm',
    PRIMARY KEY ( `id` ),
    UNIQUE INDEX `hrleaveandabsences_absence_type_title`(title)



)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;