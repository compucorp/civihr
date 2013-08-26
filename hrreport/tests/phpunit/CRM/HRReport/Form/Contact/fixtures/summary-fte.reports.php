<?php
return array(
  //testcase for CiviHR Full Time Equivalents Report
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'title',
        'level_type',
        'fte',
      ),
      'group_bys' => array(
        'title',
        'level_type',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-fte.csv',
  ),
);