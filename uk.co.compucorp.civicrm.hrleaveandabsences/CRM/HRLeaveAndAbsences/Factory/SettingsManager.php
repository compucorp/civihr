<?php

use CRM_HRLeaveAndAbsences_Service_InMemorySettingsManager as InMemorySettingsManager;
use CRM_HRLeaveAndAbsences_Service_APISettingsManager as APISettingsManager;

/**
 * Creates CRM_HRLeaveAndAbsences_Service_SettingsManager instances
 */
class CRM_HRLeaveAndAbsences_Factory_SettingsManager {

  /**
   * Instantiate a new CRM_HRLeaveAndAbsences_Service_SettingsManager
   * implementation and returns it.
   *
   * By default, it will create an APISettingsManager instance, but if $inMemory
   * is true, an InMemorySettings instance will be returned
   *
   * @param bool $inMemory
   *
   * @return \CRM_HRLeaveAndAbsences_Service_SettingsManager
   */
  public static function create($inMemory = false) {
    if ($inMemory) {
      return new InMemorySettingsManager();
    }

    return new APISettingsManager();
  }

}
