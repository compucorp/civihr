<?php

use CRM_HRCore_CMSData_UserRoleInterface as UserRoleInterface;
use CRM_HRCore_CMSData_UserRole_Drupal as DrupalUserRole;

class CRM_HRCore_CMSData_UserRoleFactory {

  /**
   * Creates an object of the UserRoleInterface class based on the
   * CMS framework in use.
   *
   * @param array $contactData
   * @param string $cmsFramework
   * 
   * @return UserRoleInterface;
   *
   * @throws \Exception
   */
  public static function create($cmsFramework, $contactData) {
    if ($cmsFramework == 'Drupal') {
      return new DrupalUserRole($contactData);
    }

    $msg = sprintf('Unrecognized CMS: "%s"', $cmsFramework);
    throw new \Exception($msg);
  }
}
