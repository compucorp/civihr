<?php

return [
  'Localise CiviCRM' => [
    'url' => 'civicrm/admin/setting/localization?reset=1',
    'permission' => 'access CiviCRM',
  ],

  'Job Contract' => [
    'permission' => 'access CiviCRM',
    'children' => [
      'Contract Types' => [
        'url' => 'civicrm/admin/options/hrjc_contract_type?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Normal Places of Work' => [
        'url' => 'civicrm/admin/options/hrjc_location?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Contract End Reasons' => [
        'url' => 'civicrm/admin/options/hrjc_contract_end_reason?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Contract Revision Reasons' => [
        'url' => 'civicrm/admin/options/hrjc_revision_change_reason?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Standard Full Time Hours' => [
        'url' => 'civicrm/standard_full_time_hours',
        'permission' => 'access CiviCRM',
      ],
      'Pay Scales' => [
        'url' => 'civicrm/pay_scale',
        'permission' => 'access CiviCRM',
      ],
      'Benefits' => [
        'url' => 'civicrm/admin/options/hrjc_benefit_name?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Deductions' => [
        'url' => 'civicrm/admin/options/hrjc_deduction_name?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Insurance Plan Types' => [
        'url' => 'civicrm/admin/options/hrjc_insurance_plantype?reset=1',
        'permission' => 'access CiviCRM',
      ],
    ],
  ],

  'Job Roles' => [
    'permission' => 'access CiviCRM',
    'children' => [
      'Locations' => [
        'url' => 'civicrm/admin/options/hrjc_location?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Regions' => [
        'url' => 'civicrm/admin/options/hrjc_region?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Departments' => [
        'url' => 'civicrm/admin/options/hrjc_department?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Levels' => [
        'url' => 'civicrm/admin/options/hrjc_level_type?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Cost Centres' => [
        'url' => 'civicrm/admin/options/cost_centres?reset=1',
        'permission' => 'access CiviCRM',
      ],
    ],
  ],

  'Other Staff Details' => [
    'permission' => 'access CiviCRM',
    'children' => [
      'Prefixes' => [
        'url' => 'civicrm/admin/options/individual_prefix?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Genders' => [
        'url' => 'civicrm/admin/options/gender?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Emergency Contact Relationships' => [
        'url' => 'civicrm/admin/options/relationship_with_employee_20150304120408?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Manager Types' => [
        'url' => 'civicrm/admin/reltype?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Career History' => [
        'url' => 'civicrm/admin/options/occupation_type_20130617111138?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Disability Types' => [
        'url' => 'civicrm/admin/options/type_20130502151940?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Qualifications – Skill Categories' => [
        'url' => 'civicrm/admin/options/category_of_skill_20130510015438?reset=1',
        'permission' => 'access CiviCRM',
      ],
      'Qualifications – Skill Levels' => [
        'url' => 'civicrm/admin/options/level_of_skill_20130510015934?reset=1',
        'permission' => 'access CiviCRM',
      ],
    ],
  ],

  'Tasks' => [
    'permission' => 'administer CiviCase',
    'children' => [
      'Tasks Settings' => [
        'url' => 'civicrm/tasksassignments/settings',
        'permission' => 'administer CiviCase',
      ],
      'Task and Document Types' => [
        'url' => 'civicrm/admin/options/activity_type?reset=1',
        'permission' => 'administer CiviCase',
      ],
      'Workflow Types' => [
        'url' => 'civicrm/a/#/caseType',
        'permission' => 'administer CiviCase',
      ],
    ],
  ],

  'Leave' => [
    'permission' => 'administer leave and absences',
    'children' => [
      'Leave Types' => [
        'url' => 'civicrm/admin/leaveandabsences/types?action=browse&reset=1',
        'permission' => 'administer leave and absences',
      ],
      'Leave Periods' => [
        'url' => 'civicrm/admin/leaveandabsences/periods?action=browse&reset=1',
        'permission' => 'administer leave and absences',
      ],
      'Public Holidays' => [
        'url' => 'civicrm/admin/leaveandabsences/public_holidays?action=browse&reset=1',
        'permission' => 'administer leave and absences',
      ],
      'Work Patterns' => [
        'url' => 'civicrm/admin/leaveandabsences/work_patterns?action=browse&reset=1',
        'permission' => 'administer leave and absences',
      ],
      'Leave Settings' => [
        'url' => 'civicrm/admin/leaveandabsences/general_settings',
        'permission' => 'administer leave and absences',
        'separator' => '1',
      ],
      'Sickness Reasons' => [
        'url' => 'civicrm/admin/options/hrleaveandabsences_sickness_reason?reset=1',
        'permission' => 'administer leave and absences',
      ],
      'TOIL to be Accrued' => [
        'url' => 'civicrm/admin/options/hrleaveandabsences_toil_amounts?reset=1',
        'permission' => 'administer leave and absences',
      ],
      'Work Pattern Change Reasons' => [
        'url' => 'civicrm/admin/options/hrleaveandabsences_work_pattern_change_reason?reset=1',
        'permission' => 'administer leave and absences',
      ],
      'Work Pattern Day Equivalents' => [
        'url' => 'civicrm/admin/options/hrleaveandabsences_leave_days_amounts?reset=1',
        'permission' => 'administer leave and absences',
        'separator' => '1',
      ],
      'Import Leave Requests' => [
        'url' => 'civicrm/admin/leaveandabsences/import',
        'permission' => 'administer leave and absences',
      ],
      'Calendar Feeds' => [
        'url' => 'civicrm/admin/leaveandabsences/calendar-feeds',
        'permission' => 'can administer calendar feeds',
      ],
    ],
  ],

  'Custom Fields' => [
    'url' => 'civicrm/admin/custom/group?reset=1',
    'permission' => 'administer CiviCRM',
  ],

  'Administration Console' => [
    'url' => 'civicrm/admin?reset=1',
    'permission' => 'access root menu items and configurations',
    'children' => [
      'System Status' => [
        'url' => 'civicrm/a/#/status',
        'permission' => 'access root menu items and configurations',
      ],
      'Configuration Checklist' => [
        'url' => 'civicrm/admin/configtask?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
    ],
  ],

  'Customize Data and Screens' => [
    'permission' => 'access root menu items and configurations',
    'children' => include 'CustomizeData.php',
  ],

  'Communications' => [
    'permission' => 'access root menu items and configurations,edit system workflow message templates,edit user-driven message templates',
    'operator' => 'OR',
    'children' => [
      'Organization Address and Contact Info' => [
        'url' => 'civicrm/admin/domain?action=update&reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'FROM Email Addresses' => [
        'url' => 'civicrm/admin/options/from_email_address?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'Message Templates' => [
        'url' => 'civicrm/admin/messageTemplates?reset=1',
        'permission' => 'access root menu items and configurations,edit system workflow message templates,edit user-driven message templates',
        'operator' => 'OR',
      ],
      'Schedule Reminders' => [
        'url' => 'civicrm/admin/scheduleReminders?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'Preferred Communication Methods' => [
        'url' => 'civicrm/admin/options/preferred_communication_method?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'Label Formats' => [
        'url' => 'civicrm/admin/labelFormats?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'Print Page (PDF) Formats' => [
        'url' => 'civicrm/admin/pdfFormats?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'Communication Style Options' => [
        'url' => 'civicrm/admin/options/communication_style?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'Email Greeting Formats' => [
        'url' => 'civicrm/admin/options/email_greeting?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'Postal Greeting Formats' => [
        'url' => 'civicrm/admin/options/postal_greeting?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'Addressee Formats' => [
        'url' => 'civicrm/admin/options/addressee?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
    ],
  ],

  'Localization' => [
    'permission' => 'access root menu items and configurations',
    'children' => [
      'Languages, Currency, Locations' => [
        'url' => 'civicrm/admin/setting/localization?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'Address Settings' => [
        'url' => 'civicrm/admin/setting/preferences/address?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'Date Formats' => [
        'url' => 'civicrm/admin/setting/date?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'Preferred Language Options' => [
        'url' => 'civicrm/admin/options/languages?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
    ],
  ],

  'Users and Permissions' => [
    'permission' => 'access root menu items and configurations',
    'children' => [
      'Permissions (Access Control)' => [
        'url' => 'civicrm/admin/access?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
      'Synchronize Users to Contacts' => [
        'url' => 'civicrm/admin/synchUser?reset=1',
        'permission' => 'access root menu items and configurations',
      ],
    ],
  ],

  'System Settings' => [
    'permission' => 'access root menu items and configurations',
    'children' => include 'SystemSettings.php',
  ],
];

