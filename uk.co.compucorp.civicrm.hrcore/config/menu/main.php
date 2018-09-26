<?php

return [
  'Home' => 'civicrm/tasksassignments/dashboard#/tasks',

  'Search' => [
    'icon' => 'crm-i fa-search',
    'children' => [
      'Find Contacts' => 'civicrm/contact/search?reset=1',
      'Advanced Search' => 'civicrm/contact/search/advanced?reset=1',
    ],
  ],

  'Staff' => [
    'icon' => 'crm-i fa-users',
    'children' => [
      'New Individual' => [
        'url' => 'civicrm/contact/add?reset=1&ct=Individual',
        'permission' => 'add contacts',
      ],
      'New Organization' => [
        'url' => 'civicrm/contact/add?reset=1&ct=Organization',
        'permission' => 'add contacts',
        'separator' => '1',
        'children' => [
          'New Health Insurance Provider' => [
            'url' => 'civicrm/contact/add?ct=Organization&cst=Health_Insurance_Provider&reset=1',
            'permission' => 'add contacts',
          ],
          'New Life Insurance Provider' => [
            'url' => 'civicrm/contact/add?ct=Organization&cst=Life_Insurance_Provider&reset=1',
            'permission' => 'add contacts',
          ],
          'New Pension Provider' => [
            'url' => 'civicrm/contact/add?ct=Organization&cst=Pension_Provider&reset=1',
            'permission' => 'add contacts',
          ],
        ],
      ],
      'New Email' => [
        'url' => 'civicrm/activity/email/add?atype=3&action=add&reset=1&context=standalone',
        'separator' => '1',
      ],
      'Import Contacts' => [
        'url' => 'civicrm/import/contact?reset=1',
        'permission' => 'import contacts',
      ],
      'Import / Export' => [
        'permission' => 'access HRJobs',
        'children' => [
          'Import Job Contracts' => [
            'url' => 'civicrm/job/import',
            'permission' => 'access HRJobs',
          ],
          'Import Job Roles' => [
            'url' => 'civicrm/jobroles/import',
            'separator' => true,
          ],
        ],
      ],
      'Import Custom Fields' => [
        'url' => 'civicrm/import/custom?reset=1',
        'permission' => 'access CiviCRM',
        'children' => CRM_HRCore_Menu_Config_CustomFields::getItems()
      ],
      'New Group' => [
        'url' => 'civicrm/group/add?reset=1',
        'permission' => 'edit groups',
      ],
      'Manage Groups' => [
        'url' => 'civicrm/group?reset=1',
        'permission' => 'access CiviCRM',
        'separator' => '1',
      ],
      'Find and Merge Duplicate Contacts' => [
        'url' => 'civicrm/contact/deduperules?reset=1',
        'permission' => 'administer dedupe rules,merge duplicate contacts',
        'operator' => 'OR',
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
