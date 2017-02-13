<?php
// This file declares a managed database record of type "CaseType" and "OptionValue".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference

$defaultManagedRecords = new CRM_HRCase_DefaultCaseAndActivityTypes();

return array_merge
(
  CRM_HRCase_DefaultCaseAndActivityTypes::getDefaultActivityTypesManagedRecords(),
  CRM_HRCase_DefaultCaseAndActivityTypes::getDefaultCaseTypesManagedRecords()
);
