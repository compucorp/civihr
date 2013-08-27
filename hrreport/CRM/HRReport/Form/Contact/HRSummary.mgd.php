<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
return array (
  array (
    'name' => 'CiviHR Contact Summary Report Template',
    'entity' => 'ReportTemplate',
    'params' =>
    array (
      'version' => 3,
      'label' => 'CiviHR Contact Summary Report Template',
      'description' => 'HR Report with stats on people and job positions.',
      'class_name' => 'CRM_HRReport_Form_Contact_HRSummary',
      'report_url' => 'civihr/summary',
      'component' => '',
    ),
  ),
  array (
    'name'   => 'CiviHR Contact Summary Report',
    'entity' => 'ReportInstance',
    'params' =>
    array (
      'version' => 3,
      'title'   => 'CiviHR Contact Summary Report',
      'description' => 'HR Report with stats on people and job positions.',
      'report_id'   => 'civihr/summary',
      'form_values' => serialize(
        array(
          'fields' => array(
            'id'         => 1,
            'job_positions' => 1,
            'state_province_id' => 1,
          ),
          'group_bys' => array(
            'state_province_id' => 1,
          ),
        )
      ),
    ),
  ),
  array(
    'name'   => 'CiviHR Annual and Monthly Cost Equivalents Report ',
    'entity' => 'ReportInstance',
    'params' =>
    array (
      'version' => 3,
      'title'   => 'CiviHR Annual and Monthly Cost Equivalents Report ',
      'description' => 'HR Report with stats on Annual and Monthly Cost Equivalents',
      'report_id'   => 'civihr/summary',
      'form_values' => serialize(
        array(
          'fields' => array(
            'hrjob_level_type'  => 1,
            'hrjob_period_type' => 1,
            'hrjob_location'    => 1,
            'monthly_cost_eq'   => 1,
            'annual_cost_eq'    => 1,
          ),
          'group_bys' => array(
            'hrjob_level_type' => 1,
            'hrjob_location'   => 1,
          ),
        )
      ),
    ),
  ),
  array(
    'name'   => 'CiviHR FTE Report ',
    'entity' => 'ReportInstance',
    'params' =>
    array (
      'version' => 3,
      'title'   => 'CiviHR Full Time Equivalents Report ',
      'description' => 'HR Report with stats on Full Time Equivalents',
      'report_id'   => 'civihr/summary',
      'form_values' => serialize(
        array(
          'fields' => array(
            'hrjob_level_type' => 1,
            'full_time_eq'     => 1,
          ),
          'group_bys' => array(
            'hrjob_level_type' => 1,
          ),
        )
      ),
    ),
  ),
);
