<?php

use CRM_HRCore_Form_CreateUserRecordTaskForm as CreateUserRecordTaskForm;
use CRM_HRCore_Form_SendInvitationEmailTaskForm as SendInvitationEmailTaskForm;
use CRM_HRCore_SearchTask_SearchTaskAdderInterface as SearchTaskAdderInterface;

class CRM_HRCore_SearchTask_ContactFormSearchTaskAdder implements SearchTaskAdderInterface {

  /**
   * @param $tasks
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
