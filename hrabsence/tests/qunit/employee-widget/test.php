<?php
// CRM_Core_Resources::singleton()->addScriptFile(...);
// CRM_Core_Resources::singleton()->addStyleFile(...);
// CRM_Core_Resources::singleton()->addSetting(...);

CRM_HRAbsence_Page_EmployeeAbsencePage::registerResources(
  0,
  array(
    10 => 'Vacation',
    11 => 'TOIL',
    12 => 'TOIL (Credit)',
  ),
  array(
    2 => array(
      'id' => 2,
      'name' => 'FY2012',
      'title' => 'FY 2012',
      'start_date' => '2012-04-01 00:00:00',
      'end_date' => '2013-03-31 23:59:59',
    ),
    3 => array(
      'id' => 3,
      'name' => 'FY2013',
      'title' => 'FY 2013',
      'start_date' => '2013-04-01 00:00:00',
      'end_date' => '2014-03-31 23:59:59',
    ),
  )
);

CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrabsence', 'tests/qunit/assert.js', 10);
CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrabsence', 'tests/qunit/employee-widget/mock-data.js', 10);
CRM_HRAbsence_Page_EmployeeAbsencePage::addScriptFiles('org.civicrm.hrabsence', 'tests/qunit/employee-widget/test-*.js', 20);
