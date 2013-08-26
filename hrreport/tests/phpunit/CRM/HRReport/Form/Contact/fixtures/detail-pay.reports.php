<?php
// Return a list of report test cases
return array(
  //testCase with hrjob_pay filters
  array(
    'CRM_HRReport_Form_Contact_HRDetail',
    array(
      'fields' => array(
        'sort_name',
        'email',
        'position',
        'pay_grade',
        'pay_amount',
        'pay_unit',
      ),
      'filters' => array(
        'pay_grade_op' => 'in',
        'pay_grade_value' => 'paid',
        'pay_amount_op' => 'gt',
        'pay_amount_value' => 90,
        'pay_unit_op' => 'notin',
        'pay_unit_value' => 'Year,Week',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/testCase2.csv',
  )
);