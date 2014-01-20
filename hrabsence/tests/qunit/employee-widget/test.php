<?php
// CRM_Core_Resources::singleton()->addScriptFile(...);
// CRM_Core_Resources::singleton()->addStyleFile(...);
// CRM_Core_Resources::singleton()->addSetting(...);

CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrabsence', 'tests/qunit/assert.js');
CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrabsence', 'tests/qunit/employee-widget/mock-data.js');

CRM_HRAbsence_Page_EmployeeAbsencePage::registerResources(
  0,
  array(
    10 => 'Vacation',
    11 => 'TOIL',
    12 => 'TOIL (Credit)',
  ),
  array(
    3 => 'FY 2013',
    2 => 'FY 2012',
  )
);

