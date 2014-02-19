<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
return array (
  array (
    'name' => 'CiviHR Public Holiday Report Template',
    'entity' => 'ReportTemplate',
    'params' =>
    array (
      'version' => 3,
      'label' => 'CiviHR Public Holiday Report',
      'description' => 'HR Report showing absences at individual level. ',
      'class_name' => 'CRM_HRReport_Form_Activity_PublicHoliday',
      'report_url' => 'civihr/public',
      'grouping' => 'Absence',
      'component' => '',
    ),
  ),
  array (
    'name'   => 'CiviHR Public Holiday Report',
    'entity' => 'ReportInstance',
    'params' =>
    array (
      'version' => 3,
      'title'   => 'CiviHR Public Holiday Report',
      'description' => 'HR Report showing Public Holidays. ',
      'report_id'   => 'civihr/public',
      'form_values' => serialize(
        array(
          'fields' => array(
            'id'  => 1,
            'activity_date_time' => 1,
            'subject' => 1,
            'activity_date_time' => 'this.year',
          ),
        )
      ),
    ),
  ),
);
