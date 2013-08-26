<?php
return array(
  //testCase with hrjob isTiedToFunding,level type and contract type filters with "in" operator
  array(
    'CRM_HRReport_Form_Contact_HRDetail',
    array(
      'fields' => array(
        'sort_name',
        'email',
        'position',
        'title',
        'contract_type',
        'level_type',
        'period_type',
        'location',
      ),
      'filters' => array(
        'is_tied_to_funding_op' => 'eq',
        'is_tied_to_funding_value' => 1,
        'level_type_op' => 'in',
        'level_type_value' => "Senior Manager,Junior Manager,Junior Staff",
        'contract_type_op' => 'in',
        'contract_type_value' => 'Employee,Volunteer,Contractor',
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
        'position',
        'title',
        'contract_type',
        'level_type',
        'period_type',
        'location',
      ),
      'filters' => array(
        'is_tied_to_funding_op' => 'eq',
        'is_tied_to_funding_value' => 1,
        'level_type_op' => 'notin',
        'level_type_value' => "Senior Staff",
        'contract_type_op' => 'notin',
        'contract_type_value' => 'Apprentice,Intern,Trustee',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-tiedfunding.csv',
  ),
);