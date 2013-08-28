<?php

// A list of filters. In the sample data set, you could use any of these filters,
// and the results would include *only* contact #211 (job 1).
$filters = array(
  // HRJob
  array('hrjob_title_op' => 'has', 'hrjob_title_value' => 'Title-211-1'),
  array('hrjob_contract_type_op' => 'in', 'hrjob_contract_type_value' => 'Contractor'),
  array('hrjob_level_type_op' => 'like', 'hrjob_level_type_value' => 'Senior Staff'),
  array('hrjob_period_type_op' => 'in', 'hrjob_period_type_value' => 'Permanent'),
  array('hrjob_department_op' => 'ew', 'hrjob_department_value' => 'Dept-211'),
  array('hrjob_location_op' => 'in', 'hrjob_location_value' => 'Headquarters'),
  array('hrjob_position_op' => 'has', 'hrjob_position_value' => 'Position-211-1'),
/* FIXME array(
    'hrjob_period_start_date_relative' => '0',
    'hrjob_period_start_date_from ' => '02/27/2012',
    'hrjob_period_start_date_from_display' => '02/27/2012',
    'hrjob_period_start_date_to' => '03/02/2012',
    'hrjob_period_start_date_to_display' => '03/02/2012'
  ),
*/
  // FIXME array('hrjob_period_end_date_op' => '', 'hrjob_period_end_date_value' => ''),
  array('is_tied_to_funding_op' => 'in', 'is_tied_to_funding_value' => '1'),

  // HRJobHealth
  array('hrjob_health_provider_op' => 'in', 'hrjob_health_provider_value' => 'Provider-1'),
  array('hrjob_health_plan_type_op' => 'in', 'hrjob_health_plan_type_value' => 'Individual'),

  // HRJobHour
  array('hrjob_hours_type_op' => 'in', 'hrjob_hours_type_value' => 'full'),
  array('hrjob_hours_type_op' => 'notin', 'hrjob_hours_type_value' => 'part,casual'),
  // FIXME array('hrjob_hours_amount_op' => 'lt', 'hrjob_hours_amount_value' => '20'),
  array('hrjob_hours_unit_op' => 'in', 'hrjob_hours_unit_value' => 'Week'),
  // FIXME array('hrjob_hours_fte_op' => 'gte', 'hrjob_hours_fte_value' => '0.9'),

  // HRJobPay
  array('hrjob_pay_grade_op' => 'in', 'hrjob_pay_grade_value' => 'paid'),
  array('hrjob_pay_amount_op' => 'lt', 'hrjob_pay_amount_value' => '100'),
  array('hrjob_pay_unit_op' => 'in', 'hrjob_pay_unit_value' => 'Day'),

  // HRJobPension
  array('hrjob_is_enrolled_op' => 'in', 'hrjob_is_enrolled_value' => '1'),

  // array('_op' => '', '_value' => ''),
);

/* ******** Boiler plate ******** */

// Construct a full test-case for each filter. Since all filters match
// the same contact and job (211-1), we can use the same csv file.
$cases = array();
foreach ($filters as $filter) {
  $cases[] = array(
    'CRM_HRReport_Form_Contact_HRDetail',
    array(
      'fields' => array(
        'id',
        'sort_name',
        'email',
        'hrjob_position',
        'hrjob_title',
        'hrjob_contract_type',
        'hrjob_level_type',
        'hrjob_period_type',
        'hrjob_location',
        'hrjob_health_provider',
        'hrjob_health_plan_type',
      ),
      'filters' => $filter,
    ),
    'fixtures/singleFilter-dataset.sql',
    'fixtures/singleFilter-211.csv',
  );
}
return $cases;