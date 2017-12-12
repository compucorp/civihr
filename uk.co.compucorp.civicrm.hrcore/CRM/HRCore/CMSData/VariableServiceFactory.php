<?php

use CRM_HRCore_CMSData_Variable_VariableServiceInterface as VariableServiceInterface;
use CRM_HRCore_CMSData_Variable_DrupalVariableService as DrupalVariableService;

/**
 * Responsible for creating a variable service depending on the CMS system.
 */
class CRM_HRCore_CMSData_VariableServiceFactory {

  /**
   * Returns a service to interact with CMS variables
   *
   * @return VariableServiceInterface
   */
  public static function create() {
    $userSystem = CRM_Core_Config::singleton()->userSystem;

    switch (get_class($userSystem)) {
      case CRM_Utils_System_Drupal::class:
        return new DrupalVariableService();

      default:
        $msg = sprintf('Unrecognized system "%s"', get_class($userSystem));
        throw new \Exception($msg);
    }
  }

}
