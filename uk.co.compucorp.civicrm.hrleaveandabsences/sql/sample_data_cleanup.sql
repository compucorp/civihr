SET foreign_key_checks = 0;
SELECT @staffID:=id FROM civicrm_contact WHERE display_name = 'civihr_staff@compucorp.co.uk';
SELECT @managerID:=id FROM civicrm_contact WHERE display_name = 'civihr_manager@compucorp.co.uk';

DELETE details,
  job_leave,
  health,
  hour,
  pay,
  pension,
  role,
  revision,
  contract
FROM civicrm_hrjobcontract contract
  LEFT JOIN civicrm_hrjobcontract_revision revision
    ON contract.id = revision.jobcontract_id
  LEFT JOIN civicrm_hrjobcontract_details details
    ON details.jobcontract_revision_id = revision.id
  LEFT JOIN civicrm_hrjobcontract_leave job_leave
    ON job_leave.jobcontract_revision_id = revision.id
  LEFT JOIN civicrm_hrjobcontract_health health
    ON health.jobcontract_revision_id = revision.id
  LEFT JOIN civicrm_hrjobcontract_hour hour
    ON hour.jobcontract_revision_id = revision.id
  LEFT JOIN civicrm_hrjobcontract_pay pay
    ON pay.jobcontract_revision_id = revision.id
  LEFT JOIN civicrm_hrjobcontract_pension pension
    ON pension.jobcontract_revision_id = revision.id
  LEFT JOIN civicrm_hrjobcontract_role role
    ON role.jobcontract_revision_id = revision.id
WHERE contract.contact_id = @staffID;

TRUNCATE civicrm_hrleaveandabsences_absence_period;
TRUNCATE civicrm_hrleaveandabsences_public_holiday;
TRUNCATE civicrm_hrleaveandabsences_leave_period_entitlement;
TRUNCATE civicrm_hrleaveandabsences_leave_request;
TRUNCATE civicrm_hrleaveandabsences_leave_request_date;
TRUNCATE civicrm_hrleaveandabsences_leave_balance_change;

DELETE FROM civicrm_relationship
  WHERE contact_id_a = @staffID AND
        contact_id_b = @managerID AND
        relationship_type_id = (SELECt id FROM civicrm_relationship_type WHERE name_a_b = 'has Leave Approved by');

SET @staffID = NULL;
SET @managerID = NULL;

SET foreign_key_checks = 1;
