<?php

/**
 * Collection of upgrade steps
 */
class CRM_HRCareer_Upgrader extends CRM_HRCareer_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
    $this->executeCustomDataFile('xml/customdata.xml');
  }
}
