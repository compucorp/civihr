<?php

use CRM_HRCore_CMSData_UserMailNotifierFactory as CMSUserMailNotifierFactory;
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
    $cmsUserMailNotifier = self::getCMSUserMailNotifier($contactID);
    $cmsUserMailNotifier->sendPasswordResetEmail();

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
    $cmsUserMailNotifier = self::getCMSUserMailNotifier($contactID);
    $cmsUserMailNotifier->sendWelcomeEmail();

    CRM_Core_Session::setStatus(ts('Welcome Email sent'), 'Success', 'success');

    $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid=$contactID");
    CRM_Utils_System::redirect($url);
  }

  /**
   * Gets the CMSNotifier class depending on the CMS framework
   *
   * @param int $contactID
   *
   * @return \CRM_HRCore_CMSData_UserMailNotifierInterface
   *
   * @throws \Exception
   */
  private static function getCMSUserMailNotifier($contactID) {
    $cmsFramework = CRM_Core_Config::singleton()->userFramework;
    $contactUserInfo = ContactHelper::getUserInformation($contactID);
    $contactUserInfo['cmsId'] = $contactUserInfo['id'];

    return CMSUserMailNotifierFactory::create($cmsFramework, $contactUserInfo);
  }
}
