<?php
return array(
  //testcase for CiviHR Contact Summary Report using "in" operator for filters
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'id',
        'state_province_id',
        'contract_type',
        'level_type',
        'period_type',
        'location',
        'job_positions',
      ),
      'group_bys' => array(
        'state_province_id',
      ),
      'filters' => array(
        'is_tied_to_funding_op' => 'eq',
        'is_tied_to_funding_value' => 1,
        'contract_type_op' => 'in',
        'contract_type_value' => 'Apprentice,Employee,Volunteer',
        'level_type_op' => 'in',
        'level_type_value' => 'Senior Manager,Junior Manager,Senior Staff',
        'period_type_op' => 'in',
        'period_type_value' => 'Permanent',
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
        'contract_type',
        'level_type',
        'period_type',
        'location',
        'job_positions',
      ),
      'group_bys' => array(
        'state_province_id',
      ),
      'filters' => array(
        'is_tied_to_funding_op' => 'eq',
        'is_tied_to_funding_value' => 1,
        'contract_type_op' => 'notin',
        'contract_type_value' => 'Intern,Trustee,Contractor',
        'level_type_op' => 'notin',
        'level_type_value' => 'Junior Staff',
        'period_type_op' => 'notin',
        'period_type_value' => 'Temporary',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-filter-testCase1.csv',
  )
);