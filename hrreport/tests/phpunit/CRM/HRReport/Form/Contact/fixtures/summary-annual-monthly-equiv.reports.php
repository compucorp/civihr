<?php
return array(
  //testcase for CiviHR Annual and Monthly Cost Equivalents Report
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'level_type',
        'period_type',
        'location',
        'monthly_cost_eq',
        'annual_cost_eq',
      ),
      'group_bys' => array(
        'level_type',
        'location',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-annual-monthly-equiv.csv',
  )
);