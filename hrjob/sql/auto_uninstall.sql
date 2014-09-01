ALTER TABLE `civicrm_hrjob` DROP FOREIGN KEY `FK_civicrm_hrjob_contact_id`;
ALTER TABLE `civicrm_hrjob` DROP FOREIGN KEY `FK_civicrm_hrjob_manager_contact_id`;
ALTER TABLE `civicrm_hrjob` DROP FOREIGN KEY `FK_civicrm_hrjob_funding_org_id`;
ALTER TABLE `civicrm_hrjob` DROP FOREIGN KEY `FK_civicrm_hrjob_funder`;

DROP TABLE IF EXISTS `civicrm_hrjob_role`;
DROP TABLE IF EXISTS `civicrm_hrjob_pension`;
DROP TABLE IF EXISTS `civicrm_hrjob_leave`;
DROP TABLE IF EXISTS `civicrm_hrjob_hour`;
DROP TABLE IF EXISTS `civicrm_hrjob_health`;
DROP TABLE IF EXISTS `civicrm_hrjob_pay`;
DROP TABLE IF EXISTS `civicrm_hrjob`;

