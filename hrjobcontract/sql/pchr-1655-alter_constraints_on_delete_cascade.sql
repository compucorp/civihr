# civicrm_hrjobcontract

ALTER TABLE `civicrm_hrjobcontract` 
DROP FOREIGN KEY `FK_civicrm_hrjobcontract_contact_id`;

ALTER TABLE `civicrm_hrjobcontract` 
ADD CONSTRAINT `FK_civicrm_hrjobcontract_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;


# civicrm_hrjobcontract_revision

ALTER TABLE `civicrm_hrjobcontract_revision`
DROP FOREIGN KEY `FK_civicrm_hrjobcontract_revision_jobcontract_id`;

ALTER TABLE `civicrm_hrjobcontract_revision`
ADD CONSTRAINT `FK_civicrm_hrjobcontract_revision_jobcontract_id` FOREIGN KEY (`jobcontract_id`) REFERENCES `civicrm_hrjobcontract` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;


# civicrm_hrjobcontract_details

ALTER TABLE `civicrm_hrjobcontract_details`
DROP FOREIGN KEY `FK_civicrm_hrjobcontract_details_contract_revision_id`;

ALTER TABLE `civicrm_hrjobcontract_details`
ADD CONSTRAINT `FK_civicrm_hrjobcontract_details_contract_revision_id` FOREIGN KEY (`jobcontract_revision_id`) REFERENCES `civicrm_hrjobcontract_revision` (`details_revision_id`) ON DELETE CASCADE ON UPDATE NO ACTION;


# civicrm_hrjobcontract_pay

ALTER TABLE `civicrm_hrjobcontract_pay`
DROP FOREIGN KEY `FK_civicrm_hrjobcontract_pay_jobcontract_revision_id`;

ALTER TABLE `civicrm_hrjobcontract_pay`
ADD CONSTRAINT `FK_civicrm_hrjobcontract_pay_jobcontract_revision_id`  FOREIGN KEY (`jobcontract_revision_id`)  REFERENCES `civicrm_hrjobcontract_revision` (`pay_revision_id`) ON DELETE CASCADE ON UPDATE NO ACTION;


# civicrm_hrjobcontract_health

ALTER TABLE `civicrm_hrjobcontract_health`
DROP FOREIGN KEY `FK_civicrm_hrjobcontract_health_jobcontract_revision_id`;

ALTER TABLE `civicrm_hrjobcontract_health`
ADD CONSTRAINT `FK_civicrm_hrjobcontract_health_jobcontract_revision_id`  FOREIGN KEY (`jobcontract_revision_id`)  REFERENCES `civicrm_hrjobcontract_revision` (`health_revision_id`) ON DELETE CASCADE ON UPDATE NO ACTION;


# civicrm_hrjobcontract_hour

ALTER TABLE `civicrm_hrjobcontract_hour`
DROP FOREIGN KEY `FK_civicrm_hrjobcontract_hour_jobcontract_revision_id`;

ALTER TABLE `civicrm_hrjobcontract_hour`
ADD CONSTRAINT `FK_civicrm_hrjobcontract_hour_jobcontract_revision_id`  FOREIGN KEY (`jobcontract_revision_id`)  REFERENCES `civicrm_hrjobcontract_revision` (`hour_revision_id`) ON DELETE CASCADE ON UPDATE NO ACTION;


# civicrm_hrjobcontract_leave

ALTER TABLE `civicrm_hrjobcontract_leave`
DROP FOREIGN KEY `FK_civicrm_hrjobcontract_leave_jobcontract_revision_id`;

ALTER TABLE `civicrm_hrjobcontract_leave`
ADD CONSTRAINT `FK_civicrm_hrjobcontract_leave_jobcontract_revision_id`  FOREIGN KEY (`jobcontract_revision_id`)  REFERENCES `civicrm_hrjobcontract_revision` (`leave_revision_id`) ON DELETE CASCADE ON UPDATE NO ACTION;


# civicrm_hrjobcontract_pension

ALTER TABLE `civicrm_hrjobcontract_pension`
DROP FOREIGN KEY `FK_civicrm_hrjobcontract_pension_jobcontract_revision_id`;

ALTER TABLE `civicrm_hrjobcontract_pension`
ADD CONSTRAINT `FK_civicrm_hrjobcontract_pension_jobcontract_revision_id`  FOREIGN KEY (`jobcontract_revision_id`)  REFERENCES `civicrm_hrjobcontract_revision` (`pension_revision_id`) ON DELETE CASCADE ON UPDATE NO ACTION;


# civicrm_hrjobcontract_role

ALTER TABLE `civicrm_hrjobcontract_role`
DROP FOREIGN KEY `FK_civicrm_hrjobcontract_role_jobcontract_revision_id`;

ALTER TABLE `civicrm_hrjobcontract_role`
ADD CONSTRAINT `FK_civicrm_hrjobcontract_role_jobcontract_revision_id`  FOREIGN KEY (`jobcontract_revision_id`)  REFERENCES `civicrm_hrjobcontract_revision` (`role_revision_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
