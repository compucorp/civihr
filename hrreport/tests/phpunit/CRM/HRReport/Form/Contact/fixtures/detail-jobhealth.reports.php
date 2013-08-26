<?php
return array(
  //testCase with combination of hrjob health and hrjob filters
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
        'provider',
        'plan_type',
      ),
      'filters' => array(
        'level_type_op' => 'in',
        'level_type_value' => "Senior Manager,Junior Manager",
        'contract_type_op' => 'in',
        'contract_type_value' => 'Employee,Contractor',
        'provider_op' => 'in',
        'provider_value' => 'Unknown',
        'plan_type_op' => 'in',
        'plan_type_value' => 'Individual,Family',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/testCase8.csv',
  ),
  //some variation to testCase8
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
        'provider',
        'plan_type',
      ),
      'filters' => array(
        'level_type_op' => 'notin',
        'level_type_value' => "Senior Staff,Junior Staff",
        'contract_type_op' => 'notin',
        'contract_type_value' => 'Intern,Trustee,Apprentice,Volunteer',
        'provider_op' => 'in',
        'provider_value' => 'Unknown',
        'plan_type_op' => 'in',
        'plan_type_value' => 'Individual,Family',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/testCase8.csv',
  )
);