<?php

/**
 * Collection of upgrade steps.
 */
class CRM_HRLeaveAndAbsences_Upgrader extends CRM_HRLeaveAndAbsences_Upgrader_Base {

  use CRM_HRLeaveAndAbsences_Upgrader_Step_1000;

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
  }
}
