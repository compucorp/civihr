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
            'custom_14_1' => 1, //FIXME: custom name - this is sth we need to find better alternative for
            'state_province_id' => 1,
          ),
          'group_bys' => array(
            'state_province_id' => 1,
          ),
        )
      ),
    ),
  ),
);
