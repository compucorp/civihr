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
-- Dumping data for table `civicrm_contact`
--

INSERT INTO `civicrm_contact` (`id`, `contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `hash`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`, `created_date`, `modified_date`) VALUES
(203, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Hetfield, Peter', 'Peter Hetfield III', NULL, NULL, NULL, '4', NULL, 'Both', '1535107201', NULL, NULL, 'Peter', 'L', 'Hetfield', NULL, 4, NULL, NULL, 1, NULL, 'Dear Peter', 1, NULL, 'Dear Peter', 1, NULL, 'Peter Hetfield III', NULL, 2, '1971-06-30', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:13', '2014-02-15 11:13:38'),
(204, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Anderson, Peter', 'Dr. Peter Anderson Sr.', NULL, NULL, NULL, '2', NULL, 'Both', '-1366939132', NULL, NULL, 'Peter', '', 'Anderson', 4, 2, NULL, NULL, 1, NULL, 'Dear Peter', 1, NULL, 'Dear Peter', 1, NULL, 'Dr. Peter Anderson Sr.', NULL, 2, '1974-04-25', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:13', '2014-02-15 11:14:02'),
(205, 'Individual', NULL, 1, 0, 0, 0, 0, 0, NULL, NULL, 'Anderson, Kirk', 'Mr. Kirk Anderson II', NULL, NULL, NULL, '3', NULL, 'Both', '1741331911', NULL, NULL, 'Kirk', '', 'Anderson', 3, 3, NULL, NULL, 1, NULL, 'Dear Kirk', 1, NULL, 'Dear Kirk', 1, NULL, 'Mr. Kirk Anderson II', NULL, 2, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:13', '2014-02-15 11:13:43'),
(206, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Anderson, John', 'John Anderson Jr.', NULL, NULL, NULL, '2', NULL, 'Both', '465340484', NULL, NULL, 'John', '', 'Anderson', NULL, 1, NULL, NULL, 1, NULL, 'Dear John', 1, NULL, 'Dear John', 1, NULL, 'John Anderson Jr.', NULL, 2, '1928-02-25', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:13', '2014-02-15 11:13:19'),
(207, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Anderson, John', 'Dr. John Anderson II', NULL, NULL, NULL, NULL, NULL, 'Both', '465340484', NULL, NULL, 'John', 'Y', 'Anderson', 4, 3, NULL, NULL, 1, NULL, 'Dear John', 1, NULL, 'Dear John', 1, NULL, 'Dr. John Anderson II', NULL, 2, '1959-07-05', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:13', '2014-02-15 11:13:47'),
(208, 'Organization', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, ' Peace School', ' Peace School', NULL, NULL, NULL, '1', NULL, 'Both', '-494971325', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, ' Peace School', NULL, NULL, NULL, 0, NULL, NULL, NULL, ' Peace School', NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:13:15'),
(209, 'Individual', NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, 'Anderson, John', 'John Anderson III', NULL, NULL, NULL, NULL, NULL, 'Both', '465340484', NULL, NULL, 'John', '', 'Anderson', NULL, 4, NULL, NULL, 1, NULL, 'Dear John', 1, NULL, 'Dear John', 1, NULL, 'John Anderson III', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:13:23'),
(210, 'Individual', NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, 'peterh@fakemail.com', 'peterh@fakemail.com', NULL, NULL, NULL, '1', NULL, 'Both', '-28681942', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, 1, NULL, 'Dear peterh@fakemail.com', 1, NULL, 'Dear peterh@fakemail.com', 1, NULL, 'peterh@fakemail.com', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:14:06'),
(211, 'Individual', NULL, 0, 0, 0, 0, 1, 0, NULL, NULL, 'Hammett, Peter', 'Dr. Peter Hammett', NULL, NULL, NULL, '3', NULL, 'Both', '557528799', NULL, NULL, 'Peter', '', 'Hammett', 4, NULL, NULL, NULL, 1, NULL, 'Dear Peter', 1, NULL, 'Dear Peter', 1, NULL, 'Dr. Peter Hammett', NULL, 2, '1960-02-02', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:13:49'),
(212, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Hetfield, Emma', 'Emma Hetfield', NULL, NULL, NULL, NULL, NULL, 'Both', '1537046527', NULL, NULL, 'Emma', 'E', 'Hetfield', NULL, NULL, NULL, NULL, 1, NULL, 'Dear Emma', 1, NULL, 'Dear Emma', 1, NULL, 'Emma Hetfield', NULL, 1, '1933-07-10', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:14:11'),
(213, 'Organization', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'New York Health Initiative', 'New York Health Initiative', NULL, NULL, NULL, '3', NULL, 'Both', '-2124811621', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, 'New York Health Initiative', NULL, NULL, NULL, 0, NULL, NULL, NULL, 'New York Health Initiative', NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:13:14'),
(214, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Hetfield, John', 'Mr. John Hetfield', NULL, NULL, NULL, NULL, NULL, 'Both', '-307078637', NULL, NULL, 'John', 'P', 'Hetfield', 3, NULL, NULL, NULL, 1, NULL, 'Dear John', 1, NULL, 'Dear John', 1, NULL, 'Mr. John Hetfield', NULL, 2, '1957-09-24', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:13:58'),
(215, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'hammett.james35@sample.co.uk', 'hammett.james35@sample.co.uk', NULL, NULL, NULL, NULL, NULL, 'Both', '1431082725', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'Dear hammett.james35@sample.co.uk', 1, NULL, 'Dear hammett.james35@sample.co.uk', 1, NULL, 'hammett.james35@sample.co.uk', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:14:01'),
(216, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Anderson, Cristina', 'Cristina Anderson', NULL, NULL, NULL, NULL, NULL, 'Both', '821806118', NULL, NULL, 'Cristina', 'D', 'Anderson', NULL, NULL, NULL, NULL, 1, NULL, 'Dear Cristina', 1, NULL, 'Dear Cristina', 1, NULL, 'Cristina Anderson', NULL, 1, '1929-06-23', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:13:32'),
(217, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Hammett, Peter', 'Peter Hammett III', NULL, NULL, NULL, NULL, NULL, 'Both', '557528799', NULL, NULL, 'Peter', '', 'Hammett', NULL, 4, NULL, NULL, 1, NULL, 'Dear Peter', 1, NULL, 'Dear Peter', 1, NULL, 'Peter Hammett III', NULL, 2, '1963-08-26', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:13:26'),
(218, 'Individual', NULL, 1, 0, 0, 0, 0, 0, NULL, NULL, 'Hetfield, Kirk', 'Kirk Hetfield', NULL, NULL, NULL, NULL, NULL, 'Both', '-1849405552', NULL, NULL, 'Kirk', '', 'Hetfield', NULL, NULL, NULL, NULL, 1, NULL, 'Dear Kirk', 1, NULL, 'Dear Kirk', 1, NULL, 'Kirk Hetfield', NULL, 2, '1953-02-21', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:13:54'),
(219, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Johnson, Amy', 'Amy Johnson', NULL, NULL, NULL, '2', NULL, 'Both', '1966241625', NULL, NULL, 'Amy', '', 'Johnson', NULL, NULL, NULL, NULL, 1, NULL, 'Dear Amy', 1, NULL, 'Dear Amy', 1, NULL, 'Amy Johnson', NULL, 1, '1987-07-13', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:13:28'),
(220, 'Individual', NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, 'janderson@lol.net', 'janderson@lol.net', NULL, NULL, NULL, NULL, NULL, 'Both', '-1598117864', NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, 1, NULL, 'Dear janderson@lol.net', 1, NULL, 'Dear janderson@lol.net', 1, NULL, 'janderson@lol.net', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:14:09'),
(221, 'Individual', NULL, 1, 0, 0, 0, 1, 0, NULL, NULL, 'Johnson, Cristina', 'Mrs. Cristina Johnson', NULL, NULL, NULL, NULL, NULL, 'Both', '2095701394', NULL, NULL, 'Cristina', '', 'Johnson', 1, NULL, NULL, NULL, 1, NULL, 'Dear Cristina', 1, NULL, 'Dear Cristina', 1, NULL, 'Mrs. Cristina Johnson', NULL, NULL, '1981-02-21', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2014-02-15 11:13:14', '2014-02-15 11:13:15');


DELETE FROM `civicrm_option_value` WHERE `option_group_id` IN  (2, 26) ;

--
-- Dumping data for table `civicrm_option_value`
--

INSERT INTO `civicrm_option_value` ( `option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `domain_id`, `visibility_id`) VALUES
(2, 'Absence', '62', 'Absence', 'Timesheet', 1, 0, 57, NULL, 0, 0, 1, NULL, NULL, NULL),
(2, 'Sick', '63', 'Sick', 'Timesheet', 1, 0, 58, NULL, 0, 0, 1, NULL, NULL, NULL),
(2, 'Vacation', '64', 'Vacation', 'Timesheet', 1, 0, 59, NULL, 0, 0, 1, NULL, NULL, NULL),
(2, 'Maternity', '65', 'Maternity', 'Timesheet', 1, 0, 60, NULL, 0, 0, 1, NULL, NULL, NULL),
(2, 'Paternity', '66', 'Paternity', 'Timesheet', 1, 0, 61, NULL, 0, 0, 1, NULL, NULL, NULL),
(2, 'TOIL', '67', 'TOIL', 'Timesheet', 1, 0, 63, NULL, 0, 0, 1, NULL, NULL, NULL),
(2, 'TOIL (Credit)', '68', 'TOIL (Credit)', 'Timesheet', 1, 0, 62, NULL, 0, 0, 1, NULL, NULL, NULL),
(2, 'Other', '69', 'Other', 'Timesheet', 1, 0, 64, NULL, 0, 0, 1, NULL, NULL, NULL),
(26, 'Scheduled', '1', 'Scheduled', NULL, 0, 1, 1, NULL, 0, 1, 1, NULL, NULL, NULL),
(26, 'Completed', '2', 'Completed', NULL, 0, NULL, 2, NULL, 0, 1, 1, NULL, NULL, NULL),
(26, 'Cancelled', '3', 'Cancelled', NULL, 0, NULL, 3, NULL, 0, 1, 1, NULL, NULL, NULL),
(26, 'Rejected', '9', 'Rejected', NULL, 0, 0, 9, NULL, 0, 1, 1, NULL, NULL, NULL);


--
-- Dumping data for table `civicrm_hrabsence_type`
--

INSERT INTO `civicrm_hrabsence_type` (`id`, `name`, `title`, `is_active`, `allow_credits`, `credit_activity_type_id`, `allow_debits`, `debit_activity_type_id`) VALUES
(1, 'Sick', 'Sick', 1, 0, NULL, 1, 63),
(2, 'Vacation', 'Vacation', 1, 0, NULL, 1, 64),
(3, 'Maternity', 'Maternity', 1, 0, NULL, 1, 65),
(4, 'Paternity', 'Paternity', 1, 0, NULL, 1, 66),
(5, 'TOIL', 'TOIL', 1, 1, 68, 1, 67),
(6, 'Other', 'Other', 1, 0, NULL, 1, 69);

--
-- Dumping data for table `civicrm_activity`
--

INSERT INTO `civicrm_activity` (`id`, `source_record_id`, `activity_type_id`, `subject`, `activity_date_time`, `duration`, `location`, `phone_id`, `phone_number`, `details`, `status_id`, `priority_id`, `parent_id`, `is_test`, `medium_id`, `is_auto`, `relationship_id`, `is_current_revision`, `original_id`, `result`, `is_deleted`, `campaign_id`, `engagement_level`, `weight`) VALUES
(657, NULL, 63, NULL, '2014-02-15 08:13:18', NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(658, 657, 62, NULL, '2015-03-02 05:04:38', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(659, 657, 62, NULL, '2015-03-03 05:04:38', 240, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(660, NULL, 64, NULL, '2014-02-15 08:13:18', NULL, NULL, NULL, NULL, NULL, 3, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(661, 660, 62, NULL, '2015-01-09 09:46:23', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(662, 660, 62, NULL, '2015-01-10 09:46:23', 240, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(663, NULL, 64, NULL, '2014-02-15 08:13:20', NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(664, 663, 62, NULL, '2013-06-30 04:49:30', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(665, 663, 62, NULL, '2013-07-01 04:49:30', 240, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(666, 663, 62, NULL, '2013-07-02 04:49:30', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(667, NULL, 66, NULL, '2014-02-15 08:13:20', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(668, 667, 62, NULL, '2014-07-10 02:08:03', 240, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(669, 667, 62, NULL, '2014-07-11 02:08:03', 0, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(670, 667, 62, NULL, '2014-07-12 02:08:03', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(671, NULL, 63, NULL, '2014-02-15 08:13:20', NULL, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(672, 671, 62, NULL, '2014-10-19 11:58:53', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(673, 671, 62, NULL, '2014-10-20 11:58:53', 240, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(674, 671, 62, NULL, '2014-10-21 11:58:53', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(675, 671, 62, NULL, '2014-10-22 11:58:53', 0, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(676, NULL, 63, NULL, '2014-02-15 08:13:20', NULL, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(677, 676, 62, NULL, '2014-12-12 09:52:47', 240, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(678, 676, 62, NULL, '2014-12-13 09:52:47', 0, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(679, NULL, 67, NULL, '2014-02-15 08:13:21', NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(680, 679, 62, NULL, '2014-11-06 02:47:42', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(681, 679, 62, NULL, '2014-11-07 02:47:42', 240, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(682, 679, 62, NULL, '2014-11-08 02:47:42', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(683, 679, 62, NULL, '2014-11-09 02:47:42', 240, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(684, NULL, 64, NULL, '2014-02-15 08:13:21', NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(685, 684, 62, NULL, '2015-04-14 10:20:15', 240, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(686, 684, 62, NULL, '2015-04-15 10:20:15', 0, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(687, 684, 62, NULL, '2015-04-16 10:20:15', 240, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(688, NULL, 64, NULL, '2014-02-15 08:13:21', NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(689, 688, 62, NULL, '2016-03-16 05:52:19', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(690, 688, 62, NULL, '2016-03-17 05:52:19', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(691, NULL, 64, NULL, '2014-02-15 08:13:21', NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(692, 691, 62, NULL, '2015-08-12 03:21:34', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(693, 691, 62, NULL, '2015-08-13 03:21:34', 240, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(694, 691, 62, NULL, '2015-08-14 03:21:34', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(695, 691, 62, NULL, '2015-08-15 03:21:34', 240, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(696, NULL, 64, NULL, '2014-02-15 08:13:22', NULL, NULL, NULL, NULL, NULL, 3, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(697, 696, 62, NULL, '2016-09-15 06:46:15', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(698, 696, 62, NULL, '2016-09-16 06:46:15', 0, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(699, 696, 62, NULL, '2016-09-17 06:46:15', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(700, NULL, 63, NULL, '2014-02-15 08:13:22', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(701, 700, 62, NULL, '2016-09-05 07:05:42', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(702, 700, 62, NULL, '2016-09-06 07:05:42', 0, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL),
(703, 700, 62, NULL, '2016-09-07 07:05:42', 480, NULL, NULL, NULL, NULL, 2, 2, NULL, 0, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL);

--
-- Dumping data for table `civicrm_activity_contact`
--

INSERT INTO `civicrm_activity_contact` (`id`, `activity_id`, `contact_id`, `record_type_id`) VALUES
(840, 657, 203, 1),
(841, 657, 204, 2),
(842, 657, 204, 3),
(843, 660, 221, 1),
(844, 660, 203, 2),
(845, 660, 203, 3),
(846, 663, 219, 1),
(847, 663, 210, 2),
(848, 663, 210, 3),
(849, 667, 220, 1),
(850, 667, 207, 2),
(851, 667, 207, 3),
(852, 671, 211, 1),
(853, 671, 206, 2),
(854, 671, 206, 3),
(855, 676, 210, 1),
(856, 676, 211, 2),
(857, 676, 211, 3),
(858, 679, 206, 1),
(859, 679, 204, 2),
(860, 679, 204, 3),
(861, 684, 215, 1),
(862, 684, 215, 2),
(863, 684, 215, 3),
(864, 688, 206, 1),
(865, 688, 210, 2),
(866, 688, 210, 3),
(867, 691, 203, 1),
(868, 691, 204, 2),
(869, 691, 204, 3),
(870, 696, 219, 1),
(871, 696, 210, 2),
(872, 696, 210, 3),
(873, 700, 218, 1),
(874, 700, 209, 2),
(875, 700, 209, 3);
