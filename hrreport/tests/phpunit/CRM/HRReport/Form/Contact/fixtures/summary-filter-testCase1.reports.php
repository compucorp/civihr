<?php
return array(
  //testcase for CiviHR Contact Summary Report using "in" operator for filters
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'id',
        'state_province_id',
        'hrjob_contract_type',
        'hrjob_level_type',
        'hrjob_period_type',
        'hrjob_location',
        'job_positions',
      ),
      'group_bys' => array(
        'state_province_id',
      ),
      'filters' => array(
        'is_tied_to_funding_op' => 'eq',
        'is_tied_to_funding_value' => 1,
        'hrjob_contract_type_op' => 'in',
        'hrjob_contract_type_value' => 'Apprentice,Employee,Volunteer',
        'hrjob_level_type_op' => 'in',
        'hrjob_level_type_value' => 'Senior Manager,Junior Manager,Senior Staff',
        'hrjob_period_type_op' => 'in',
        'hrjob_period_type_value' => 'Permanent',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-filter-testCase1.csv',
  ),

  //testcase for CiviHR Contact Summary Report using "notin" operator for filters
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'id',
        'state_province_id',
        'hrjob_contract_type',
        'hrjob_level_type',
        'hrjob_period_type',
        'hrjob_location',
        'job_positions',
      ),
      'group_bys' => array(
        'state_province_id',
      ),
      'filters' => array(
        'is_tied_to_funding_op' => 'eq',
        'is_tied_to_funding_value' => 1,
        'hrjob_contract_type_op' => 'notin',
        'hrjob_contract_type_value' => 'Intern,Trustee,Contractor',
        'hrjob_level_type_op' => 'notin',
        'hrjob_level_type_value' => 'Junior Staff',
        'hrjob_period_type_op' => 'notin',
        'hrjob_period_type_value' => 'Temporary',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-filter-testCase1.csv',
  )
);
