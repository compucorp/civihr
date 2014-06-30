<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
$activityStatus = CRM_HRAbsence_BAO_HRAbsenceType::getActivityStatus();
return array (
  array (
    'name' => 'CiviHR Absence Report Template',
    'entity' => 'ReportTemplate',
    'params' =>
    array (
      'version' => 3,
      'label' => 'CiviHR Absence Report',
      'description' => 'HR Report showing absences at individual level. ',
      'class_name' => 'CRM_HRReport_Form_Activity_HRAbsence',
      'report_url' => 'civihr/absence',
      'grouping' => 'Absence',
      'component' => '',
    ),
  ),
  array (
    'name'   => 'CiviHR Absence Report',
    'entity' => 'ReportInstance',
    'params' =>
    array (
      'version' => 3,
      'title'   => 'CiviHR Absence Report',
      'description' => 'HR Report showing absences at individual level. ',
      'report_id'   => 'civihr/absence',
      'form_values' => serialize(
        array(
          'addToDashboard' => 1,
          'fields' => array(
            'id'  => 1,
            'contact_target' => 1,
            'activity_type_id' => 1,
            'duration' => 1,
            'absence_date' => 1,
            'status_id' => 1,
            'this.month' => 1,
          ),
          'status_id_op' => 'in',
          'status_id_value' => array(array_search('Approved', $activityStatus)),
        )
      ),
    ),
  ),
);
