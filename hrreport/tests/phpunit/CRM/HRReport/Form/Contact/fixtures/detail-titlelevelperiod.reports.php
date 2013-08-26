<?php
return array(
  //testCase with hrjob title, level type and period type filters
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
        'title_op' => 'like',
        'title_value' => 'Manager2',
        'level_type_op' => 'in',
        'level_type_value' => "Senior Manager",
        'period_type_op' => 'in',
        'period_type_value' => 'Temporary',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-titlelevelperiod.csv',
  ),
);