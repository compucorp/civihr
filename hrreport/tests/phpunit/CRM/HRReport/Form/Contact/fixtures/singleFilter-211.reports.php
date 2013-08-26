<?php

// A list of filters. In the sample data set, you could use any of these filters,
// and the results would include *only* contact #211 (job 1).
$filters = array(
  array('title_op' => 'like', 'title_value' => 'Title-211-1'),
  array('position_op' => 'like', 'position_value' => 'Position-211-1'),
  array('hours_type_op' => 'in', 'hours_type_value' => 'full'),
  array('hours_type_op' => 'in', 'hours_type_value' => 'full,casual'),
  array('hours_type_op' => 'notin', 'hours_type_value' => 'part,casual'),
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
        'position',
        'title',
        'contract_type',
        'level_type',
        'period_type',
        'location',
        'provider',
        'plan_type',
      ),
      'filters' => $filter,
    ),
    'fixtures/singleFilter-dataset.sql',
    "fixtures/singleFilter-211.csv",
  );
}
return $cases;