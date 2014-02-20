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
    'add' => '4.4',
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
    'add' => '4.4',
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
  'work_days_per_year' => array(
    'group_name' => 'hrjob',
    'group' => 'hrjob',
    'name' => 'work_days_per_year',
    'prefetch' => 0,
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 2,
      'maxlength' => 8,
    ),
    'default' => 50 * 5,
    'add' => '4.4',
    'title' => 'Standard work-days per year',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'The number of days worked by a typical full-time employee each year',
    'help_text' => null,
    'validate_callback' => 'CRM_HRJob_Estimator::validateEstimateConstant',
    'on_change' => array(
      array('CRM_HRJob_Estimator', 'onUpdateEstimateConstants')
    ),
  ),
  'work_hours_per_year' => array(
    'group_name' => 'hrjob',
    'group' => 'hrjob',
    'name' => 'work_hours_per_year',
    'prefetch' => 0,
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 2,
      'maxlength' => 8,
    ),
    'default' => 50 * 5 * 8,
    'add' => '4.4',
    'title' => 'Standard work-hours per year',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'The number of hours worked by a typical full-time employee each year',
    'help_text' => null,
    'validate_callback' => 'CRM_HRJob_Estimator::validateEstimateConstant',
    'on_change' => array(
      array('CRM_HRJob_Estimator', 'onUpdateEstimateConstants')
    ),
  ),
);
