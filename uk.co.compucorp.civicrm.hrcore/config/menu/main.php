<?php

$result = civicrm_api3('OptionValue', 'getsingle', [
  'option_group_id' => 'custom_search',
  'name' => 'CRM_HRCore_Form_Search_StaffDirectory',
]);

$searchDirectoryURL = '';
if (!empty($result['value'])) {
  $searchDirectoryURL =
    "civicrm/contact/search/custom?csid={$result['value']}&force=1&reset=1&select_staff=current";
}


return [
  'Home' => 'civicrm/tasksassignments/dashboard#/tasks',

  'Staff' => [
    'icon' => 'crm-i fa-users',
    'children' => [
      'Add New Staff Member' => [
        'url' => 'civicrm/contact/add?reset=1&ct=Individual',
        'permission' => 'add contacts',
      ],
      'Staff Directory' => [
        'url' => $searchDirectoryURL,
        'separator' => 1
      ],
      'Record Communication' => [
        'children' => [
          'New Email' => 'civicrm/activity/email/add?atype=3&action=add&reset=1&context=standalone',
          'New Meeting' => 'civicrm/activity?reset=1&action=add&context=standalone',
        ]
      ],
    ],
  ],

  'Tasks' => [
    'icon' => 'crm-i fa-list-ul',
    'permission' => 'access Tasks and Assignments',
    'children' => [
      'Tasks' => 'civicrm/tasksassignments/dashboard#/tasks',
      'Documents' => 'civicrm/tasksassignments/dashboard#/documents',
      'Calendar' => 'civicrm/tasksassignments/dashboard#/calendar',
      'Key Dates' => 'civicrm/tasksassignments/dashboard#/key-dates',
    ],
  ],

  'Leave' => [
    'icon' => 'crm-i fa-briefcase',
    'permission' => 'access leave and absences',
    'children' => [
      'Leave Requests' => 'civicrm/leaveandabsences/dashboard#/requests',
      'Leave Calendar' => 'civicrm/leaveandabsences/dashboard#/calendar',
      'Leave Balances' => 'civicrm/leaveandabsences/dashboard#/leave-balances',
    ],
  ],

  'Reports' => [
    'url' => 'civicrm/reports',
    'icon' => 'fa fa-table',
    'permission' => 'access hrreports',
  ],

  'Configure' => [
    'icon' => 'crm-i fa-cog',
    'permission' => 'administer CiviCRM',
    'children' => include 'configure.php',
  ],

  'Help' => [
    'permission' => 'access CiviCRM',
    'icon' => 'crm-i fa-question-circle',
    'children' => [
      'User Guide' => [
        'url' => 'http://userguide.civihr.org/',
        'target' => '_blank',
        'permission' => 'access CiviCRM',
      ],
      'CiviHR website' => [
        'url' => 'https://www.civihr.org/',
        'target' => '_blank',
        'permission' => 'access root menu items and configurations',
      ],
      'Get support' => [
        'url' => 'https://www.civihr.org/support',
        'target' => '_blank',
        'permission' => 'access CiviCRM',
      ],
    ],
  ],

  'Developer' => [
    'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
    'operator' => 'AND',
    'icon' => 'crm-i fa-code',
    'children' => include 'developer.php',
  ],

  'Self Service Portal' => 'dashboard',
];
