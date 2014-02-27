<?php
return array(
  array(
    'CRM_HRReport_Form_Activity_HRAbsenceCalendar',
    array(
      'fields' => array(
        'display_name',
        'activity_type_id',
      ),
      'filters' => array(
        'absence_date_relative' => null,
        'absence_date_from' => '20140101',
        'absence_date_to' => '20161231',
      ),
    ),
    'fixtures/dataset-absence.sql',
    'fixtures/absence-calendar.csv',
  ),
  array(
    'CRM_HRReport_Form_Activity_HRAbsenceCalendar',
    array(
      'fields' => array(
        'display_name',
        'activity_type_id',
      ),
      'filters' => array(
        'absence_date_relative' => null,
        'absence_date_from' => '20140101',
        'absence_date_to' => '20161231',
        'activity_type_id_op' => 'in',
        'activity_type_id_value' => array(63, 64, 65, 66, 67, 68, 69),
      ),
    ),
    'fixtures/dataset-absence.sql',
    'fixtures/absence-calendar.csv',
  ),
  array(
    'CRM_HRReport_Form_Activity_HRAbsenceCalendar',
    array(
      'fields' => array(
        'display_name',
        'activity_type_id',
      ),
      'filters' => array(
        'absence_date_relative' => null,
        'absence_date_from' => '20140101',
        'absence_date_to' => '20161231',
        'status_id_op' => 'in',
        'status_id_value' => array(1, 2),
      ),
    ),
    'fixtures/dataset-absence.sql',
    'fixtures/absence-calendar.csv',
  ),
);
