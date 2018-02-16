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
    self::doUserAction('cancel', 'User account has been deleted');
  }

  /**
   * Function to disable the CMS user account of the
   * contact.
   */
  public static function disable() {
    self::doUserAction('disable', 'User account has been disabled');
  }

  /**
   * Function to enable the CMS user account of the
   * contact.
   */
  public static function enable() {
    self::doUserAction('enable', 'User account has been enabled');
  }

  /**
   * Function to execute the required method of the User Account
   * object based on the action passed in.
   * After execution, the success message is displayed and the
   * user redirected to contact summary page.
   *
   * @param string $action
   * @param string $successMessage
   */
  private static function doUserAction($action, $successMessage) {
    $contactID = CRM_Utils_Array::value('cid', $_GET);
    $contactInfo = ContactHelper::getUserInformation($contactID);
    $userAccount = UserAccountFactory::create();
    $userAccount->$action($contactInfo);
    CRM_Core_Session::setStatus(ts($successMessage), 'Success', 'success');

    $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid=$contactID");
    CRM_Utils_System::redirect($url);
  }
}
