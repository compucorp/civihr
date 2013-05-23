<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
return array (
  0 =>
  array (
    'name' => 'CiviHR Contact Detail Report',
    'entity' => 'ReportTemplate',
    'params' =>
    array (
      'version' => 3,
      'label' => 'CiviHR Contact Detail Report',
      'description' => 'Report has basic contact information with applicant details',
      'class_name' => 'CRM_HRReport_Form_Contact_HRDetail',
      'report_url' => 'civihr/detail',
      'component' => '',
    ),
  ),
);