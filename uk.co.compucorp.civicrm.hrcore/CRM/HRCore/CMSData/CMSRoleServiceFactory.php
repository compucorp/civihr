<?php

use CRM_HRCore_CMSData_Role_DrupalRoleService as DrupalRoleService;
use CRM_HRCore_CMSData_Role_RoleServiceInterface as RoleServiceInterface;

/**
 * This factory is responsible for returning a class to interact with roles for
 * the current CMS
 */
class CRM_HRCore_CMSData_CMSRoleServiceFactory {

  /**
   * Creates a service to interact with CMS roles based on the current CMS
   *
   * @return RoleServiceInterface
   */
  public static function create() {
    $userFramework = CRM_Core_Config::singleton()->userFramework;

    switch ($userFramework) {
      case 'Drupal':
        return new DrupalRoleService();
      default:
        $msg = sprintf('Unrecognized system "%s"', $userFramework);
        throw new \Exception($msg);
    }
  }

}
