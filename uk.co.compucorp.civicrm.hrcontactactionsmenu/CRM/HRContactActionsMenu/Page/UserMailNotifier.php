<?php

use CRM_HRContactActionsMenu_Helper_Contact as ContactHelper;

/**
 * Class CRM_HRContactActionsMenu_Page_UserMailNotifier
 */
class CRM_HRContactActionsMenu_Page_UserMailNotifier {

  /**
   * Function to send password reset email to CMS user
   * of the civicrm contact.
   */
  public static function sendPasswordResetEmail() {
    $contactID = CRM_Utils_Array::value('cid', $_GET);
    $contactInfo = ContactHelper::getUserInformation($contactID);
    $cmsUserMailNotifier = Civi::container()->get('hrcore.cms_notifier');
    $cmsUserMailNotifier->sendPasswordResetEmail($contactInfo);

    CRM_Core_Session::setStatus(ts('Password Reset Email sent'), 'Success', 'success');

    $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid=$contactID");
    CRM_Utils_System::redirect($url);
  }

  /**
   * Function to send welcome email to CMS user
   * of the civicrm contact.
   */
  public static function sendWelcomeEmail() {
    $contactID = CRM_Utils_Array::value('cid', $_GET);
    $contactInfo = ContactHelper::getUserInformation($contactID);
    $cmsUserMailNotifier = Civi::container()->get('hrcore.cms_notifier');
    $cmsUserMailNotifier->sendWelcomeEmail($contactInfo);

    CRM_Core_Session::setStatus(ts('Welcome Email sent'), 'Success', 'success');

    $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid=$contactID");
    CRM_Utils_System::redirect($url);
  }
}
