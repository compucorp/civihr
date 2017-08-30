<?php

use CRM_HRCore_Form_CreateUserRecordTaskForm as CreateUserRecordTaskForm;

class CRM_HRCore_SearchTask_ContactFormSearchTaskAdder {

  /**
   * @param $tasks
   */
  public static function add(&$tasks) {
    $tasks[] = [
      'title'  => ts('Create User Accounts(s)'),
      'class'  => CreateUserRecordTaskForm::class,
    ];
  }

  /**
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
