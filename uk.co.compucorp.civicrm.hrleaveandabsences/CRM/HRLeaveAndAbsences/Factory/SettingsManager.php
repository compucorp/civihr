<?php

use CRM_HRLeaveAndAbsences_Service_InMemorySettingsManager as InMemorySettingsManager;
use CRM_HRLeaveAndAbsences_Service_APISettingsManager as APISettingsManager;

class CRM_HRLeaveAndAbsences_Factory_SettingsManager {

  public static function create($inMemory = false) {
    if ($inMemory) {
      return new InMemorySettingsManager();
    }

    return new APISettingsManager();
  }

}
