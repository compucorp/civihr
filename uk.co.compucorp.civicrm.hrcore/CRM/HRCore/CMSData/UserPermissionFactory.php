<?php

use CRM_HRCore_CMSData_UserPermission_Drupal as DrupalUserPermission;
use CRM_HRCore_CMSData_UserPermissionInterface as UserPermissionInterface;

class CRM_HRCore_CMSData_UserPermissionFactory {

  /**
   * Creates an object of the UserPermissionInterface class based on the
   * CMS framework in use.
   *
   * @return UserPermissionInterface;
   *
   * @throws \Exception
   */
  public static function create() {
    $cmsFramework = CRM_Core_Config::singleton()->userFramework;

    if ($cmsFramework == 'Drupal') {
      return new DrupalUserPermission();
    }

    $msg = sprintf('Unrecognized CMS: "%s"', $cmsFramework);
    throw new \Exception($msg);
  }
}
