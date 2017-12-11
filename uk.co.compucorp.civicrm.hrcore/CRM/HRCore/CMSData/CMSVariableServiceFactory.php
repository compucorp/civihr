<?php

use CRM_HRCore_CMSData_Variable_VariableServiceInterface as VariableAdapterInterface;
use CRM_HRCore_CMSData_Variable_DrupalVariableService as DrupalVariableService;

class CRM_HRCore_CMSData_CMSVariableServiceFactory {
  /**
   * @return VariableAdapterInterface
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
