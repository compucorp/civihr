<?php

/**
 * Collection of upgrade steps.
 */
class CRM_HRLeaveAndAbsences_Upgrader extends CRM_HRLeaveAndAbsences_Upgrader_Base {

  use CRM_HRLeaveAndAbsences_Upgrader_Step_1000;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1001;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1002;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1003;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1004;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1005;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1006;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1007;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1008;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1009;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1010;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1011;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1012;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1013;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1014;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1015;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1016;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1017;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1018;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1019;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1020;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1021;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1022;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1023;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1024;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1025;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1026;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1027;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1028;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1029;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1030;
  use CRM_HRLeaveAndAbsences_Upgrader_Step_1031;

  /**
   * A list of directories to be scanned for XML installation files
   *
   * @var array
   */
  private $xmlDirectories = [ 'option_groups' ];

  /**
   * Custom extension installation logic
   */
  public function install() {
    $this->processXMLInstallationFiles();
    $this->runAllUpgraders();
  }

  /**
   * Scans all the directories in $xmlDirectories for installation files
   * (xml files ending with _install.xml) and processes them.
   */
  private function processXMLInstallationFiles() {
    foreach($this->xmlDirectories as $directory) {
      $files = glob($this->extensionDir . "/xml/{$directory}/*_install.xml");
      if (is_array($files)) {
        foreach ($files as $file) {
          $this->executeCustomDataFileByAbsPath($file);
        }
      }
    }
    // Flush the cache so that all pseudoconstants can be re-read from db
    // This is to avoid issues when running upgraders during installation
    // whereby some pseudoconstants were not available.
    CRM_Core_PseudoConstant::flush();
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
