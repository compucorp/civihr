<?php
return array(
  //testCase with hrjob isTiedToFunding,level type and contract type filters with "in" operator
  array(
    'CRM_HRReport_Form_Contact_HRDetail',
    array(
      'fields' => array(
        'sort_name',
        'email',
        'hrjob_position',
        'hrjob_title',
        'hrjob_contract_type',
        'hrjob_level_type',
        'hrjob_period_type',
        'hrjob_location',
      ),
      'filters' => array(
        'is_tied_to_funding_op' => 'eq',
        'is_tied_to_funding_value' => 1,
        'hrjob_level_type_op' => 'in',
        'hrjob_level_type_value' => "Senior Manager,Junior Manager,Junior Staff",
        'hrjob_contract_type_op' => 'in',
        'hrjob_contract_type_value' => 'Employee,Volunteer,Contractor',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-tiedfunding.csv',
  ),

  //testCase with hrjob isTiedToFunding,level type and contract type filters with "notin" operator
  array(
    'CRM_HRReport_Form_Contact_HRDetail',
    array(
      'fields' => array(
        'sort_name',
        'email',
        'hrjob_position',
        'hrjob_title',
        'hrjob_contract_type',
        'hrjob_level_type',
        'hrjob_period_type',
        'hrjob_location',
      ),
      'filters' => array(
        'is_tied_to_funding_op' => 'eq',
        'is_tied_to_funding_value' => 1,
        'hrjob_level_type_op' => 'notin',
        'hrjob_level_type_value' => "Senior Staff",
        'hrjob_contract_type_op' => 'notin',
        'hrjob_contract_type_value' => 'Apprentice,Intern,Trustee',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-tiedfunding.csv',
  ),
);
