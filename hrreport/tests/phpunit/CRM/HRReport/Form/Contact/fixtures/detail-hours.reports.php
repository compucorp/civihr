<?php
return array(
  //testCase with hrjob hours filters
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
        'hours_type',
        'hours_unit',
      ),
      'filters' => array(
        'hours_type_op' => 'in',
        'hours_type_value' => 'part,full',
        'hours_amount_op' => 'gte',
        'hours_amount_value' => 15,
        'hours_unit_op' => 'notin',
        'hours_unit_value' => 'Month,Year',
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
        'position',
        'title',
        'contract_type',
        'level_type',
        'period_type',
        'location',
        'hours_type',
        'hours_unit',
      ),
      'filters' => array(
        'hours_type_op' => 'notin',
        'hours_type_value' => 'casual',
        'hours_amount_op' => 'gte',
        'hours_amount_value' => 15,
        'hours_unit_op' => 'in',
        'hours_unit_value' => 'Day,Week',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-hours.csv',
  )
);