<?php

use CRM_HRCore_Form_CreateUserRecordTaskForm as CreateUserRecordTaskForm;

class CRM_HRCore_SearchTask_ContactFormSearchTaskAdder {

  /**
   * @param $tasks
   */
  public static function add(&$tasks) {

    $tasks = [
      'user' => [
        'title' => ts('Create User Accounts'),
        'class' => CreateUserRecordTaskForm::class,
      ],
      'delete' => [
        'title' => ts('Delete Staff'),
        'class' => 'CRM_Contact_Form_Task_Delete',
        'result' => FALSE,
        'url' => 'civicrm/task/delete-contact',
      ],
      'permanently' => [
        'title' => ts('Delete Staff permanently'),
        'class' => 'CRM_Contact_Form_Task_Delete',
        'result' => FALSE,
      ],
      'export' => [
        'title' => ts('Export Staff'),
        'class' => [
          'CRM_Export_Form_Select',
          'CRM_Export_Form_Map',
        ],
        'result' => FALSE,
      ],
      'merge' => [
        'title' => ts('Merge Staff'),
        'class' => 'CRM_Contact_Form_Task_Merge',
        'result' => TRUE,
      ],
      'print' => [
        'title' => ts('Print/merge document'),
        'class' => 'CRM_Contact_Form_Task_PDF',
        'result' => TRUE,
        'url' => 'civicrm/task/print-document',
      ],
    ];
  }

  /**
   *
   * @param $objectName
   *
   * @return bool
   */
  public static function shouldAdd($objectName) {
    $isContact = $objectName === 'contact';
    $canCreateUsers = CRM_Core_Permission::check('create users');

    return $isContact && $canCreateUsers;
  }

}
