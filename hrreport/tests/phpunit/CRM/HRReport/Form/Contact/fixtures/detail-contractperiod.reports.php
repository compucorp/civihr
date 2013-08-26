<?php
return array(
  //testCase with hrjob contract type and period type filters
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
        'contract_type_op' => 'in',
        'contract_type_value' => 'Apprentice',
        'period_type_op' => 'in',
        'period_type_value' => 'Temporary,Permanent',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-contractperiod.csv',
  ),
);