<?php
return array(
  //testCase with hrjob contract type and period type filters
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
        'hrjob_contract_type_op' => 'in',
        'hrjob_contract_type_value' => 'Apprentice',
        'hrjob_period_type_op' => 'in',
        'hrjob_period_type_value' => 'Temporary,Permanent',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-contractperiod.csv',
  ),
);