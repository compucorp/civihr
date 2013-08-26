<?php
return array(
  //testCase with hrjob_pension filters
  array(
    'CRM_HRReport_Form_Contact_HRDetail',
    array(
      'fields' => array(
        'sort_name',
        'email',
        'position',
        'ee_contrib_pct',
        'er_contrib_pct',
      ),
      'filters' => array(
        'is_enrolled_op' => 'eq',
        'is_enrolled_value' => 1,
        'ee_contrib_pct_op' => 'lte',
        'ee_contrib_pct_value' => 200,
        'er_contrib_pct_op' => 'lte',
        'er_contrib_pct_value' => 100,
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/testCase3.csv',
  ),
);