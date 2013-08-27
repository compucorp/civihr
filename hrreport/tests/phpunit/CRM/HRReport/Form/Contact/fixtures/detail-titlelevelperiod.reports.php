<?php
return array(
  //testCase with hrjob title, level type and period type filters
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
        'hrjob_title_op' => 'has',
        'hrjob_title_value' => 'Manager2',
        'hrjob_level_type_op' => 'in',
        'hrjob_level_type_value' => "Senior Manager",
        'hrjob_period_type_op' => 'in',
        'hrjob_period_type_value' => 'Temporary',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-titlelevelperiod.csv',
  ),
);