SET foreign_key_checks = 0;
TRUNCATE TABLE civicrm_activity;
TRUNCATE TABLE civicrm_activity_contact;
TRUNCATE TABLE civicrm_address;
TRUNCATE TABLE civicrm_case;
TRUNCATE TABLE civicrm_case_activity;
TRUNCATE TABLE civicrm_case_contact;

DELETE FROM civicrm_contact
WHERE id !=1
      AND id NOT IN (SELECT contact_id FROM civicrm_uf_match);

DELETE FROM civicrm_email
WHERE contact_id !=1
      AND id NOT IN (SELECT contact_id FROM civicrm_uf_match);

TRUNCATE TABLE civicrm_contactaccessrights_rights;
TRUNCATE TABLE civicrm_group_contact;
TRUNCATE TABLE civicrm_group_organization;
TRUNCATE TABLE civicrm_hrabsence_entitlement;
TRUNCATE TABLE civicrm_hrjobcontract;
TRUNCATE TABLE civicrm_hrjobcontract_details;
TRUNCATE TABLE civicrm_hrjobcontract_health;
TRUNCATE TABLE civicrm_hrjobcontract_hour;
TRUNCATE TABLE civicrm_hrjobcontract_leave;
TRUNCATE TABLE civicrm_hrjobcontract_pay;
TRUNCATE TABLE civicrm_hrjobcontract_pension;
TRUNCATE TABLE civicrm_hrjobcontract_revision;
TRUNCATE TABLE civicrm_hrjobcontract_role;
TRUNCATE TABLE civicrm_hrjobroles;
TRUNCATE TABLE civicrm_hrvacancy;
TRUNCATE TABLE civicrm_hrvacancy_permission;
TRUNCATE TABLE civicrm_hrvacancy_stage;
TRUNCATE TABLE civicrm_im;
TRUNCATE TABLE civicrm_loc_block;
TRUNCATE TABLE civicrm_log;
TRUNCATE TABLE civicrm_note;
TRUNCATE TABLE civicrm_phone;
TRUNCATE TABLE civicrm_website;
TRUNCATE TABLE civicrm_relationship;
SET foreign_key_checks = 1;
