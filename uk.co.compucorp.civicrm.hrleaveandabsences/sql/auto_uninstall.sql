DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_toil_request`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_sickness_request`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_contact_work_pattern`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_leave_request_date`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_leave_request`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_leave_balance_change`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_leave_period_entitlement`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_notification_receiver`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_absence_type`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_work_day`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_work_week`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_work_pattern`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_absence_period`;
DROP TABLE IF EXISTS `civicrm_hrleaveandabsences_public_holiday`;

-- Deletes sample data for contracts (for l&a, the drop tables already take care of the job)
SET foreign_key_checks = 0;
TRUNCATE civicrm_hrjobcontract_revision;
TRUNCATE civicrm_hrjobcontract_details;
TRUNCATE civicrm_hrjobcontract_health;
TRUNCATE civicrm_hrjobcontract_hour;
TRUNCATE civicrm_hrjobcontract_leave;
TRUNCATE civicrm_hrjobcontract_pay;
TRUNCATE civicrm_hrjobcontract_pension;
TRUNCATE civicrm_hrjobcontract_role;
TRUNCATE civicrm_hrjobcontract;
DELETE FROM `civicrm_relationship` WHERE contact_id_a = 202 AND contact_id_b = 203 and civicrm_relationship.relationship_type_id = (SELECT rt.id FROM `civicrm_relationship_type` rt WHERE rt.name_a_b = 'has Leave Approved By');
SET foreign_key_checks = 1;
