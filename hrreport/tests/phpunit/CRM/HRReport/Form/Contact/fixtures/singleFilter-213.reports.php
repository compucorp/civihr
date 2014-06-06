<?php

// A list of filters. In the sample data set, you could use any of these filters,
// and the results would include *only* contact #213 (job 6).
$filters = array(
  // HRJob
  array('hrjob_title_op'         => 'has', 'hrjob_title_value'         => 'Title-213-6'),
  array('hrjob_contract_type_op' => 'in',  'hrjob_contract_type_value' => 'Employee'),
  array('hrjob_level_type_op'    => 'has', 'hrjob_level_type_value'    => 'Junior Manager'),
  array('hrjob_period_type_op'   => 'in',  'hrjob_period_type_value'   => 'Temporary'),
  array('hrjob_department_op'    => 'ew',  'hrjob_department_value'    => 'Dept-213'),
  array('hrjob_location_op'      => 'in',  'hrjob_location_value'      => 'Home'),
  array('hrjob_position_op'      => 'has', 'hrjob_position_value'      => 'Position-213-6'),
  array(
    'hrjob_period_start_date_from'  => '20101001',
    'hrjob_period_start_date_to'    => '20101015',
  ),
  array(
    'hrjob_period_end_date_from'    => '20190120',
    'hrjob_period_end_date_to'      => '20210127',
  ),
  array('is_tied_to_funding_op'   => 'eq',  'is_tied_to_funding_value' => '0'),

  // HRJobHealth
  array('hrjob_health_plan_type_op' => 'in', 'hrjob_health_plan_type_value' => 'Family'),
  array('hrjob_life_insurance_plan_type_op' => 'in', 'hrjob_life_insurance_plan_type_value' => 'Family'),
  array('organization_name_op'  => 'has', 'organization_name_value'  => 'HealthOrg213'),
  array('display_name_op'  => 'has', 'display_name_value'  => 'LifeOrg213'),

  // HRJobHour
  array('hrjob_hours_type_op'   => 'in',    'hrjob_hours_type_value'   => 'part'),
  array('hrjob_hours_type_op'   => 'notin', 'hrjob_hours_type_value'   => 'full,casual'),
  array('hrjob_hours_amount_op' => 'gte',   'hrjob_hours_amount_value' => '40'),
  array('hrjob_hours_unit_op'   => 'in',    'hrjob_hours_unit_value'   => 'Month'),

  array('hrjob_hours_fte_op' => 'lt', 'hrjob_hours_fte_value' => '0.4'),

  // HRJobPay
  array('hrjob_pay_grade_op' => 'in',   'hrjob_pay_grade_value'  => 'unpaid'),
  array('hrjob_pay_amount_op' => 'gte', 'hrjob_pay_amount_value' => '100'),
  array('hrjob_pay_unit_op' => 'in',    'hrjob_pay_unit_value'   => 'Week'),

  // HRJobPension
  array('hrjob_is_enrolled_op' => 'in', 'hrjob_is_enrolled_value' => '0'),
);

/* ******** Boiler plate ******** */

// Construct a full test-case for each filter. Since all filters match
// the same contact and job (213-6), we can use the same csv file.
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
        'hrjob_health_provider_life_insurance',
        'hrjob_life_insurance_plan_type',
      ),
      'filters' => $filter,
    ),
    'fixtures/singleFilter-dataset.sql',
    'fixtures/singleFilter-213.csv',
  );
}
return $cases;
