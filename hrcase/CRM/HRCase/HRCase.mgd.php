<?php
// This file declares a managed database record of type "WordReplacement".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
return array (
  array (
    'name' => 'WordReplacement People Management',
    'entity' => 'WordReplacement',
    'params' =>
    array (
      'version' => 3,
      'find_word' => 'CiviCase',
      'replace_word'=>'People Management',
      'is_active' => 1,
    ),
  ),
  array (
    'name' => 'WordReplacement Assignment',
    'entity' => 'WordReplacement',
    'params' =>
    array (
      'version' => 3,
      'find_word' => 'Case',
      'replace_word'=>'Assignment',
      'is_active' => 1,
    ),
  ),
  array (
    'name' => 'WordReplacement Assignments',
    'entity' => 'WordReplacement',
    'params' =>
    array (
      'version' => 3,
      'find_word' => 'Cases',
      'replace_word'=>'Assignments',
      'is_active' => 1,
    ),
  ),
  array (
    'name' => 'WordReplacement Client',
    'entity' => 'WordReplacement',
    'params' =>
    array (
      'version' => 3,
      'find_word' => 'Client',
      'replace_word'=>'Contact',
      'is_active' => 1,
    ),
  ),
  array (
    'name' => 'WordReplacement case',
    'entity' => 'WordReplacement',
    'params' =>
    array (
      'version' => 3,
      'find_word' => 'case',
      'replace_word'=>'assignment',
      'is_active' => 1,
    ),
  )
);
