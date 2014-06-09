<?php
return array(
  //testCase with hrjob_pension filters
  array(
    'CRM_HRReport_Form_Contact_HRDetail',
    array(
      'fields' => array(
        'sort_name',
        'email',
        'hrjob_position',
      ),
      'filters' => array(
        'hrjob_is_enrolled_op' => 'in',
        'hrjob_is_enrolled_value' => 1,
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/detail-pension.csv',
  )
);
