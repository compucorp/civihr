<?php

use CRM_HRContactActionsMenu_Helper_Contact as ContactHelper;
use CRM_HRCore_CMSData_UserAccountFactory as UserAccountFactory;

/**
 * Class CRM_HRContactActionsMenu_Page_UserAccount
 */
class CRM_HRContactActionsMenu_Page_UserAccount {

  /**
   * Function to delete the CMS user account of the
   * contact.
   */
  public static function delete() {
    $contactID = CRM_Utils_Array::value('cid', $_GET);
    $contactInfo = ContactHelper::getUserInformation($contactID);
    $userAccount = UserAccountFactory::create();
    $userAccount->cancel($contactInfo);

    CRM_Core_Session::setStatus(ts('User account has been deleted'), 'Success', 'success');

    $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid=$contactID");
    CRM_Utils_System::redirect($url);
  }

  /**
   * Function to disable the CMS user account of the
   * contact.
   */
  public static function disable() {
    $contactID = CRM_Utils_Array::value('cid', $_GET);
    $contactInfo = ContactHelper::getUserInformation($contactID);
    $userAccount = UserAccountFactory::create();
    $userAccount->disable($contactInfo);

    CRM_Core_Session::setStatus(ts('User account has been disabled'), 'Success', 'success');

    $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid=$contactID");
    CRM_Utils_System::redirect($url);
  }

  /**
   * Function to enable the CMS user account of the
   * contact.
   */
  public static function enable() {
    $contactID = CRM_Utils_Array::value('cid', $_GET);
    $contactInfo = ContactHelper::getUserInformation($contactID);
    $userAccount = UserAccountFactory::create();
    $userAccount->enable($contactInfo);

    CRM_Core_Session::setStatus(ts('User account has been enabled'), 'Success', 'success');

    $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid=$contactID");
    CRM_Utils_System::redirect($url);
  }
}
