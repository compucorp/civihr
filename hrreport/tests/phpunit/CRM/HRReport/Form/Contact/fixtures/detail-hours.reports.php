<?php
return array(
  //testCase with hrjob hours filters
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
        'hrjob_hours_type',
        'hrjob_hours_unit',
      ),
      'filters' => array(
        'hrjob_hours_type_op' => 'in',
        'hrjob_hours_type_value' => 'part,full',
        'hrjob_hours_amount_op' => 'gte',
        'hrjob_hours_amount_value' => 15,
        'hrjob_hours_unit_op' => 'notin',
        'hrjob_hours_unit_value' => 'Month,Year',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-hours.csv',
  ),

  //some variation to testCase9
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
        'hrjob_hours_type',
        'hrjob_hours_unit',
      ),
      'filters' => array(
        'hrjob_hours_type_op' => 'notin',
        'hrjob_hours_type_value' => 'casual',
        'hrjob_hours_amount_op' => 'gte',
        'hrjob_hours_amount_value' => 15,
        'hrjob_hours_unit_op' => 'in',
        'hrjob_hours_unit_value' => 'Day,Week',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-hours.csv',
  )
);