<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
return array (
  array (
    'name' => 'CiviHR Absence Calendar Report Template',
    'entity' => 'ReportTemplate',
    'params' =>
    array (
      'version' => 3,
      'label' => 'CiviHR Absence Calendar Report',
      'description' => 'HR Report showing absences at individual level on monthly chart.',
      'class_name' => 'CRM_HRReport_Form_Activity_HRAbsenceCalendar',
      'report_url' => 'civihr/absence/calendar',
      'grouping' => 'Absence',
      'component' => '',
    ),
  ),
  array (
    'name'   => 'CiviHR Absence Calendar Report',
    'entity' => 'ReportInstance',
    'params' =>
    array (
      'version' => 3,
      'title'   => 'CiviHR Absence Calendar Report',
      'description' => 'HR Report showing absences at individual level on monthly chart.',
      'report_id'   => 'civihr/absence/calendar',
      'form_values' => serialize(
        array(
          'absence_duration_relative' => 'this.month',
          'status_id' => array(2),
        )
      ),
    ),
  ),
);
