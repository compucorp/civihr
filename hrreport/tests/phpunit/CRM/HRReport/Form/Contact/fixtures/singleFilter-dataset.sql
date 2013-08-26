-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 06, 2013 at 04:24 PM
-- Server version: 5.5.29
-- PHP Version: 5.4.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS=0;

--
-- Database: `drupalgit_civi`
--

--
-- Dumping data for table `civicrm_address`
--

INSERT INTO `civicrm_address` (`id`, `contact_id`, `location_type_id`, `is_primary`, `is_billing`, `street_address`, `street_number`, `street_number_suffix`, `street_number_predirectional`, `street_name`, `street_type`, `street_number_postdirectional`, `street_unit`, `supplemental_address_1`, `supplemental_address_2`, `supplemental_address_3`, `city`, `county_id`, `state_province_id`, `postal_code_suffix`, `postal_code`, `usps_adc`, `country_id`, `geo_code_1`, `geo_code_2`, `manual_geo_code`, `timezone`, `name`, `master_id`) VALUES
(187, 211, 2, 1, 0, '138S Bay Path N', 138, 'S', NULL, 'Bay', 'Path', 'N', NULL, 'Subscriptions Dept', NULL, NULL, 'New York', NULL, 1001, NULL, '10331', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(188, 219, 2, 1, 0, '633P Cadell Pl S', 633, 'P', NULL, 'Cadell', 'Pl', 'S', NULL, 'Mailstop 101', NULL, NULL, 'Des Moines', NULL, 1061, NULL, '10253', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(189, 213, 2, 1, 0, '156I Martin Luther King St SW', 156, 'I', NULL, 'Martin Luther King', 'St', 'SW', NULL, 'Community Relations', NULL, NULL, 'Portland', NULL, 1031, NULL, '10312', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL);

--
-- Dumping data for table `civicrm_contact`
--

INSERT INTO `civicrm_contact` (`id`, `contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `hash`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`, `created_date`, `modified_date`) VALUES
(203, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Hetfield, Amy', 'Ms. Amy Hetfield', NULL, NULL, NULL, NULL, NULL, 'Both', '1537046527', NULL, NULL, 'Amy', 'W', 'Hetfield', 2, NULL, 1, NULL, 'Dear Amy', 1, NULL, 'Dear Amy', 1, NULL, 'Ms. Amy Hetfield', NULL, 1, '1982-09-09', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:50', '2013-08-06 16:12:55'),
(204, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Hetfield, Emma', 'Ms. Emma Hetfield', NULL, NULL, NULL, '1', NULL, 'Both', '1537046527', NULL, NULL, 'Emma', '', 'Hetfield', 2, NULL, 1, NULL, 'Dear Emma', 1, NULL, 'Dear Emma', 1, NULL, 'Ms. Emma Hetfield', NULL, 1, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:50', '2013-08-06 16:13:02'),
(205, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Anderson, John', 'John Anderson', NULL, NULL, NULL, NULL, NULL, 'Both', '465340484', NULL, NULL, 'John', '', 'Anderson', NULL, NULL, 1, NULL, 'Dear John', 1, NULL, 'Dear John', 1, NULL, 'John Anderson', NULL, 2, '1990-05-02', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:13:01'),
(206, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Hammett, Peter', 'Peter Hammett Sr.', NULL, NULL, NULL, NULL, NULL, 'Both', '557528799', NULL, NULL, 'Peter', '', 'Hammett', NULL, 2, 1, NULL, 'Dear Peter', 1, NULL, 'Dear Peter', 1, NULL, 'Peter Hammett Sr.', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:12:56'),
(207, 'Organization', NULL, 0, 0, 0, 0, 1, 0, NULL, NULL, 'Bay Technology Network', 'Bay Technology Network', NULL, NULL, NULL, '5', NULL, 'Both', '6586143', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, 'Bay Technology Network', NULL, NULL, NULL, 0, NULL, NULL, 217, 'Bay Technology Network', NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:13:06'),
(208, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Anderson, Cristina', 'Ms. Cristina Anderson', NULL, NULL, NULL, NULL, NULL, 'Both', '821806118', NULL, NULL, 'Cristina', 'F', 'Anderson', 2, NULL, 1, NULL, 'Dear Cristina', 1, NULL, 'Dear Cristina', 1, NULL, 'Ms. Cristina Anderson', NULL, NULL, '1991-08-25', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:13:01'),
(209, 'Individual', NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, 'Johnson, Amy', 'Ms. Amy Johnson', NULL, NULL, NULL, NULL, NULL, 'Both', '1966241625', NULL, NULL, 'Amy', 'A', 'Johnson', 2, NULL, 1, NULL, 'Dear Amy', 1, NULL, 'Dear Amy', 1, NULL, 'Ms. Amy Johnson', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:13:03'),
(210, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Hetfield, Peter', 'Peter Hetfield Jr.', NULL, NULL, NULL, NULL, NULL, 'Both', '1535107201', NULL, NULL, 'Peter', 'Q', 'Hetfield', NULL, 1, 1, NULL, 'Dear Peter', 1, NULL, 'Dear Peter', 1, NULL, 'Peter Hetfield Jr.', NULL, 2, '1982-04-19', 0, NULL, NULL, NULL, 'Sierra Sports Trust', NULL, NULL, 214, 0, '2013-08-06 16:12:51', '2013-08-06 16:13:07'),
(211, 'Individual', NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, 'Last211, Cristina', 'Cristina Last211', NULL, NULL, NULL, NULL, NULL, 'Both', '-494892012', NULL, NULL, 'Cristina', '', 'Last211', NULL, NULL, 1, NULL, 'Dear Cristina', 1, NULL, 'Dear Cristina', 1, NULL, 'Cristina Last211', NULL, NULL, '1929-07-06', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:12:51'),
(213, 'Individual', NULL, 0, 1, 0, 0, 1, 0, NULL, NULL, 'Last213, Cristina', 'Cristina Last213', NULL, NULL, NULL, '3', NULL, 'Both', '2095701394', NULL, NULL, 'Cristina', '', 'Last213', NULL, NULL, 1, NULL, 'Dear Cristina', 1, NULL, 'Dear Cristina', 1, NULL, 'Cristina Last213', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:12:53'),
(219, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Last219, Cristina', 'Cristina Last219', NULL, NULL, NULL, NULL, NULL, 'Both', '821806118', NULL, NULL, 'Cristina', 'P', 'Last219', NULL, NULL, 1, NULL, 'Dear Cristina', 1, NULL, 'Dear Cristina', 1, NULL, 'Cristina Last219', NULL, 1, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:12:52');


--
-- Dumping data for table `civicrm_email`
--

INSERT INTO `civicrm_email` (`id`, `contact_id`, `location_type_id`, `email`, `is_primary`, `is_billing`, `on_hold`, `is_bulkmail`, `hold_date`, `reset_date`, `signature_text`, `signature_html`) VALUES
(185, 211, 1, 'email-211@sample.co.pl', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(186, 219, 1, 'email-219@airmail.biz', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(187, 219, 1, 'email-219@mymail.biz', 0, 0, 0, 0, NULL, NULL, NULL, NULL),
(188, 213, 1, 'email-213@mymail.com', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(189, 213, 1, 'email-213@lol.co.in', 0, 0, 0, 0, NULL, NULL, NULL, NULL);

--
-- Dumping data for table `civicrm_hrjob`
--

INSERT INTO `civicrm_hrjob` (`id`, `contact_id`, `position`, `title`, `is_tied_to_funding`, `funding_notes`, `contract_type`, `level_type`, `period_type`, `period_start_date`, `period_end_date`, `manager_contact_id`, `location`, `is_primary`) VALUES
(1, 211, 'Position-211-1', 'Title-211-1', 1, NULL, 'Contractor', 'Senior Staff', 'Permanent', '2011-03-01', '2013-01-26', 211, 'Headquarters', 1),
(2, 211, 'Position-211-2', 'Title-211-2', 0, NULL, 'Volunteer', 'Junior Staff', 'Permanent', '2011-10-07', '2013-03-29', 207, 'Headquarters', 0),
(3, 211, 'Position-211-3', 'Title-211-3', 1, NULL, 'Apprentice', 'Senior Manager', 'Temporary', '2010-12-18', '2013-03-29', 214, 'Headquarters', 0),
(4, 219, 'Position-219-4', 'Title-219-4', 1, NULL, 'Apprentice', 'Junior Staff', 'Permanent', '2011-11-23', '2013-04-17', 206, NULL, 1),
(6, 213, 'Position-213-6', 'Title-213-6', 0, NULL, 'Employee', 'Junior Manager', 'Permanent', '2010-10-10', '2013-01-25', 213, 'Home', 1);

--
-- Dumping data for table `civicrm_hrjob_health`
--

INSERT INTO `civicrm_hrjob_health` (`id`, `job_id`, `provider`, `plan_type`, `description`, `dependents`) VALUES
(1, 1, 'Provider-1', 'Individual', 'Description-1', 'dependents-1'),
(3, 6, 'Provider-6', 'Family', 'Description-6', 'dependents-6');

--
-- Dumping data for table `civicrm_hrjob_hour`
--

INSERT INTO `civicrm_hrjob_hour` (`id`, `job_id`, `hours_type`, `hours_amount`, `hours_unit`, `hours_fte`) VALUES
(1, 1, 'full', 32.00, 'Week', 1.50),
(2, 6, 'part', 16.00, 'Day', 2.00);

--
-- Dumping data for table `civicrm_hrjob_leave`
--

INSERT INTO `civicrm_hrjob_leave` (`id`, `job_id`, `leave_type`, `leave_amount`) VALUES
(1, 1, 'Annual', 2),
(2, 1, 'Public', 5),
(3, 1, 'Sick', 3),
(4, 2, 'Annual', 1),
(5, 2, 'Public', 4),
(6, 2, 'Sick', 1),
(7, 3, 'Annual', 2),
(8, 3, 'Public', 5),
(9, 3, 'Sick', 2),
(10, 4, 'Annual', 5),
(11, 4, 'Public', 3),
(12, 4, 'Sick', 3),
(16, 6, 'Annual', 4),
(17, 6, 'Public', 1),
(18, 6, 'Sick', 2);

--
-- Dumping data for table `civicrm_hrjob_pay`
--

INSERT INTO `civicrm_hrjob_pay` (`id`, `job_id`, `pay_grade`, `pay_amount`, `pay_unit`) VALUES
(1, 2, 'Paid', 80.00, 'Day'),
(2, 3, 'Paid', 200.00, 'Hour'),
(3, 4, 'Unpaid', 200.00, 'Day');

--
-- Dumping data for table `civicrm_hrjob_pension`
--

INSERT INTO `civicrm_hrjob_pension` (`id`, `job_id`, `is_enrolled`, `ee_contrib_pct`, `er_contrib_pct`) VALUES
(1, 1, 1, 200.00, 100.00),
(2, 6, 0, 250.00, 100.00);

--
-- Dumping data for table `civicrm_hrjob_role`
--

INSERT INTO `civicrm_hrjob_role` (`id`, `job_id`, `title`, `description`, `hours`, `region`, `department`, `manager_contact_id`, `functional_area`, `organization`, `cost_center`, `location`) VALUES
(1, 1, 'Manager2', 'desc3', 24.00, 'Europe', 'Operations', 217, 'Save the Rhinos', 'ZINGIT', '003', NULL),
(2, 1, 'Manager1', 'desc1', 16.00, 'Europe', 'Finance', 216, 'Save the Panda', 'UP', '004', NULL),
(3, 3, 'Manager1', 'desc1', 32.00, 'North America', 'Operations', 204, 'Save the Panda', 'ZINGIT', '004', NULL),
(4, 3, 'Manager2', 'desc4', 32.00, 'Europe', 'Fundraising', 212, 'Save the Tigers', 'UP', '004', NULL),
(5, 3, 'Manager1', 'desc1', 24.00, 'Asia', 'Finance', 217, 'Save the Whales', 'ZING', '003', NULL),
(7, 6, 'Manager2', 'desc4', 40.00, 'Africa', 'Operations', 220, 'Save the Whales', 'UP', '005', NULL);


--
-- Dumping data for table `civicrm_phone`
--

INSERT INTO `civicrm_phone` (`id`, `contact_id`, `location_type_id`, `is_primary`, `is_billing`, `mobile_provider_id`, `phone`, `phone_ext`, `phone_numeric`, `phone_type_id`) VALUES
(167, 219, 1, 1, 0, NULL, '491-2170', NULL, '4912170', 1),
(168, 213, 1, 1, 0, NULL, '(425) 514-6819', NULL, '4255146819', 1),
(169, 213, 1, 0, 0, NULL, '858-9417', NULL, '8589417', 1);
