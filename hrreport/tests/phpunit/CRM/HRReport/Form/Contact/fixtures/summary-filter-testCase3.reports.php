<?php
return array(
  //testcase for CiviHR Annual and Monthly Cost Equivalents Report with "in" operator for level type and "notin" operator for period type filters
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'hrjob_level_type',
        'hrjob_period_type',
        'hrjob_location',
        'monthly_cost_eq',
        'annual_cost_eq',
      ),
      'group_bys' => array(
        'hrjob_level_type',
        'hrjob_location',
      ),
      'filters' => array(
        'hrjob_level_type_op' => 'in',
        'hrjob_level_type_value' => 'Senior Manager,Junior Manager,Senior Staff',
        'hrjob_period_type_op' => 'notin',
        'hrjob_period_type_value' => 'Temporary',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-filter-testCase3.csv',
  ),
  //testcase for CiviHR Annual and Monthly Cost Equivalents Report with "in" operator for period type and "notin" operator for level type filters
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'hrjob_level_type',
        'hrjob_period_type',
        'hrjob_location',
        'monthly_cost_eq',
        'annual_cost_eq',
      ),
      'group_bys' => array(
        'hrjob_level_type',
        'hrjob_location',
      ),
      'filters' => array(
        'hrjob_level_type_op' => 'notin',
        'hrjob_level_type_value' => 'Junior Staff',
        'hrjob_period_type_op' => 'in',
        'hrjob_period_type_value' => 'Permanent',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-filter-testCase3.csv',
  ),
);