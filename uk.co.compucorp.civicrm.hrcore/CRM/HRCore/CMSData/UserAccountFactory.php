<?php

use CRM_HRCore_CMSData_UserAccountInterface as UserAccountInterface;
use CRM_HRCore_CMSData_UserAccount_Drupal as DrupalUserAccount;

class CRM_HRCore_CMSData_UserAccountFactory {

  /**
   * Creates an object of the UserAccount class based on the
   * CMS framework in use.
   *
   * @return UserAccountInterface;
   *
   * @throws \Exception
   */
  public static function create() {
    $cmsFramework = CRM_Core_Config::singleton()->userFramework;

    if ($cmsFramework == 'Drupal') {
      return new DrupalUserAccount();
    }

    $msg = sprintf('Unrecognized CMS: "%s"', $cmsFramework);
    throw new \Exception($msg);
  }
}
