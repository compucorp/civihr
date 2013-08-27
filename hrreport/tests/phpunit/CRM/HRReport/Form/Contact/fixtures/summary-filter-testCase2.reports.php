<?php
return array(
  //testcase for CiviHR Contact fte Report with some filters using "in" operator for level type
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'hrjob_level_type',
        'full_time_eq',
      ),
      'group_bys' => array(
        'hrjob_level_type',
      ),
      'filters' => array(
        'hrjob_level_type_op' => 'in',
        'hrjob_level_type_value' => 'Senior Manager,Junior Staff,Senior Staff',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-filter-testCase2.csv',
  ),
  //testcase for CiviHR Contact fte Report with some filters using "notin" operator for level type
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'hrjob_level_type',
        'full_time_eq',
      ),
      'group_bys' => array(
        'hrjob_level_type',
      ),
      'filters' => array(
        'hrjob_level_type_op' => 'notin',
        'hrjob_level_type_value' => 'Junior Manager',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-filter-testCase2.csv',
  ),
);