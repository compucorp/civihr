<?php

/**
 * Collection of upgrade steps
 */
class CRM_HRSampleData_Upgrader extends CRM_HRSampleData_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
    require_once ($this->extensionDir . '/GenerateHRData.php');
    $sampleData = new GenerateHRData();
  }
}
