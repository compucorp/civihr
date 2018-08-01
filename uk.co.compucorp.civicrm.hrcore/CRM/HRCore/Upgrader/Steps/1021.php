<?php

trait CRM_HRCore_Upgrader_Steps_1021 {

  /**
   * Creates the Missing Log Tables after CiviCRM Upgrade
   */
  public function upgrade_1021() {
    $this->up1021_fixMissingLogTables();

    return TRUE;
  }

  /**
   * Fixes the Missing Log Tables
   */
  private function up1021_fixMissingLogTables() {
    civicrm_api3('System', 'createmissinglogtables');
  }

}
