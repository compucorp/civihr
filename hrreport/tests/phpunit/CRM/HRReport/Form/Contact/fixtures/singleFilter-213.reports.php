<?php

// A list of filters. In the sample data set, you could use any of these filters,
// and the results would include *only* contact #213 (job 6).
$filters = array(
  array('hrjob_title_op' => 'has', 'hrjob_title_value' => 'Title-213-6'),
  array('hrjob_position_op' => 'has', 'hrjob_position_value' => 'Position-213-6'),
  array('hrjob_hours_type_op' => 'in', 'hrjob_hours_type_value' => 'part'),
  array('hrjob_hours_type_op' => 'notin', 'hrjob_hours_type_value' => 'full,casual'),
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
      ),
      'filters' => $filter,
    ),
    'fixtures/singleFilter-dataset.sql',
    'fixtures/singleFilter-213.csv',
  );
}
return $cases;