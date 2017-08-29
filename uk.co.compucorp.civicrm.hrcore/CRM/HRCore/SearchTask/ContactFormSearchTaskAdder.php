<?php

use CRM_HRCore_Form_CreateUserRecordTaskForm as CreateUserRecordTaskForm;

class CRM_HRCore_SearchTask_ContactFormSearchTaskAdder {

  /**
   * @inheritdoc
   */
  public static function add(&$tasks) {
    $tasks[] = [
      'title'  => ts('Create User Record(s)'),
      'class'  => CreateUserRecordTaskForm::class,
    ];
  }

  /**
   * @inheritdoc
   */
  public static function shouldAdd($objectName) {
    $isContact = $objectName === 'contact';
    $canCreateUsers = CRM_Core_Permission::check('create users');

    return $isContact && $canCreateUsers;
  }

}
