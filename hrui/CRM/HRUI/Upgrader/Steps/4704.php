<?php

/**
 * Fix the Custom CSS URL to use an relative path
 */
trait CRM_HRUI_Upgrader_Steps_4704 {

  /**
   * @return bool
   *   True on success, false otherwise
   */
  public function upgrade_4704() {
    civicrm_api3('Setting', 'create', [
      'customCSSURL' => '[civicrm.root]/tools/extensions/org.civicrm.shoreditch/css/custom-civicrm.css',
    ]);

    return TRUE;
  }
}
