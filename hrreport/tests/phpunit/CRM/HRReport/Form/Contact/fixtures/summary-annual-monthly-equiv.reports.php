<?php
return array(
  //testcase for CiviHR Annual and Monthly Cost Equivalents Report
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'hrjob_level_type',
        'hrjob_period_type',
        'hrjob_location',
        'monthly_cost_eq',
        'annual_cost_eq',
      ),
      'group_bys' => array(
        'hrjob_level_type',
        'hrjob_location',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-annual-monthly-equiv.csv',
  )
);