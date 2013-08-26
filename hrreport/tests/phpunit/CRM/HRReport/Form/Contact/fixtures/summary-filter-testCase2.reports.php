<?php
return array(
  //testcase for CiviHR Contact fte Report with some filters using "in" operator for level type
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'title',
        'level_type',
        'fte',
      ),
      'group_bys' => array(
        'title',
        'level_type',
      ),
      'filters' => array(
        'title_op' => 'like',
        'title_value' => 'Manager2',
        'level_type_op' => 'in',
        'level_type_value' => 'Senior Manager,Junior Staff,Senior Staff',
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
        'title',
        'level_type',
        'fte',
      ),
      'group_bys' => array(
        'title',
        'level_type',
      ),
      'filters' => array(
        'title_op' => 'like',
        'title_value' => 'Manager2',
        'level_type_op' => 'notin',
        'level_type_value' => 'Junior Manager',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-filter-testCase2.csv',
  ),
);