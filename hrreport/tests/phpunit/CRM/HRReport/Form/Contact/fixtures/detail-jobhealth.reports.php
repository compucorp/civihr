<?php
return array(
  //testCase with combination of hrjob health and hrjob filters
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
        'hrjob_health_provider',
        'hrjob_health_plan_type',
      ),
      'filters' => array(
        'hrjob_level_type_op' => 'in',
        'hrjob_level_type_value' => "Senior Manager,Junior Manager",
        'hrjob_contract_type_op' => 'in',
        'hrjob_contract_type_value' => 'Employee,Contractor',
        'hrjob_health_provider_op' => 'in',
        'hrjob_health_provider_value' => 'Unknown',
        'hrjob_health_plan_type_op' => 'in',
        'hrjob_health_plan_type_value' => 'Individual,Family',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-jobhealth.csv',
  ),
  //some variation to testCase8
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
        'hrjob_health_provider',
        'hrjob_health_plan_type',
      ),
      'filters' => array(
        'hrjob_level_type_op' => 'notin',
        'hrjob_level_type_value' => "Senior Staff,Junior Staff",
        'hrjob_contract_type_op' => 'notin',
        'hrjob_contract_type_value' => 'Intern,Trustee,Apprentice,Volunteer',
        'hrjob_health_provider_op' => 'in',
        'hrjob_health_provider_value' => 'Unknown',
        'hrjob_health_plan_type_op' => 'in',
        'hrjob_health_plan_type_value' => 'Individual,Family',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-jobhealth.csv',
  )
);