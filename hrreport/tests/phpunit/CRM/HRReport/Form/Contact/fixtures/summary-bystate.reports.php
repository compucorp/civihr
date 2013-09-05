<?php
return array(
  //testcase for CiviHR Contact Summary Report
  array(
    'CRM_HRReport_Form_Contact_HRSummary',
    array(
      'fields' => array(
        'id',
        'state_province_id',
        'job_positions',
      ),
      'group_bys' => array(
        'state_province_id',
      ),
    ),
    'fixtures/dataset-detail.sql',
    'fixtures/summary-bystate.csv',
  )
);
