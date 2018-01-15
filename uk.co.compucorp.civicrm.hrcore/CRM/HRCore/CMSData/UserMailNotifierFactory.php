<?php

use CRM_HRCore_CMSData_UserMailNotifierInterface as UserMailNotifierInterface;
use CRM_HRCore_CMSData_UserMailNotifier_Drupal as DrupalUserMailNotifier;

/**
 * Class CRM_HRCore_CMSData_UserMailNotifierFactory
 */
class CRM_HRCore_CMSData_UserMailNotifierFactory {

  /**
   * Creates an object of the UserMailNotifier class based on the
   * CMS framework in use.
   *
   * @return UserMailNotifierInterface;
   *
   * @throws \Exception
   */
  public static function create() {
    $cmsFramework = CRM_Core_Config::singleton()->userFramework;

    if ($cmsFramework == 'Drupal') {
      return new DrupalUserMailNotifier();
    }

    $msg = sprintf('Unrecognized CMS: "%s"', $cmsFramework);
    throw new \Exception($msg);
  }
}
