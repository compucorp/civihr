<?php

use CRM_HRCore_Form_CreateUserRecordTaskForm as CreateUserRecordTaskForm;
use CRM_HRCore_Form_SendInvitationEmailTaskForm as SendInvitationEmailTaskForm;

class CRM_HRCore_SearchTask_ContactFormSearchTaskAdder {

  /**
   * @inheritdoc
   */
  public static function add(&$tasks) {
    $tasks[] = [
      'title'  => ts('Create User Record'),
      'class'  => CreateUserRecordTaskForm::class,
    ];
    $tasks[] = [
      'title'  => ts('Send Invitation Email'),
      'class'  => SendInvitationEmailTaskForm::class,
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
