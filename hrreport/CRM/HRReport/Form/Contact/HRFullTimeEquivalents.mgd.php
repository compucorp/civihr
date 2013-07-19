<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
return array (
  array (
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
            'title' => 1,
            'level_type' => 1,
            'hours_type' => 1,
            'hours_unit' => 1,
            'fte' => 1,
          ),
          'group_bys' => array(
            'title' => 1,
            'level_type' => 1,
          ),
        )
      ),
    ),
  ),
);
