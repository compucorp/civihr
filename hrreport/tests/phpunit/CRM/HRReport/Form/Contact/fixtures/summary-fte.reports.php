<?php
return array(
  //testcase for CiviHR Full Time Equivalents Report
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'hrjob_level_type',
        'full_time_eq',
      ),
      'group_bys' => array(
        'hrjob_level_type',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-fte.csv',
  ),
);
