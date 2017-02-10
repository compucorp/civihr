<?php
// This file declares a managed database record of type "CaseType" and "OptionValue".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference

$activityTypes = [
  'Schedule Exit Interview',
  'Get "No Dues" certification',
  'Conduct Exit interview',
  'Revoke access to databases',
  'Block work email ID',
  'Background Check',
  'References Check',
  'Schedule joining date',
  'Issue appointment letter',
  'Fill Employee Details Form',
  'Submission of ID/Residence proofs and photos',
  'Program and work induction by program supervisor',
  'Enter employee data in CiviHR',
  'Group Orientation to organization, values, policies',
  'Probation appraisal (start probation workflow)',
  'Confirm End of Probation Date',
  'Start Probation workflow'
];

$activityTypesList = [];
foreach ($activityTypes as $activityType) {
  $activityTypesList[] =  [
    'name' => str_replace(' ', '_', $activityType) . '_Activity_Type',
    'entity' => 'OptionValue',
    'params' =>
      [
        'version' => 3,
        'option_group_id' => 'activity_type',
        'name' => $activityType,
        'title' => $activityType,
        'component_id' => 'CiviTask',
        'is_active' => 1,
      ],
  ];
}

$caseTypesList = [
  [
    'name' => 'Exiting_CaseType',
    'entity' => 'CaseType',
    'params' =>
      [
        'version' => 3,
        'name' => 'Exiting',
        'title' => 'Exiting',
        'is_active' => 1,
        'weight' => 1,
        'definition' =>
          [
            'activityTypes' => [
              ['name' => 'Schedule Exit Interview'],
              ['name' => 'Get "No Dues" certification'],
              ['name' => 'Conduct Exit interview'],
              ['name' => 'Revoke access to databases'],
              ['name' => 'Block work email ID'],
              ['name' => 'Background Check'],
              ['name' => 'References Check']
            ],

            'activitySets' => [
              [
                'name'   => 'standard_timeline',
                'label' => 'Standard Timeline',
                'timeline' => 1,
                'activityTypes' => [
                  [
                    'name' => 'Schedule Exit Interview',
                    'reference_offset' => -10,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ],
                  [
                    'name' => 'Get "No Dues" certification',
                    'reference_offset' => -7,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ],
                  [
                    'name' => 'Conduct Exit interview',
                    'reference_offset' => -3,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ],
                  [
                    'name' => 'Revoke access to databases',
                    'reference_offset' => 0,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ],
                  [
                    'name' => 'Block work email ID',
                    'reference_offset' => 0,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ]
                ]
              ]
            ],

            'caseRoles' => [
              ['name' => 'HR Manager', 'creator' => 1, 'manager' => 1]
            ]
          ]
      ],
  ],

  [
    'name' => 'Joining_CaseType',
    'entity' => 'CaseType',
    'params' =>
      [
        'version' => 3,
        'name' => 'Joining',
        'title' => 'Joining',
        'is_active' => 1,
        'weight' => 2,
        'definition' =>
          [
            'activityTypes' => [
              ['name' => 'Schedule joining date'],
              ['name' => 'Issue appointment letter'],
              ['name' => 'Fill Employee Details Form'],
              ['name' => 'Submission of ID/Residence proofs and photos'],
              ['name' => 'Program and work induction by program supervisor'],
              ['name' => 'Enter employee data in CiviHR'],
              ['name' => 'Group Orientation to organization, values, policies'],
              ['name' => 'Probation appraisal (start probation workflow)'],
              ['name' => 'Background Check'],
              ['name' => 'References Check'],
              ['name' => 'Confirm End of Probation Date'],
              ['name' => 'Start Probation workflow']
            ],

            'activitySets' => [
              [
                'name'   => 'standard_timeline',
                'label' => 'Standard Timeline',
                'timeline' => 1,
                'activityTypes' => [
                  [
                    'name' => 'Schedule joining date',
                    'reference_offset' => -10,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ],
                  [
                    'name' => 'Issue appointment letter',
                    'reference_offset' => -10,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ],
                  [
                    'name' => 'Fill Employee Details Form',
                    'reference_offset' => -10,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ],
                  [
                    'name' => 'Submission of ID/Residence proofs and photos',
                    'reference_offset' => -10,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ],
                  [
                    'name' => 'Enter employee data in CiviHR',
                    'reference_offset' => -7,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ],
                  [
                    'name' => 'Program and work induction by program supervisor',
                    'reference_offset' => -10,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ],
                  [
                    'name' => 'Group Orientation to organization, values, policies',
                    'reference_offset' => 7,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ],
                  [
                    'name' => 'Confirm End of Probation Date',
                    'reference_offset' => 30,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ],
                  [
                    'name' => 'Start Probation workflow',
                    'reference_offset' => 30,
                    'reference_activity' => 'Open Case',
                    'reference_select' => 'newest'
                  ]
                ]
              ]
            ],

            'caseRoles' => [
              ['name' => 'HR Manager', 'creator' => 1, 'manager' => 1],
              ['name' => 'Recruiting Manager', 'creator' => 1, 'manager' => 1]
            ]
          ]
      ],
  ],
];

return array_merge($activityTypesList, $caseTypesList);