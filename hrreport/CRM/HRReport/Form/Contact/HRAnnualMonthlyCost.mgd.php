<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
return array (
  array (
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
            'level_type' => 1,
            'period_type' => 1,
            'location' => 1,
            'monthly_cost_eq' => 1,
            'annual_cost_eq' => 1,
          ),
          'group_bys' => array(
            'level_type' => 1,
            'location' => 1,
          ),
        )
      ),
    ),
  ),
);
