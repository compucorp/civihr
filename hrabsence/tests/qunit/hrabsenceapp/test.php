<?php
// CRM_Core_Resources::singleton()->addScriptFile(...);
// CRM_Core_Resources::singleton()->addStyleFile(...);
// CRM_Core_Resources::singleton()->addSetting(...);

CRM_HRAbsence_Page_EmployeeAbsencePage::registerResources(
  202, // contactId
  array( // absenceTypes
    100 => array(
      'id' => 100,
      'name' => 'Vacation',
      'title' => 'Vacation',
      'is_active' => 1,
      'allow_debits' => 1,
      'debit_activity_type_id' => 10,
      'allow_credits' => 0,
      'credit_activity_type_id' => NULL,
    ),
    101 => array(
      'id' => 101,
      'name' => 'TOIL',
      'title' => 'TOIL',
      'is_active' => 1,
      'allow_debits' => 1,
      'debit_activity_type_id' => 11,
      'allow_credits' => 1,
      'credit_activity_type_id' => 12,
    ),
  ),
  array( // activityTypes
    10 => 'Vacation',
    11 => 'TOIL',
    12 => 'TOIL (Credit)',
  ),
  array( // periods
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
CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrabsence', 'tests/qunit/hrabsenceapp/fixtures.js', 10);
CRM_HRAbsence_Page_EmployeeAbsencePage::addScriptFiles('org.civicrm.hrabsence', 'tests/qunit/hrabsenceapp/test-*.js', 20);
