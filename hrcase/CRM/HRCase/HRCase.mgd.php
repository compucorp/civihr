<?php
// This file declares a managed database record of type "WordReplacement".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
return [
  [
    'name' => 'WordReplacement CiviCase -> Workflow',
    'entity' => 'WordReplacement',
    'params' =>
    [
      'version' => 3,
      'find_word' => 'CiviCase',
      'replace_word'=>'Workflow',
      'is_active' => 1,
    ],
  ],
  [
    'name' => 'WordReplacement Case -> Workflow',
    'entity' => 'WordReplacement',
    'params' =>
    [
      'version' => 3,
      'find_word' => 'Case',
      'replace_word'=>'Workflow',
      'is_active' => 1,
    ],
  ],
  [
    'name' => 'WordReplacement Cases -> Workflows',
    'entity' => 'WordReplacement',
    'params' =>
    [
      'version' => 3,
      'find_word' => 'Cases',
      'replace_word'=>'Workflows',
      'is_active' => 1,
    ],
  ],
  [
    'name' => 'WordReplacement Client -> Contact',
    'entity' => 'WordReplacement',
    'params' =>
    [
      'version' => 3,
      'find_word' => 'Client',
      'replace_word'=>'Contact',
      'is_active' => 1,
    ],
  ],
  [
    'name' => 'WordReplacement case -> workflow',
    'entity' => 'WordReplacement',
    'params' =>
    [
      'version' => 3,
      'find_word' => 'case',
      'replace_word'=>'workflow',
      'is_active' => 1,
    ],
  ],
  [
    'name' => 'WordReplacement cases -> workflows',
    'entity' => 'WordReplacement',
    'params' =>
    [
      'version' => 3,
      'find_word' => 'cases',
      'replace_word'=>'workflows',
      'is_active' => 1,
    ],
  ]
];
