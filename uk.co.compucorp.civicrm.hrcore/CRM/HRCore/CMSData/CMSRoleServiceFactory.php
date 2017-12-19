<?php

use CRM_HRCore_CMSData_Role_DrupalRoleService as DrupalRoleService;
use CRM_HRCore_CMSData_Role_RoleServiceInterface as RoleServiceInterface;

/**
 * This factory is responsible for returning a class to interact with roles for
 * the current CMS
 */
class CRM_HRCore_CMSData_CMSRoleServiceFactory {

  /**
   * @return RoleServiceInterface
   */
  public static function create() {
    $userSystem = CRM_Core_Config::singleton()->userSystem;

    switch (get_class($userSystem)) {
      case CRM_Utils_System_Drupal::class:
        return new DrupalRoleService();
      default:
        $msg = sprintf('Unrecognized system "%s"', get_class($userSystem));
        throw new \Exception($msg);
    }
  }

}
