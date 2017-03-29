<?php

/**
 * Collection of upgrade steps.
 */
class CRM_HRCore_Upgrader extends CRM_HRCore_Upgrader_Base {

  use CRM_HRCore_Upgrader_Steps_1000;
  use CRM_HRCore_Upgrader_Steps_1001;
  use CRM_HRCore_Upgrader_Steps_1002;
  use CRM_HRCore_Upgrader_Steps_1003;

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

      if (is_callable([$this, $methodName])) {
        $this->{$methodName}();
      }
    }
  }

}
