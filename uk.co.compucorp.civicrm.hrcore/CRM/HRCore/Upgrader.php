<?php

/**
 * Collection of upgrade steps.
 */
class CRM_HRCore_Upgrader extends CRM_HRCore_Upgrader_Base {

  use CRM_HRCore_Upgrader_Steps_1000;

  public function install() {
    $this->runAllUpgraders();
  }

  /**
   * Runs all the upgrader methods when installing the extension
   */
  private function runAllUpgraders() {
    $revisions = $this->getRevisions();
    foreach ($revisions as $revision) {
      $methodName = 'upgrade_' . $revision;
      if (is_callable(array($this, $methodName))) {
        $this->{$methodName}();
      }
    }
  }

}
