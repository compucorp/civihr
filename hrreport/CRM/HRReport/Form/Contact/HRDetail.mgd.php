<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
return array (
  array (
    'name' => 'CiviHR Contact Detail Report Template',
    'entity' => 'ReportTemplate',
    'params' =>
    array (
      'version' => 3,
      'label' => 'CiviHR Contact Detail Report',
      'description' => 'HR Report showing drilled down details at individual level. ',
      'class_name' => 'CRM_HRReport_Form_Contact_HRDetail',
      'report_url' => 'civihr/detail',
      'component' => '',
    ),
  ),
  array (
    'name'   => 'CiviHR Contact Detail Report',
    'entity' => 'ReportInstance',
    'params' =>
    array (
      'version' => 3,
      'title'   => 'CiviHR Contact Detail Report',
      'description' => 'HR Report showing drilled down details at individual level. ',
      'report_id'   => 'civihr/detail',
      'fields' => array(
        'id'  => 1,
        'sort_name' => 1,
        'email'     => 1,
        'custom_14' => 1,//FIXME: custom name - this is sth we need to find better alternative for
        'state_province_id' => 1,
        'country_id' => 1,
      ),
    ),
  ),
);