<?php
return array (
  'work_months_per_year' => array(
    'group_name' => 'hrjob',
    'group' => 'hrjob',
    'name' => 'work_months_per_year',
    'prefetch' => 0,
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 2,
      'maxlength' => 8,
    ),
    'default' => 12,
    'add' => '4.5',
    'title' => 'Standard work-months per year',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'The number of months worked by a typical full-time employee each year',
    'help_text' => null,
    'validate_callback' => 'CRM_HRJob_Estimator::validateEstimateConstant',
    'on_change' => array(
      array('CRM_HRJob_Estimator', 'onUpdateEstimateConstants')
    ),
  ),
  'work_weeks_per_year' => array(
    'group_name' => 'hrjob',
    'group' => 'hrjob',
    'name' => 'work_weeks_per_year',
    'prefetch' => 0,
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 2,
      'maxlength' => 8,
    ),
    'default' => 50,
    'add' => '4.5',
    'title' => 'Standard work-weeks per year',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'The number of weeks worked by a typical full-time employee each year',
    'help_text' => null,
    'validate_callback' => 'CRM_HRJob_Estimator::validateEstimateConstant',
    'on_change' => array(
      array('CRM_HRJob_Estimator', 'onUpdateEstimateConstants')
    ),
  ),
  'work_days_per_week' => array(
    'group_name' => 'hrjob',
    'group' => 'hrjob',
    'name' => 'work_days_per_week',
    'prefetch' => 0,
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 2,
      'maxlength' => 8,
    ),
    'default' => 5,
    'add' => '4.5',
    'title' => 'Standard work-days per week',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'The number of days worked by a typical full-time employee each week',
    'help_text' => null,
    'validate_callback' => 'CRM_HRJob_Estimator::validateEstimateConstant',
    'on_change' => array(
      array('CRM_HRJob_Estimator', 'onUpdateEstimateConstants')
    ),
  ),
  'work_days_per_month' => array(
    'group_name' => 'hrjob',
    'group' => 'hrjob',
    'name' => 'work_days_per_month',
    'prefetch' => 0,
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 2,
      'maxlength' => 8,
    ),
    'default' => 22,
    'add' => '4.5',
    'title' => 'Standard work-days per month',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'The number of days worked by a typical full-time employee each month',
    'help_text' => null,
    'validate_callback' => 'CRM_HRJob_Estimator::validateEstimateConstant',
    'on_change' => array(
      array('CRM_HRJob_Estimator', 'onUpdateEstimateConstants')
    ),
  ),
  'work_hour_per_day' => array(
    'group_name' => 'hrjob',
    'group' => 'hrjob',
    'name' => 'work_hour_per_day',
    'prefetch' => 0,
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 2,
      'maxlength' => 8,
    ),
    'default' => 8,
    'add' => '4.5',
    'title' => 'Standard work-hour per day',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'The number of hours worked by a typical full-time employee each day',
    'help_text' => null,
    'validate_callback' => 'CRM_HRJob_Estimator::validateEstimateConstant',
    'on_change' => array(
      array('CRM_HRJob_Estimator', 'onUpdateEstimateConstants')
    ),
  )
);
