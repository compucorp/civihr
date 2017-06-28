<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
$isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrabsence', 'is_active', 'full_name');
$absenceOptionValue = civicrm_api3('OptionValue', 'get', [
  'name' => 'Absence',
  'option_group_id' => 'activity_type',
  'sequential' => 1
]);

$activityTypeValue = $absenceOptionValue['values'][0]['value'];

if ($isEnabled) {
  $activityStatus = CRM_HRAbsence_BAO_HRAbsenceType::getActivityStatus();
  $approvedStatus = array_search('Approved', $activityStatus);
}
else {
  $activityStatus = CRM_Core_PseudoConstant::activityStatus();
  $approvedStatus = array_search('Completed', $activityStatus);
}

return [
  [
    'name' => 'CiviHR Absence Dates Report Template',
    'entity' => 'ReportTemplate',
    'params' =>
    [
      'version' => 3,
      'label' => 'CiviHR Absence Dates Report',
      'description' => 'HR Report showing absence dates for each individual. ',
      'class_name' => 'CRM_HRReport_Form_Activity_HRAbsenceDates',
      'report_url' => 'civihr/absencedates',
      'grouping' => 'Absence',
      'component' => '',
    ],
  ],
  [
    'name'   => 'CiviHR Absence Dates Report',
    'entity' => 'ReportInstance',
    'params' =>
    [
      'version' => 3,
      'title'   => 'CiviHR Absence Dates Report',
      'description' => 'HR Report showing absence dates for each individual. ',
      'report_id'   => 'civihr/absencedates',
      'form_values' => serialize(
        [
          'addToDashboard' => 1,
          'fields' => [
            'id'  => 1,
            'contact_target' => 1,
            'activity_type_id' => 1,
            'duration' => 1,
            'absence_date' => 1,
            'status_id' => 1,
            'this.month' => 1,
          ],
          'status_id_op' => 'in',
          'status_id_value' => [$approvedStatus],
          'activity_type_id_op' => 'in',
          'activity_type_id_value' => [$activityTypeValue],
        ]
      ),
    ],
  ],
];
