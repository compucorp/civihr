<?php

use CRM_HRCore_Helper_ExtensionHelper as ExtensionHelper;

trait CRM_HRCore_Upgrader_Steps_1010 {

  /**
   * Installs the Contact Actions Menu extension if not
   * installed already.
   */
  public function upgrade_1010() {
    $key = 'uk.co.compucorp.civicrm.hrcontactactionsmenu';
    if (ExtensionHelper::isExtensionEnabled($key)) {
      return TRUE;
    }

    civicrm_api3('Extension', 'install', [
      'keys' => [$key]
    ]);

    return TRUE;
  }
}
