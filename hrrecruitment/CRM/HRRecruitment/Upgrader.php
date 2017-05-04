<?php

/**
 * Collection of upgrade steps
 */
class CRM_HRRecruitment_Upgrader extends CRM_HRRecruitment_Upgrader_Base {

  /**
   * Sets the weight on "Application" CaseType
   *
   * @return bool
   */
  public function upgrade_1400() {
    $this->ctx->log->info('Applying update 1400');
    CRM_Core_DAO::executeQuery("UPDATE civicrm_case_type SET weight = 7 WHERE name = 'Application'");
    return TRUE;
  }

}
