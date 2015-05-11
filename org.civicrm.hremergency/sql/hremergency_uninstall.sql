/**
 * SQLs to clean the CiviCRM when the extension is uninstalled
 */

SET foreign_key_checks = 0;

DROP TABLE IF EXISTS civicrm_value_emergency_contacts_21;

DELETE FROM civicrm_option_value 
WHERE option_group_id IN (SELECT id FROM civicrm_option_group WHERE name = "relationship_with_employee_20150304120408");

DELETE FROM civicrm_option_group 
WHERE name = "relationship_with_employee_20150304120408";

DELETE FROM civicrm_custom_field 
WHERE custom_group_id IN (SELECT id FROM civicrm_custom_group WHERE name = "Emergency_Contacts");

DELETE FROM civicrm_custom_group
WHERE name = "Emergency_Contacts";

SET foreign_key_checks = 1;