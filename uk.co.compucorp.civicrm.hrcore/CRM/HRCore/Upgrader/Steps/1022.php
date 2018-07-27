<?php

trait CRM_HRCore_Upgrader_Steps_1022 {

  /**
   * Uninstall the hrdemog extension.
   *
   * This extension was already deleted from the code at this point, so this
   * will fail if you run it from the UI (missing extension). Running
   * upgraders from the CLI (as is done for production releases) will work
   *
   * @return bool
   */
  public function upgrade_1022() {
    $key = 'org.civicrm.hrdemog';

    if (!CRM_HRCore_Helper_ExtensionHelper::isExtensionEnabled($key)) {
      return TRUE;
    }

    civicrm_api3('Extension', 'disable', ['keys' => $key]);
    civicrm_api3('Extension', 'uninstall', ['keys' => $key]);

    return TRUE;
  }

}
