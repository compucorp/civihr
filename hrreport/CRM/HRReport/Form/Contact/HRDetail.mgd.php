<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
return array (
  array (
    'name' => 'CiviHR Job Detail Report Template',
    'entity' => 'ReportTemplate',
    'params' =>
    array (
      'version' => 3,
      'label' => 'CiviHR Job Detail Report',
      'description' => 'HR Report showing drilled down job details at individual level. ',
      'class_name' => 'CRM_HRReport_Form_Contact_HRDetail',
      'report_url' => 'civihr/detail',
      'component' => '',
    ),
  ),
  array (
    'name'   => 'CiviHR Job Detail Report',
    'entity' => 'ReportInstance',
    'params' =>
    array (
      'version' => 3,
      'title'   => 'CiviHR Job Detail Report',
      'description' => 'HR Report showing drilled down job details at individual level. ',
      'report_id'   => 'civihr/detail',
      'form_values' => serialize(
        array(
          'fields' => array(
            'id'  => 1,
            'sort_name' => 1,
            'email'     => 1,
            'position' => 1,
            'title' => 1,
            'manager' => 1,
            'level_type' => 1,
            'state_province_id' => 1,
            'country_id' => 1,
          ),
        )
      ),
    ),
  ),
);
