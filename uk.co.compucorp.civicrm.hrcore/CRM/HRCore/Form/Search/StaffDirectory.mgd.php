<?php
// This file declares a managed database record of type "CustomSearch".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return [
  0 =>
  [
    'name' => 'CRM_HRCore_Form_Search_StaffDirectory',
    'entity' => 'CustomSearch',
    'params' =>
    [
      'version' => 3,
      'label' => 'Staff Directory',
      'description' => 'Staff Directory Search',
      'class_name' => 'CRM_HRCore_Form_Search_StaffDirectory',
    ],
  ],
];
