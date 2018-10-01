<?php

class CRM_HRCore_Menu_Main {

  /**
   * Return the main menu Items.
   *
   * @return array
   */
  public static function getItems() {
    $menuItems = [
      [
        'attributes' =>
          [
            'label' => 'Search',
            'icon' => 'crm-i fa-search',
            'operator' => '',
          ],
        'child' =>
          [
            [
              'attributes' =>
                [
                  'label' => 'Find Contacts',
                  'url' => 'civicrm/contact/search?reset=1',
                  'operator' => '',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Advanced Search',
                  'url' => 'civicrm/contact/search/advanced?reset=1',
                  'operator' => '',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Find Contributions',
                  'url' => 'civicrm/contribute/search?reset=1',
                  'permission' => 'access CiviContribute',
                  'operator' => '',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Find Mailings',
                  'url' => 'civicrm/mailing?reset=1',
                  'permission' => 'access CiviMail',
                  'operator' => '',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Find Memberships',
                  'url' => 'civicrm/member/search?reset=1',
                  'permission' => 'access CiviMember',
                  'operator' => '',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Find Participants',
                  'url' => 'civicrm/event/search?reset=1',
                  'permission' => 'access CiviEvent',
                  'operator' => '',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Find Pledges',
                  'url' => 'civicrm/pledge/search?reset=1',
                  'permission' => 'access CiviPledge',
                  'operator' => '',
                ],
            ],
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Staff',
            'icon' => 'crm-i fa-users',
            'operator' => '',
          ],
        'child' =>
          [
            [
              'attributes' =>
                [
                  'label' => 'New Individual',
                  'url' => 'civicrm/contact/add?reset=1&ct=Individual',
                  'permission' => 'add contacts',
                  'operator' => '',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'New Household',
                  'url' => 'civicrm/contact/add?reset=1&ct=Household',
                  'permission' => 'add contacts',
                  'operator' => '',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'New Organization',
                  'url' => 'civicrm/contact/add?reset=1&ct=Organization',
                  'permission' => 'add contacts',
                  'operator' => '',
                  'separator' => '1',
                ],
              'child' =>
                [
                  [
                  'attributes' =>
                    [
                      'label' => 'New Health Insurance Provider',
                      'url' => 'civicrm/contact/add?ct=Organization&cst=Health_Insurance_Provider&reset=1',
                      'permission' => 'add contacts',
                      'operator' => NULL,
                    ],
                  ],
                  [
                  'attributes' =>
                    [
                      'label' => 'New Life Insurance Provider',
                      'url' => 'civicrm/contact/add?ct=Organization&cst=Life_Insurance_Provider&reset=1',
                      'permission' => 'add contacts',
                      'operator' => NULL,
                    ],
                  ],
                  [
                  'attributes' =>
                    [
                      'label' => 'New Pension Provider',
                      'url' => 'civicrm/contact/add?ct=Organization&cst=Pension_Provider&reset=1',
                      'permission' => 'add contacts',
                      'operator' => NULL,
                    ],
                  ],
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'New Email',
                  'url' => 'civicrm/activity/email/add?atype=3&action=add&reset=1&context=standalone',
                  'operator' => '',
                  'separator' => '1',
                ],
              ],
            [
              'attributes' =>
                [
                  'label' => 'Import Contacts',
                  'url' => 'civicrm/import/contact?reset=1',
                  'permission' => 'import contacts',
                  'operator' => '',
                ],
              ],
            [
              'attributes' =>
                [
                  'label' => 'Import / Export',
                  'permission' => 'access HRJobs',
                  'operator' => NULL,
                ],
              'child' =>
                [
                  [
                    'attributes' =>
                      [
                        'label' => 'Import Job Contracts',
                        'url' => 'civicrm/job/import',
                        'permission' => 'access HRJobs',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Import Job Roles',
                        'url' => 'civicrm/jobroles/import',
                        'operator' => NULL,
                        'separator' => true,
                      ],
                  ],
                ],
              ],
            [
              'attributes' =>
                [
                  'label' => 'Import Custom Fields',
                  'url' => 'civicrm/import/custom?reset=1',
                  'permission' => 'access CiviCRM',
                  'operator' => NULL,
                ],
              'child' => CRM_HRCore_Menu_CustomFields::getItems(),
              ],
            [
              'attributes' =>
                [
                  'label' => 'New Group',
                  'url' => 'civicrm/group/add?reset=1',
                  'permission' => 'edit groups',
                  'operator' => '',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Manage Groups',
                  'url' => 'civicrm/group?reset=1',
                  'permission' => 'access CiviCRM',
                  'operator' => '',
                  'separator' => '1',
                ],
              ],
            [
              'attributes' =>
                [
                  'label' => 'Find and Merge Duplicate Contacts',
                  'url' => 'civicrm/contact/deduperules?reset=1',
                  'permission' => 'administer dedupe rules,merge duplicate contacts',
                  'operator' => 'OR',
                ],
              ],
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Tasks',
            'url' => '',
            'icon' => 'crm-i fa-list-ul',
            'permission' => 'access Tasks and Assignments',
            'operator' => NULL,
          ],
        'child' =>
          [
            [
              'attributes' =>
                [
                  'label' => 'Tasks',
                  'url' => 'civicrm/tasksassignments/dashboard#/tasks',
                  'operator' => NULL,
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Documents',
                  'url' => 'civicrm/tasksassignments/dashboard#/documents',
                  'operator' => NULL,
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Calendar',
                  'url' => 'civicrm/tasksassignments/dashboard#/calendar',
                  'operator' => NULL,
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Key Dates',
                  'url' => 'civicrm/tasksassignments/dashboard#/key-dates',
                  'operator' => NULL,
                ],
            ],
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Leave',
            'icon' => 'crm-i fa-briefcase',
            'permission' => 'access leave and absences',
            'operator' => NULL,
          ],
        'child' =>
          [
            [
              'attributes' =>
                [
                  'label' => 'Leave Requests',
                  'url' => 'civicrm/leaveandabsences/dashboard#/requests',
                  'operator' => NULL,
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Leave Calendar',
                  'url' => 'civicrm/leaveandabsences/dashboard#/calendar',
                  'operator' => NULL,
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Leave Balances',
                  'url' => 'civicrm/leaveandabsences/dashboard#/leave-balances',
                  'operator' => NULL,
                ],
            ],
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Reports',
            'url' => 'civicrm/reports',
            'icon' => 'fa fa-table',
            'permission' => 'access hrreports',
            'operator' => NULL,
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Configure',
            'icon' => 'crm-i fa-cog',
            'permission' => 'administer CiviCRM',
            'operator' => '',
          ],
        'child' =>
          [
            [
              'attributes' =>
                [
                  'label' => 'Localise CiviCRM',
                  'url' => 'civicrm/admin/setting/localization?reset=1',
                  'permission' => 'access CiviCRM',
                  'operator' => NULL,
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Job Contract',
                  'permission' => 'access CiviCRM',
                  'operator' => NULL,
                ],
              'child' =>
                [
                  [
                    'attributes' =>
                      [
                        'label' => 'Contract Types',
                        'url' => 'civicrm/admin/options/hrjc_contract_type?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Normal Places of Work',
                        'url' => 'civicrm/admin/options/hrjc_location?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Contract End Reasons',
                        'url' => 'civicrm/admin/options/hrjc_contract_end_reason?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Contract Revision Reasons',
                        'url' => 'civicrm/admin/options/hrjc_revision_change_reason?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Standard Full Time Hours',
                        'url' => 'civicrm/standard_full_time_hours',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Pay Scales',
                        'url' => 'civicrm/pay_scale',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Benefits',
                        'url' => 'civicrm/admin/options/hrjc_benefit_name?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Deductions',
                        'url' => 'civicrm/admin/options/hrjc_deduction_name?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Insurance Plan Types',
                        'url' => 'civicrm/admin/options/hrjc_insurance_plantype?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Other Staff Details',
                  'permission' => 'access CiviCRM',
                  'operator' => NULL,
                ],
              'child' =>
                [
                  [
                    'attributes' =>
                      [
                        'label' => 'Prefixes',
                        'url' => 'civicrm/admin/options/individual_prefix?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Genders',
                        'url' => 'civicrm/admin/options/gender?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Emergency Contact Relationships',
                        'url' => 'civicrm/admin/options/relationship_with_employee_20150304120408?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Manager Types',
                        'url' => 'civicrm/admin/reltype?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Career History',
                        'url' => 'civicrm/admin/options/occupation_type_20130617111138?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Disability Types',
                        'url' => 'civicrm/admin/options/type_20130502151940?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Qualifications – Skill Categories',
                        'url' => 'civicrm/admin/options/category_of_skill_20130510015438?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Qualifications – Skill Levels',
                        'url' => 'civicrm/admin/options/level_of_skill_20130510015934?reset=1',
                        'permission' => 'access CiviCRM',
                        'operator' => NULL,
                      ],
                  ],
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Tasks',
                  'permission' => 'administer CiviCase',
                  'operator' => NULL,
                ],
              'child' =>
                [
                  [
                    'attributes' =>
                      [
                        'label' => 'Tasks Settings',
                        'url' => 'civicrm/tasksassignments/settings',
                        'permission' => 'administer CiviCase',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Task and Document Types',
                        'url' => 'civicrm/admin/options/activity_type?reset=1',
                        'permission' => 'administer CiviCase',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Workflow Types',
                        'url' => 'civicrm/a/#/caseType',
                        'permission' => 'administer CiviCase',
                        'operator' => NULL,
                      ],
                  ],
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Leave',
                  'permission' => 'administer leave and absences',
                  'operator' => NULL,
                ],
              'child' =>
                [
                  [
                    'attributes' =>
                      [
                        'label' => 'Leave Types',
                        'url' => 'civicrm/admin/leaveandabsences/types?action=browse&reset=1',
                        'permission' => 'administer leave and absences',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Leave Periods',
                        'url' => 'civicrm/admin/leaveandabsences/periods?action=browse&reset=1',
                        'permission' => 'administer leave and absences',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Public Holidays',
                        'url' => 'civicrm/admin/leaveandabsences/public_holidays?action=browse&reset=1',
                        'permission' => 'administer leave and absences',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Work Patterns',
                        'url' => 'civicrm/admin/leaveandabsences/work_patterns?action=browse&reset=1',
                        'permission' => 'administer leave and absences',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Leave Settings',
                        'url' => 'civicrm/admin/leaveandabsences/general_settings',
                        'permission' => 'administer leave and absences',
                        'operator' => NULL,
                        'separator' => '1',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Sickness Reasons',
                        'url' => 'civicrm/admin/options/hrleaveandabsences_sickness_reason?reset=1',
                        'permission' => 'administer leave and absences',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'TOIL to be Accrued',
                        'url' => 'civicrm/admin/options/hrleaveandabsences_toil_amounts?reset=1',
                        'permission' => 'administer leave and absences',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Work Pattern Change Reasons',
                        'url' => 'civicrm/admin/options/hrleaveandabsences_work_pattern_change_reason?reset=1',
                        'permission' => 'administer leave and absences',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Work Pattern Day Equivalents',
                        'url' => 'civicrm/admin/options/hrleaveandabsences_leave_days_amounts?reset=1',
                        'permission' => 'administer leave and absences',
                        'operator' => NULL,
                        'separator' => '1',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Import Leave Requests',
                        'url' => 'civicrm/admin/leaveandabsences/import',
                        'permission' => 'administer leave and absences',
                        'operator' => NULL,
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Calendar Feeds',
                        'url' => 'civicrm/admin/leaveandabsences/calendar-feeds',
                        'permission' => 'can administer calendar feeds',
                        'operator' => NULL,
                      ],
                  ],
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Custom Fields',
                  'url' => 'civicrm/admin/custom/group?reset=1',
                  'permission' => 'administer CiviCRM',
                  'operator' => NULL,
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Administration Console',
                  'url' => 'civicrm/admin?reset=1',
                  'permission' => 'access root menu items and configurations',
                  'operator' => '',
                ],
              'child' =>
                [
                  [
                    'attributes' =>
                      [
                        'label' => 'System Status',
                        'url' => 'civicrm/a/#/status',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Configuration Checklist',
                        'url' => 'civicrm/admin/configtask?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Customize Data and Screens',
                  'permission' => 'access root menu items and configurations',
                  'operator' => '',
                ],
              'child' =>
                [
                  [
                    'attributes' =>
                      [
                        'label' => 'Custom Fields',
                        'url' => 'civicrm/admin/custom/group?reset=1',
                        'permission' => 'administer CiviCRM',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Profiles',
                        'url' => 'civicrm/admin/uf/group?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Tags (Categories)',
                        'url' => 'civicrm/tag?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Activity Types',
                        'url' => 'civicrm/admin/options/activity_type?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Relationship Types',
                        'url' => 'civicrm/admin/reltype?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Contact Types',
                        'url' => 'civicrm/admin/options/subtype?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Dropdown Options',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                    'child' =>
                      [
                        [
                          'attributes' =>
                            [
                              'label' => 'Gender Options',
                              'url' => 'civicrm/admin/options/gender?reset=1',
                              'permission' => 'access root menu items and configurations',
                              'operator' => '',
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Individual Prefixes (Ms, Mr...)',
                              'url' => 'civicrm/admin/options/individual_prefix?reset=1',
                              'permission' => 'access root menu items and configurations',
                              'operator' => '',
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Individual Suffixes (Jr, Sr...)',
                              'url' => 'civicrm/admin/options/individual_suffix?reset=1',
                              'permission' => 'access root menu items and configurations',
                              'operator' => '',
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Instant Messenger Services',
                              'url' => 'civicrm/admin/options/instant_messenger_service?reset=1',
                              'permission' => 'access root menu items and configurations',
                              'operator' => '',
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Location Types (Home, Work...)',
                              'url' => 'civicrm/admin/locationType?reset=1',
                              'permission' => 'access root menu items and configurations',
                              'operator' => '',
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Mobile Phone Providers',
                              'url' => 'civicrm/admin/options/mobile_provider?reset=1',
                              'permission' => 'access root menu items and configurations',
                              'operator' => '',
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Phone Types',
                              'url' => 'civicrm/admin/options/phone_type?reset=1',
                              'permission' => 'access root menu items and configurations',
                              'operator' => '',
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Website Types',
                              'url' => 'civicrm/admin/options/website_type?reset=1',
                              'permission' => 'access root menu items and configurations',
                              'operator' => '',
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Hours Types',
                              'url' => 'civicrm/hour/editoption',
                              'permission' => 'administer CiviCRM',
                              'operator' => NULL,
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Job Contract Pay Scale',
                              'url' => 'civicrm/pay_scale',
                              'permission' => 'administer CiviCRM',
                              'operator' => NULL,
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Job Contract Hours/Location',
                              'url' => 'civicrm/hours_location',
                              'permission' => 'administer CiviCRM',
                              'operator' => NULL,
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Contract Type',
                              'url' => 'civicrm/admin/options/hrjc_contract_type?reset=1',
                              'permission' => 'administer CiviCRM',
                              'operator' => NULL,
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Normal place of work',
                              'url' => 'civicrm/admin/options/hrjc_location?reset=1',
                              'permission' => 'administer CiviCRM',
                              'operator' => NULL,
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Pay cycle',
                              'url' => 'civicrm/admin/options/hrjc_pay_cycle?reset=1',
                              'permission' => 'administer CiviCRM',
                              'operator' => NULL,
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Benefits',
                              'url' => 'civicrm/admin/options/hrjc_benefit_name?reset=1',
                              'permission' => 'administer CiviCRM',
                              'operator' => NULL,
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Benefit type',
                              'url' => 'civicrm/admin/options/hrjc_benefit_type?reset=1',
                              'permission' => 'administer CiviCRM',
                              'operator' => NULL,
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Deductions',
                              'url' => 'civicrm/admin/options/hrjc_deduction_name?reset=1',
                              'permission' => 'administer CiviCRM',
                              'operator' => NULL,
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Deduction type',
                              'url' => 'civicrm/admin/options/hrjc_deduction_type?reset=1',
                              'permission' => 'administer CiviCRM',
                              'operator' => NULL,
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Reason for change',
                              'url' => 'civicrm/admin/options/hrjc_revision_change_reason?reset=1',
                              'permission' => 'administer CiviCRM',
                              'operator' => NULL,
                            ],
                        ],
                        [
                          'attributes' =>
                            [
                              'label' => 'Reason for Job Contract end',
                              'url' => 'civicrm/admin/options/hrjc_contract_end_reason?reset=1',
                              'permission' => 'administer CiviCRM',
                              'operator' => NULL,
                            ],
                        ],
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Display Preferences',
                        'url' => 'civicrm/admin/setting/preferences/display?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Search Preferences',
                        'url' => 'civicrm/admin/setting/search?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Date Preferences',
                        'url' => 'civicrm/admin/setting/preferences/date?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Navigation Menu',
                        'url' => 'civicrm/admin/menu?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Word Replacements',
                        'url' => 'civicrm/admin/options/wordreplacements?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Manage Custom Searches',
                        'url' => 'civicrm/admin/options/custom_search?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Communications',
                  'permission' => 'access root menu items and configurations',
                  'operator' => '',
                ],
              'child' =>
                [
                  [
                    'attributes' =>
                      [
                        'label' => 'Organization Address and Contact Info',
                        'url' => 'civicrm/admin/domain?action=update&reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'FROM Email Addresses',
                        'url' => 'civicrm/admin/options/from_email_address?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Message Templates',
                        'url' => 'civicrm/admin/messageTemplates?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Schedule Reminders',
                        'url' => 'civicrm/admin/scheduleReminders?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Preferred Communication Methods',
                        'url' => 'civicrm/admin/options/preferred_communication_method?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Label Formats',
                        'url' => 'civicrm/admin/labelFormats?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Print Page (PDF) Formats',
                        'url' => 'civicrm/admin/pdfFormats?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Communication Style Options',
                        'url' => 'civicrm/admin/options/communication_style?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Email Greeting Formats',
                        'url' => 'civicrm/admin/options/email_greeting?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Postal Greeting Formats',
                        'url' => 'civicrm/admin/options/postal_greeting?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Addressee Formats',
                        'url' => 'civicrm/admin/options/addressee?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Localization',
                  'permission' => 'access root menu items and configurations',
                  'operator' => '',
                ],
              'child' =>
                [
                  [
                    'attributes' =>
                      [
                        'label' => 'Languages, Currency, Locations',
                        'url' => 'civicrm/admin/setting/localization?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Address Settings',
                        'url' => 'civicrm/admin/setting/preferences/address?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Date Formats',
                        'url' => 'civicrm/admin/setting/date?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Preferred Language Options',
                        'url' => 'civicrm/admin/options/languages?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Users and Permissions',
                  'permission' => 'access root menu items and configurations',
                  'operator' => '',
                ],
              'child' =>
                [
                  [
                    'attributes' =>
                      [
                        'label' => 'Permissions (Access Control)',
                        'url' => 'civicrm/admin/access?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Synchronize Users to Contacts',
                        'url' => 'civicrm/admin/synchUser?reset=1',
                        'permission' => 'access root menu items and configurations',
                        'operator' => '',
                      ],
                  ],
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'System Settings',
                  'permission' => 'access root menu items and configurations',
                  'operator' => '',
                ],
              'child' => CRM_HRCore_Menu_SystemSettings::getItems(),
            ],
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Help',
            'permission' => 'access CiviCRM',
            'icon' => 'crm-i fa-question-circle',
          ],
        'child' =>
          [
            [
              'attributes' =>
                [
                  'label' => 'User Guide',
                  'url' => 'http://civihr-documentation.readthedocs.io/en/latest/',
                  'target' => '_blank',
                  'permission' => 'access CiviCRM',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'CiviHR website',
                  'url' => 'https://www.civihr.org/',
                  'target' => '_blank',
                  'permission' => 'access root menu items and configurations',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Get support',
                  'url' => 'https://www.civihr.org/support',
                  'target' => '_blank',
                  'permission' => 'access CiviCRM',
                ],
            ],
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Developer',
            'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
            'operator' => 'AND',
            'icon' => 'crm-i fa-code',
          ],
        'child' =>
          [
            [
              'attributes' =>
                [
                  'label' => 'API Explorer',
                  'url' => 'civicrm/api',
                  'target' => '_blank',
                  'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
                  'operator' => 'AND',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Developer Docs',
                  'target' => '_blank',
                  'url' => 'https://civihr.atlassian.net/wiki/spaces/CIV/pages',
                  'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
                  'operator' => 'AND',
                ],
            ],
            [
              'attributes' =>
                [
                  'label' => 'Style Guide',
                  'target' => '_blank',
                  'url' => 'https://www.civihr.org/support',
                  'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
                  'operator' => 'AND',
                ],
              'child' =>
                [
                  [
                    'attributes' =>
                      [
                        'label' => 'crm-*',
                        'url' => 'civicrm/styleguide/crm-star',
                        'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
                        'operator' => 'AND',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Bootstrap',
                        'url' => 'civicrm/styleguide/bootstrap',
                        'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
                        'operator' => 'AND',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Bootstrap-CiviHR',
                        'url' => 'civicrm/styleguide/bootstrap-civicrm',
                        'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
                        'operator' => 'AND',
                      ],
                  ],
                  [
                    'attributes' =>
                      [
                        'label' => 'Bootstrap-CiviHR',
                        'url' => 'civicrm/styleguide/bootstrap-civihr',
                        'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
                        'operator' => 'AND',
                      ],
                  ],
                ],
            ],
          ],
      ],
      [
        'attributes' =>
          [
            'label' => 'Self Service Portal',
            'url' => 'dashboard',
          ],
      ],
    ];

    return $menuItems;
  }
}
