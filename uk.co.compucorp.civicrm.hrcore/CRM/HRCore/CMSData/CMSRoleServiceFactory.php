<?php

use CRM_HRCore_CMSData_Role_DrupalRoleService as DrupalRoleService;
use CRM_HRCore_CMSData_Role_RoleServiceInterface as RoleServiceInterface;

class CRM_HRCore_Service_CMSRoleServiceFactory {

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
