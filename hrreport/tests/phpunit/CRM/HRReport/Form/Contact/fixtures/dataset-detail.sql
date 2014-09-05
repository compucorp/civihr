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
(189, 213, 2, 1, 0, '156I Martin Luther King St SW', 156, 'I', NULL, 'Martin Luther King', 'St', 'SW', NULL, 'Community Relations', NULL, NULL, 'Portland', NULL, 1031, NULL, '10312', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(190, 212, 2, 1, 0, '857A Pine Path S', 857, 'A', NULL, 'Pine', 'Path', 'S', NULL, 'Subscriptions Dept', NULL, NULL, 'Boston', NULL, 1007, NULL, '10201', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(191, 217, 2, 1, 0, '328W Beech Ave W', 328, 'W', NULL, 'Beech', 'Ave', 'W', NULL, 'Payables Dept.', NULL, NULL, 'Minneapolis', NULL, 1003, NULL, '10331', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(192, 203, 2, 1, 0, '351S Northpoint Rd S', 351, 'S', NULL, 'Northpoint', 'Rd', 'S', NULL, 'Community Relations', NULL, NULL, 'Portland', NULL, 1009, NULL, '10704', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(193, 206, 2, 1, 0, '611X Main Blvd SE', 611, 'X', NULL, 'Main', 'Blvd', 'SE', NULL, 'Subscriptions Dept', NULL, NULL, 'Minneapolis', NULL, 1003, NULL, '10219', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(194, 221, 2, 1, 0, '836T Jackson Dr NW', 836, 'T', NULL, 'Jackson', 'Dr', 'NW', NULL, 'Editorial Dept', NULL, NULL, 'San Francisco', NULL, 1061, NULL, '10704', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(195, 222, 2, 1, 0, '678M College Rd N', 678, 'M', NULL, 'College', 'Rd', 'N', NULL, 'Churchgate', NULL, NULL, 'San Francisco', NULL, 1061, NULL, '10201', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(196, 220, 2, 1, 0, '755O Maple Path SW', 755, 'O', NULL, 'Maple', 'Path', 'SW', NULL, 'Mailstop 101', NULL, NULL, 'Seattle', NULL, 1001, NULL, '10222', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(197, 205, 2, 1, 0, '8Q Van Ness Ln N', 8, 'Q', NULL, 'Van Ness', 'Ln', 'N', NULL, 'Payables Dept.', NULL, NULL, 'Boston', NULL, 1031, NULL, '10034', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(198, 208, 2, 1, 0, '617J Cadell Ave N', 617, 'J', NULL, 'Cadell', 'Ave', 'N', NULL, 'Donor Relations', NULL, NULL, 'Cleveland', NULL, 1060, NULL, '10331', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(199, 204, 2, 1, 0, '855W Jackson Way S', 855, 'W', NULL, 'Jackson', 'Way', 'S', NULL, 'Community Relations', NULL, NULL, 'Des Moines', NULL, 1002, NULL, '10704', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(200, 218, 2, 1, 0, '211Z Maple Dr NE', 211, 'Z', NULL, 'Maple', 'Dr', 'NE', NULL, 'Receiving', NULL, NULL, 'Seattle', NULL, 1060, NULL, '10704', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(201, 209, 2, 1, 0, '949U El Camino Path NE', 949, 'U', NULL, 'El Camino', 'Path', 'NE', NULL, 'Community Relations', NULL, NULL, 'Portland', NULL, 1008, NULL, '10201', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(202, 216, 2, 1, 0, '861T College Dr E', 861, 'T', NULL, 'College', 'Dr', 'E', NULL, 'c/o PO Plus', NULL, NULL, 'Des Moines', NULL, 1059, NULL, '10331', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(203, 210, 2, 1, 0, '905W Martin Luther King Dr W', 905, 'W', NULL, 'Martin Luther King', 'Dr', 'W', NULL, 'Donor Relations', NULL, NULL, 'Seattle', NULL, 1001, NULL, '10331', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(204, 215, 2, 1, 0, '331F Dowlen Ave SW', 331, 'F', NULL, 'Dowlen', 'Ave', 'SW', NULL, 'c/o OPDC', NULL, NULL, 'Detroit', NULL, 1007, NULL, '10004', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(205, 207, 3, 1, 0, '936S Bay Rd NE', 936, 'S', NULL, 'Bay', 'Rd', 'NE', NULL, NULL, NULL, NULL, 'Detroit', NULL, 1001, NULL, '10004', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(206, 214, 3, 1, 0, '360D Cadell Ave S', 360, 'D', NULL, 'Cadell', 'Ave', 'S', NULL, NULL, NULL, NULL, 'Los Angeles', NULL, 1006, NULL, '10034', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL),
(207, 210, 214, 0, 0, '303C Jackson Blvd NE', 303, 'C', NULL, 'Jackson', 'Blvd', 'NE', NULL, NULL, NULL, NULL, 'San Francisco', NULL, 1059, NULL, '10219', NULL, 1228, NULL, NULL, 0, NULL, NULL, NULL);

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
(211, 'Individual', NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, 'Hammett, Cristina', 'Cristina Hammett', NULL, NULL, NULL, NULL, NULL, 'Both', '-494892012', NULL, NULL, 'Cristina', '', 'Hammett', NULL, NULL, 1, NULL, 'Dear Cristina', 1, NULL, 'Dear Cristina', 1, NULL, 'Cristina Hammett', NULL, NULL, '1929-07-06', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:12:51'),
(212, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Hammett, Kirk', 'Dr. Kirk Hammett III', NULL, NULL, NULL, '2', NULL, 'Both', '-1085753061', NULL, NULL, 'Kirk', '', 'Hammett', 4, 4, 1, NULL, 'Dear Kirk', 1, NULL, 'Dear Kirk', 1, NULL, 'Dr. Kirk Hammett III', NULL, NULL, '1942-10-11', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:12:54'),
(213, 'Individual', NULL, 0, 1, 0, 0, 1, 0, NULL, NULL, 'Johnson, Cristina', 'Cristina Johnson', NULL, NULL, NULL, '3', NULL, 'Both', '2095701394', NULL, NULL, 'Cristina', '', 'Johnson', NULL, NULL, 1, NULL, 'Dear Cristina', 1, NULL, 'Dear Cristina', 1, NULL, 'Cristina Johnson', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:12:53'),
(214, 'Organization', NULL, 1, 0, 0, 0, 0, 0, NULL, NULL, 'Sierra Sports Trust', 'Sierra Sports Trust', NULL, NULL, NULL, '5', NULL, 'Both', '1602888138', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, 'Sierra Sports Trust', NULL, NULL, NULL, 0, NULL, NULL, 210, 'Sierra Sports Trust', NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:13:07'),
(215, 'Individual', NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, 'Johnson, James', 'Mr. James Johnson', NULL, NULL, NULL, '2', NULL, 'Both', '326000430', NULL, NULL, 'James', 'P', 'Johnson', 3, NULL, 1, NULL, 'Dear James', 1, NULL, 'Dear James', 1, NULL, 'Mr. James Johnson', NULL, NULL, '1991-08-25', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:13:05'),
(216, 'Individual', NULL, 1, 0, 0, 0, 0, 0, NULL, NULL, 'Hammett, Kirk', 'Mr. Kirk Hammett', NULL, NULL, NULL, NULL, NULL, 'Both', '-1085753061', NULL, NULL, 'Kirk', '', 'Hammett', 3, NULL, 1, NULL, 'Dear Kirk', 1, NULL, 'Dear Kirk', 1, NULL, 'Mr. Kirk Hammett', NULL, 2, '1950-04-30', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:13:04'),
(217, 'Individual', NULL, 1, 0, 0, 0, 1, 0, NULL, NULL, 'Hetfield, Emma', 'Emma Hetfield', NULL, NULL, NULL, '3', NULL, 'Both', '1537046527', NULL, NULL, 'Emma', 'O', 'Hetfield', NULL, NULL, 1, NULL, 'Dear Emma', 1, NULL, 'Dear Emma', 1, NULL, 'Emma Hetfield', NULL, 1, '1967-08-29', 1, NULL, NULL, NULL, 'Bay Technology Network', NULL, NULL, 207, 0, '2013-08-06 16:12:51', '2013-08-06 16:13:06'),
(218, 'Individual', NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, 'Anderson, Emma', 'Emma Anderson', NULL, NULL, NULL, NULL, NULL, 'Both', '-1382832728', NULL, NULL, 'Emma', '', 'Anderson', NULL, NULL, 1, NULL, 'Dear Emma', 1, NULL, 'Dear Emma', 1, NULL, 'Emma Anderson', NULL, 1, '1970-01-23', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:13:02'),
(219, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Anderson, Cristina', 'Cristina Anderson', NULL, NULL, NULL, NULL, NULL, 'Both', '821806118', NULL, NULL, 'Cristina', 'P', 'Anderson', NULL, NULL, 1, NULL, 'Dear Cristina', 1, NULL, 'Dear Cristina', 1, NULL, 'Cristina Anderson', NULL, 1, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:12:52'),
(220, 'Individual', NULL, 1, 0, 0, 0, 0, 0, NULL, NULL, 'Hammett, Amy', 'Mrs. Amy Hammett', NULL, NULL, NULL, '5', NULL, 'Both', '1771824164', NULL, NULL, 'Amy', '', 'Hammett', 1, NULL, 1, NULL, 'Dear Amy', 1, NULL, 'Dear Amy', 1, NULL, 'Mrs. Amy Hammett', NULL, 1, '1960-08-04', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:13:00'),
(221, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Hetfield, John', 'John Hetfield', NULL, NULL, NULL, '3', NULL, 'Both', '-307078637', NULL, NULL, 'John', 'V', 'Hetfield', NULL, NULL, 1, NULL, 'Dear John', 1, NULL, 'Dear John', 1, NULL, 'John Hetfield', NULL, 2, '1984-10-11', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:12:57'),
(222, 'Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 'Hetfield, Emma', 'Mrs. Emma Hetfield', NULL, NULL, NULL, '5', NULL, 'Both', '1537046527', NULL, NULL, 'Emma', 'E', 'Hetfield', 1, NULL, 1, NULL, 'Dear Emma', 1, NULL, 'Dear Emma', 1, NULL, 'Mrs. Emma Hetfield', NULL, 1, '1944-06-29', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2013-08-06 16:12:51', '2013-08-06 16:12:59');


--
-- Dumping data for table `civicrm_email`
--

INSERT INTO `civicrm_email` (`id`, `contact_id`, `location_type_id`, `email`, `is_primary`, `is_billing`, `on_hold`, `is_bulkmail`, `hold_date`, `reset_date`, `signature_text`, `signature_html`) VALUES
(185, 211, 1, 'hammett.cristina13@sample.co.pl', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(186, 219, 1, 'anderson.p.cristina@airmail.biz', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(187, 219, 1, 'cp.anderson@mymail.biz', 0, 0, 0, 0, NULL, NULL, NULL, NULL),
(188, 213, 1, 'johnson.cristina8@mymail.com', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(189, 213, 1, 'cristinajohnson@lol.co.in', 0, 0, 0, 0, NULL, NULL, NULL, NULL),
(190, 217, 1, 'emmahetfield50@fakemail.com', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(191, 203, 1, 'hetfield.w.amy@fakemail.co.nz', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(192, 222, 1, 'hetfielde67@notmail.com', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(193, 205, 1, 'andersonj@infomail.info', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(194, 208, 1, 'andersonc46@spamalot.org', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(195, 204, 1, 'hetfield.emma20@airmail.co.pl', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(196, 204, 1, 'hetfielde@fakemail.org', 0, 0, 0, 0, NULL, NULL, NULL, NULL),
(197, 218, 1, 'eanderson24@testing.co.uk', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(198, 207, 3, 'sales@baytechnologynetwork.org', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(199, 217, 2, 'eo.hetfield54@baytechnologynetwork.org', 0, 0, 0, 0, NULL, NULL, NULL, NULL),
(200, 214, 3, 'info@sierratrust.org', 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(201, 210, 2, 'peterh@sierratrust.org', 1, 0, 0, 0, NULL, NULL, NULL, NULL);

--
-- Dumping data for table `civicrm_hrjob`
--

INSERT INTO `civicrm_hrjob` (`id`, `contact_id`, `position`, `title`, `funding_notes`, `contract_type`, `level_type`, `period_type`, `period_start_date`, `period_end_date`, `manager_contact_id`, `location`, `is_primary`) VALUES
(1, 211, 'Fundraiser', 'Manager2', NULL, 'Contractor', 'Senior Staff', 'Permanent', '2011-03-01', '2015-01-26', 211, 'Headquarters', 1),
(2, 211, 'Volunteer Manager', 'Manager2', NULL, 'Volunteer', 'Junior Staff', 'Permanent', '2011-10-07', '2023-03-29', 207, 'Headquarters', 0),
(3, 211, 'Volunteer Manager', 'Manager2', NULL, 'Apprentice', 'Senior Manager', 'Temporary', '2010-12-18', '2023-03-29', 214, 'Home', 0),
(4, 219, 'Administrator', 'Manager2', NULL, 'Apprentice', 'Junior Staff', 'Permanent', '2011-11-23', '2014-04-17', 206, 'Home', 1),
(5, 219, 'Volunteer', 'Manager1', NULL, 'Trustee', 'Junior Staff', 'Permanent', '2012-04-06', '2015-03-07', 208, 'Home', 0),
(6, 213, 'Administrator', 'Manager1', NULL, 'Volunteer', 'Junior Manager', 'Permanent', '2010-10-10', '2016-01-25', 213, 'Headquarters', 1),
(7, 213, 'Fundraiser', 'Manager1', NULL, 'Apprentice', 'Senior Staff', 'Permanent', '2012-10-10', '2015-01-18', 216, 'Home', 0),
(8, 212, 'Volunteer Manager', 'Manager2', NULL, 'Employee', 'Junior Manager', 'Temporary', '2012-11-24', '2023-03-11', 203, 'Headquarters', 1),
(9, 212, 'Fundraiser', 'Manager2', NULL, 'Employee', 'Senior Staff', 'Permanent', '2012-11-09', '2015-05-03', 216, 'Home', 0),
(10, 217, 'Volunteer', 'Manager1', NULL, 'Apprentice', 'Junior Manager', 'Temporary', '2012-06-25', '2014-01-09', 208, 'Headquarters', 1),
(11, 217, 'Volunteer Manager', 'Manager1', NULL, 'Trustee', 'Senior Staff', 'Temporary', '2010-09-16', '2014-04-08', 210, 'Home', 0),
(12, 203, 'Chief Executive', 'Manager2', NULL, 'Intern', 'Senior Manager', 'Temporary', '2012-10-31', '2016-01-31', 214, 'Headquarters', 1),
(13, 206, 'Chief Executive', 'Manager2', NULL, 'Apprentice', 'Senior Manager', 'Temporary', '2012-10-25', '2017-04-28', 214, 'Home', 1),
(14, 206, 'Volunteer', 'Manager2', NULL, 'Employee', 'Senior Manager', 'Permanent', '2011-11-19', '2018-01-19', 214, 'Home', 0),
(15, 206, 'Chief Executive', 'Manager1', NULL, 'Employee', 'Senior Manager', 'Permanent', '2012-05-04', '2019-04-14', 204, 'Headquarters', 0),
(16, 221, 'Fundraiser', 'Manager2', NULL, 'Employee', 'Junior Staff', 'Temporary', '2011-11-07', '2020-03-02', 204, 'Headquarters', 1),
(17, 221, 'Administrator', 'Manager2', NULL, 'Volunteer', 'Senior Manager', 'Temporary', '2011-09-18', '2018-01-15', 208, 'Home', 0),
(18, 221, 'Volunteer', 'Manager1', NULL, 'Apprentice', 'Senior Staff', 'Permanent', '2012-03-02', '2014-01-23', 206, 'Home', 0),
(19, 222, 'Volunteer Manager', 'Manager1', NULL, 'Employee', 'Junior Manager', 'Permanent', '2012-08-29', '2015-01-21', 211, 'Home', 1),
(20, 220, 'Fundraiser', 'Manager1', NULL, 'Trustee', 'Junior Manager', 'Temporary', '2010-12-31', '2014-03-24', 214, 'Headquarters', 1),
(21, 220, 'Fundraiser', 'Manager1', NULL, 'Employee', 'Junior Staff', 'Temporary', '2010-12-16', '2017-01-14', 203, 'Home', 0),
(22, 205, 'Administrator', 'Manager1', NULL, 'Apprentice', 'Senior Staff', 'Permanent', '2011-04-12', '2023-03-04', 214, 'Home', 1),
(23, 205, 'Fundraiser', 'Manager2', NULL, 'Volunteer', 'Junior Staff', 'Permanent', '2012-10-20', '2018-01-02', 218, 'Headquarters', 0),
(24, 208, 'Volunteer Manager', 'Manager1', NULL, 'Contractor', 'Junior Manager', 'Permanent', '2011-07-23', '2018-02-02', 207, 'Home', 1),
(25, 204, 'Volunteer Manager', 'Manager2', NULL, 'Intern', 'Senior Manager', 'Temporary', '2012-04-01', '2015-02-15', 205, 'Headquarters', 1),
(26, 218, 'Volunteer', 'Manager1', NULL, 'Contractor', 'Junior Manager', 'Temporary', '2010-10-25', '2014-02-21', 204, 'Home', 1),
(27, 209, 'Fundraiser', 'Manager1', NULL, 'Volunteer', 'Senior Manager', 'Temporary', '2012-01-22', '2013-12-24', 208, 'Headquarters', 1),
(28, 209, 'Chief Executive', 'Manager1', NULL, 'Volunteer', 'Junior Staff', 'Temporary', '2012-09-14', '2014-01-18', 216, 'Home', 0),
(29, 209, 'Volunteer Manager', 'Manager1', NULL, 'Intern', 'Senior Manager', 'Permanent', '2011-05-23', '2015-03-15', 222, 'Home', 0),
(30, 216, 'Fundraiser', 'Manager1', NULL, 'Contractor', 'Junior Staff', 'Permanent', '2012-04-08', '2019-12-25', 219, 'Home', 1),
(31, 216, 'Chief Executive', 'Manager1', NULL, 'Volunteer', 'Junior Manager', 'Temporary', '2012-10-07', '2023-03-28', 211, 'Headquarters', 0),
(32, 210, 'Chief Executive', 'Manager1', NULL, 'Contractor', 'Senior Staff', 'Permanent', '2012-03-21', '2020-02-17', 216, 'Home', 1),
(33, 215, 'Fundraiser', 'Manager1', NULL, 'Volunteer', 'Senior Staff', 'Permanent', '2011-01-12', '2013-03-06', 208, 'Headquarters', 1);

--
-- Dumping data for table `civicrm_hrjob_health`
--

INSERT INTO `civicrm_hrjob_health` (`id`, `job_id`, `provider`, `plan_type`, `description`, `dependents`, `provider_life_insurance`, `plan_type_life_insurance`, `description_life_insurance`, `dependents_life_insurance`) VALUES
(1, 1, null, 'Individual', 'Description1', 'dependents2', null, 'Individual', 'Description1', 'dependents2'),
(2, 5, null, 'Individual', 'Description2', 'dependents2', null, 'Individual', 'Description1', 'dependents2'),
(3, 6, null, 'Individual', 'Description1', 'dependents2', null, 'Individual', 'Description1', 'dependents2'),
(4, 7, null, 'Individual', 'Description1', 'dependents1', null, 'Individual', 'Description1', 'dependents2'),
(5, 8, null, 'Family', 'Description2', 'dependents1', null, 'Individual', 'Description1', 'dependents2'),
(6, 9, null, 'Family', 'Description1', 'dependents2', null, 'Individual', 'Description1', 'dependents2'),
(7, 10, null, 'Individual', 'Description2', 'dependents1', null, 'Individual', 'Description1', 'dependents2'),
(8, 12, null, 'Family', 'Description2', 'dependents1', null, 'Individual', 'Description1', 'dependents2'),
(9, 13, null, 'Family', 'Description2', 'dependents1', null, 'Individual', 'Description1', 'dependents2'),
(10, 15, null, 'Family', 'Description2', 'dependents2', null, 'Individual', 'Description1', 'dependents2'),
(11, 16, null, 'Family', 'Description1', 'dependents1', null, 'Individual', 'Description1', 'dependents2'),
(12, 17, null, 'Family', 'Description1', 'dependents2', null, 'Individual', 'Description1', 'dependents2'),
(13, 18, null, 'Family', 'Description1', 'dependents2', null, 'Individual', 'Description1', 'dependents2'),
(14, 25, null, 'Family', 'Description1', 'dependents1', null, 'Individual', 'Description1', 'dependents2'),
(15, 29, null, 'Family', 'Description2', 'dependents1', null, 'Individual', 'Description1', 'dependents2'),
(16, 31, null, 'Family', 'Description2', 'dependents2', null, 'Individual', 'Description1', 'dependents2'),
(17, 32, null, 'Individual', 'Description2', 'dependents1', null, 'Individual', 'Description1', 'dependents2'),
(18, 33, null, 'Individual', 'Description2', 'dependents1', null, 'Individual', 'Description1', 'dependents2');

--
-- Dumping data for table `civicrm_hrjob_hour`
--

INSERT INTO `civicrm_hrjob_hour` (`id`, `job_id`, `hours_type`, `hours_amount`, `hours_unit`, `hours_fte`) VALUES
(1,  1,  0, 32.00, 'Week', 1.10),
(2,  2,  0, 16.00, 'Day',  0.50),
(3,  9,  8,   8.00,  'Year', 1.00),
(4,  12, 8,   16.00, 'Year', 0.75),
(5,  13, 8,   8.00,  'Year', 1.00),
(6,  16, 4,   32.00, 'Day',  1.00),
(7,  20, 0, 40.00, 'Day',  0.66),
(8,  24, 4,   32.00, 'Week', 1.00),
(9,  26, 8,   24.00, 'Day',  1.00),
(10, 29, 0, 40.00, 'Week', 0.75),
(11, 31, 0, 16.00, 'Day',  1.00);

--
-- Dumping data for table `civicrm_hrjob_leave`
--

INSERT INTO `civicrm_hrjob_leave` (`id`, `job_id`, `leave_type`, `leave_amount`) VALUES
(1, 1, 1, 2),
(2, 1, 2, 5),
(3, 1, 3, 3),
(4, 2, 1, 1),
(5, 2, 2, 4),
(6, 2, 3, 1),
(7, 3, 1, 2),
(8, 3, 2, 5),
(9, 3, 3, 2),
(10, 4, 1, 5),
(11, 4, 2, 3),
(12, 4, 3, 3),
(13, 5, 1, 3),
(14, 5, 2, 5),
(15, 5, 3, 4),
(16, 6, 1, 4),
(17, 6, 2, 1),
(18, 6, 3, 2),
(19, 7, 1, 5),
(20, 7, 2, 5),
(21, 7, 3, 3),
(22, 8, 1, 3),
(23, 8, 2, 5),
(24, 8, 3, 3),
(25, 9, 1, 3),
(26, 9, 2, 1),
(27, 9, 3, 4),
(28, 10, 1, 4),
(29, 10, 2, 1),
(30, 10, 3, 2),
(31, 11, 1, 1),
(32, 11, 2, 1),
(33, 11, 3, 5),
(34, 12, 1, 4),
(35, 12, 2, 5),
(36, 12, 3, 1),
(37, 13, 1, 5),
(38, 13, 2, 1),
(39, 13, 3, 1),
(40, 14, 1, 3),
(41, 14, 2, 2),
(42, 14, 3, 5),
(43, 15, 1, 4),
(44, 15, 2, 1),
(45, 15, 3, 2),
(46, 16, 1, 2),
(47, 16, 2, 4),
(48, 16, 3, 2),
(49, 17, 1, 1),
(50, 17, 2, 4),
(51, 17, 3, 5),
(52, 18, 1, 2),
(53, 18, 2, 1),
(54, 18, 3, 4),
(55, 19, 1, 3),
(56, 19, 2, 2),
(57, 19, 3, 2),
(58, 20, 1, 1),
(59, 20, 2, 4),
(60, 20, 3, 1),
(61, 21, 1, 3),
(62, 21, 2, 3),
(63, 21, 3, 1),
(64, 22, 1, 3),
(65, 22, 2, 5),
(66, 22, 3, 2),
(67, 23, 1, 1),
(68, 23, 2, 1),
(69, 23, 3, 4),
(70, 24, 1, 1),
(71, 24, 2, 3),
(72, 24, 3, 1),
(73, 25, 1, 1),
(74, 25, 2, 1),
(75, 25, 3, 1),
(76, 26, 1, 1),
(77, 26, 2, 5),
(78, 26, 3, 2),
(79, 27, 1, 5),
(80, 27, 2, 1),
(81, 27, 3, 4),
(82, 28, 1, 3),
(83, 28, 2, 3),
(84, 28, 3, 3),
(85, 29, 1, 3),
(86, 29, 2, 5),
(87, 29, 3, 5),
(88, 30, 1, 5),
(89, 30, 2, 1),
(90, 30, 3, 2),
(91, 31, 1, 2),
(92, 31, 2, 4),
(93, 31, 3, 1),
(94, 32, 1, 5),
(95, 32, 2, 4),
(96, 32, 3, 3),
(97, 33, 1, 2),
(98, 33, 2, 1),
(99, 33, 3, 5);

--
-- Dumping data for table `civicrm_hrjob_pay`
--

INSERT INTO `civicrm_hrjob_pay` (`id`, `job_id`, `pay_grade`, `pay_amount`, `pay_unit`, `pay_annualized_est`, `pay_is_auto_est`, `pay_currency`) VALUES
(1,  2,  'Paid',   80.00,  'Day',  0.50*80*250,    0, NULL),
(2,  3,  'Paid',   200.00, 'Hour', 0.75*200*2000,  0, NULL),
(3,  4,  'Unpaid', 200.00, 'Day',  1.00*200*250,   0, NULL),
(4,  7,  'Paid',   90.00,  'Day',  1.10*90*250,    0, NULL),
(5,  10, 'Paid',   40.00,  'Year', 0.50*40,        0, NULL),
(6,  15, 'Paid',   200.00, 'Hour', 0.750*200*2000, 0, NULL),
(7,  16, 'Unpaid', 200.00, 'Year', 1.00*200,       0, NULL),
(8,  17, 'Paid',   80.00,  'Day',  1.10*80*2000,   0, NULL),
(9,  21, 'Unpaid', 90.00,  'Year', 0.50*90,        0, NULL),
(10, 25, 'Unpaid', 40.00,  'Day',  0.75*40*250,    0, NULL),
(11, 27, 'Paid',   80.00,  'Year', 1.00*80,        0, NULL),
(12, 28, 'Unpaid', 200.00, 'Year', 1.00*200,       0, NULL),
(13, 31, 'Paid',   200.00, 'Day',  1.10*200*2000,  0, NULL),
(14, 33, 'Paid',   80.00,  'Year', 1.10*80,        0, NULL);

--
-- Dumping data for table `civicrm_hrjob_pension`
--

INSERT INTO `civicrm_hrjob_pension` (`id`, `job_id`, `is_enrolled`, `ee_contrib_pct`, `er_contrib_pct`) VALUES
(1, 3, 1, 200.00, 100.00),
(2, 4, 0, 250.00, 100.00),
(3, 7, 1, 300.00, 400.00),
(4, 8, 0, 150.00, 400.00),
(5, 9, 1, 300.00, 300.00),
(6, 10, 0, 50.00, 200.00),
(7, 11, 1, 125.00, 100.00),
(8, 13, 1, 150.00, 100.00),
(9, 15, 1, 300.00, 200.00),
(10, 20, 1, 400.00, 100.00),
(11, 21, 1, 500.00, 400.00),
(12, 22, 1, 275.00, 100.00),
(13, 26, 0, 135.00, 100.00),
(14, 30, 1, 400.00, 300.00),
(15, 32, 0, 200.00, 400.00),
(16, 33, 0, 300.00, 200.00);

--
-- Dumping data for table `civicrm_hrjob_role`
--

INSERT INTO `civicrm_hrjob_role` (`id`, `job_id`, `title`, `description`, `hours`, `region`, `department`, `manager_contact_id`, `functional_area`, `organization`, `cost_center`, `location`) VALUES
(1, 1, 'Manager2', 'desc3', 24.00, 'Europe', 'Operations', 217, 'Save the Rhinos', 'ZINGIT', '003', NULL),
(2, 1, 'Manager1', 'desc1', 16.00, 'Europe', 'Finance', 216, 'Save the Panda', 'UP', '004', NULL),
(3, 3, 'Manager1', 'desc1', 32.00, 'North America', 'Operations', 204, 'Save the Panda', 'ZINGIT', '004', NULL),
(4, 3, 'Manager2', 'desc4', 32.00, 'Europe', 'Fundraising', 212, 'Save the Tigers', 'UP', '004', NULL),
(5, 3, 'Manager1', 'desc1', 24.00, 'Asia', 'Finance', 217, 'Save the Whales', 'ZING', '003', NULL),
(6, 5, 'Manager2', 'desc3', 8.00, 'Europe', 'Fundraising', 205, 'Save the Whales', 'UP', '004', NULL),
(7, 6, 'Manager2', 'desc4', 40.00, 'Africa', 'Operations', 220, 'Save the Whales', 'UP', '005', NULL),
(8, 7, 'Manager1', 'desc3', 16.00, 'South America', 'Fundraising', 213, 'Save the Panda', 'ZING', '001', NULL),
(9, 7, 'Manager2', 'desc1', 40.00, 'Europe', 'HR', 209, 'Save the Panda', 'UP', '003', NULL),
(10, 8, 'Manager1', 'desc4', 8.00, 'North America', 'Fundraising', 221, 'Save the Elephant', 'ZING', '005', NULL),
(11, 9, 'Manager2', 'desc3', 40.00, 'Australasia', 'Finance', 209, 'Save the Elephant', 'UP', '002', NULL),
(12, 9, 'Manager1', 'desc1', 8.00, 'Asia', 'HR', 216, 'Save the Rhinos', 'ZING', '003', NULL),
(13, 10, 'Manager1', 'desc1', 24.00, 'North America', 'Fundraising', 221, 'Save the Tigers', 'ZINGIT', '004', NULL),
(14, 10, 'Manager2', 'desc2', 8.00, 'North America', 'Advocacy', 203, 'Save the Tigers', 'ZINGIT', '002', NULL),
(15, 11, 'Manager1', 'desc3', 40.00, 'North America', 'HR', 213, 'Save the Elephant', 'UP', '002', NULL),
(16, 12, 'Manager1', 'desc2', 32.00, 'South America', 'HR', 214, 'Save the Tigers', 'ZINGIT', '003', NULL),
(17, 13, 'Manager2', 'desc2', 24.00, 'North America', 'Advocacy', 221, 'Save the Elephant', 'ZING', '004', NULL),
(18, 14, 'Manager2', 'desc2', 8.00, 'Australasia', 'Fundraising', 217, 'Save the Tigers', 'ZINGIT', '005', NULL),
(19, 14, 'Manager2', 'desc3', 40.00, 'Europe', 'Fundraising', 215, 'Save the Tigers', 'UP', '002', NULL),
(20, 15, 'Manager2', 'desc3', 32.00, 'Asia', 'Advocacy', 205, 'Save the Elephant', 'ZINGIT', '002', NULL),
(21, 15, 'Manager2', 'desc4', 16.00, 'Europe', 'HR', 220, 'Save the Rhinos', 'UP', '002', NULL),
(22, 15, 'Manager2', 'desc1', 16.00, 'Australasia', 'Finance', 207, 'Save the Elephant', 'ZINGIT', '002', NULL),
(23, 16, 'Manager2', 'desc3', 16.00, 'South America', 'Fundraising', 211, 'Save the Elephant', 'ZINGIT', '001', NULL),
(24, 16, 'Manager1', 'desc2', 32.00, 'South America', 'Operations', 213, 'Save the Panda', 'ZINGIT', '005', NULL),
(25, 16, 'Manager1', 'desc4', 8.00, 'Africa', 'Advocacy', 218, 'Save the Rhinos', 'ZING', '004', NULL),
(26, 17, 'Manager1', 'desc3', 32.00, 'Europe', 'Finance', 220, 'Save the Whales', 'ZINGIT', '002', NULL),
(27, 17, 'Manager1', 'desc1', 16.00, 'South America', 'Advocacy', 222, 'Save the Rhinos', 'ZINGIT', '005', NULL),
(28, 18, 'Manager2', 'desc1', 32.00, 'Africa', 'Fundraising', 208, 'Save the Rhinos', 'UP', '005', NULL),
(29, 18, 'Manager2', 'desc2', 24.00, 'North America', 'Finance', 215, 'Save the Panda', 'ZINGIT', '003', NULL),
(30, 19, 'Manager2', 'desc1', 24.00, 'Australasia', 'Operations', 222, 'Save the Rhinos', 'ZING', '003', NULL),
(31, 20, 'Manager1', 'desc4', 24.00, 'Africa', 'Operations', 203, 'Save the Rhinos', 'ZINGIT', '005', NULL),
(32, 20, 'Manager1', 'desc1', 8.00, 'North America', 'Advocacy', 222, 'Save the Elephant', 'ZINGIT', '002', NULL),
(33, 20, 'Manager1', 'desc3', 8.00, 'Asia', 'Operations', 206, 'Save the Elephant', 'UP', '001', NULL),
(34, 22, 'Manager1', 'desc4', 8.00, 'Asia', 'Advocacy', 222, 'Save the Whales', 'UP', '002', NULL),
(35, 24, 'Manager2', 'desc1', 32.00, 'North America', 'Advocacy', 213, 'Save the Tigers', 'UP', '001', NULL),
(36, 24, 'Manager1', 'desc2', 40.00, 'Australasia', 'Advocacy', 215, 'Save the Whales', 'UP', '002', NULL),
(37, 24, 'Manager2', 'desc1', 8.00, 'North America', 'Operations', 217, 'Save the Panda', 'ZINGIT', '003', NULL),
(38, 26, 'Manager1', 'desc1', 8.00, 'Africa', 'HR', 206, 'Save the Elephant', 'ZINGIT', '002', NULL),
(39, 26, 'Manager2', 'desc3', 40.00, 'Africa', 'Operations', 212, 'Save the Whales', 'ZING', '002', NULL),
(40, 26, 'Manager2', 'desc4', 8.00, 'Asia', 'HR', 207, 'Save the Tigers', 'UP', '004', NULL),
(41, 27, 'Manager2', 'desc3', 24.00, 'Europe', 'Operations', 207, 'Save the Rhinos', 'ZING', '001', NULL),
(42, 28, 'Manager1', 'desc1', 16.00, 'Europe', 'Advocacy', 220, 'Save the Elephant', 'ZING', '004', NULL),
(43, 29, 'Manager1', 'desc1', 32.00, 'Europe', 'Finance', 204, 'Save the Panda', 'UP', '002', NULL),
(44, 29, 'Manager2', 'desc2', 24.00, 'North America', 'HR', 209, 'Save the Whales', 'UP', '002', NULL),
(45, 29, 'Manager1', 'desc2', 8.00, 'Asia', 'Operations', 215, 'Save the Elephant', 'ZINGIT', '004', NULL),
(46, 32, 'Manager2', 'desc4', 40.00, 'Europe', 'Operations', 203, 'Save the Whales', 'ZINGIT', '002', NULL),
(47, 32, 'Manager1', 'desc3', 24.00, 'Asia', 'Finance', 216, 'Save the Tigers', 'ZINGIT', '001', NULL),
(48, 32, 'Manager2', 'desc4', 24.00, 'South America', 'HR', 214, 'Save the Tigers', 'ZING', '001', NULL),
(49, 33, 'Manager1', 'desc1', 32.00, 'Europe', 'Fundraising', 210, 'Save the Rhinos', 'UP', '002', NULL),
(50, 33, 'Manager1', 'desc1', 24.00, 'Asia', 'Advocacy', 217, 'Save the Whales', 'ZING', '002', NULL),
(51, 33, 'Manager1', 'desc4', 24.00, 'Asia', 'HR', 211, 'Save the Rhinos', 'UP', '002', NULL);

--
-- Dumping data for table `civicrm_phone`
--

INSERT INTO `civicrm_phone` (`id`, `contact_id`, `location_type_id`, `is_primary`, `is_billing`, `mobile_provider_id`, `phone`, `phone_ext`, `phone_numeric`, `phone_type_id`) VALUES
(167, 219, 1, 1, 0, NULL, '491-2170', NULL, '4912170', 1),
(168, 213, 1, 1, 0, NULL, '(425) 514-6819', NULL, '4255146819', 1),
(169, 213, 1, 0, 0, NULL, '858-9417', NULL, '8589417', 1),
(170, 212, 1, 1, 0, NULL, '(718) 381-7836', NULL, '7183817836', 1),
(171, 203, 1, 1, 0, NULL, '(828) 354-3644', NULL, '8283543644', 2),
(172, 221, 1, 1, 0, NULL, '(553) 652-3547', NULL, '5536523547', 1),
(173, 221, 1, 0, 0, NULL, '(317) 840-6479', NULL, '3178406479', 1),
(174, 222, 1, 1, 0, NULL, '(411) 847-9904', NULL, '4118479904', 1),
(175, 205, 1, 1, 0, NULL, '516-9861', NULL, '5169861', 2),
(176, 205, 1, 0, 0, NULL, '(485) 886-8861', NULL, '4858868861', 2),
(177, 208, 1, 1, 0, NULL, '666-1635', NULL, '6661635', 1),
(178, 204, 1, 1, 0, NULL, '751-6648', NULL, '7516648', 2),
(179, 209, 1, 1, 0, NULL, '(406) 740-9703', NULL, '4067409703', 2),
(180, 216, 1, 1, 0, NULL, '541-3783', NULL, '5413783', 2),
(181, 210, 1, 1, 0, NULL, '(896) 730-2524', NULL, '8967302524', 1),
(182, 210, 1, 0, 0, NULL, '627-6813', NULL, '6276813', 2);

