<?php

trait CRM_HRCore_Upgrader_Steps_1031 {

  /**
   * Disables and Uninstall the Recruitment Extension
   *
   * @return bool
   */
  public function upgrade_1031() {
    $this->up1031_updateManagedEntitiesTable();
    $this->up1031_disableAndUninstallRecruitment();

    return TRUE;
  }

  /**
   * Making sure that the entity_id of CaseType and Managed are equal
   */
  private function up1031_updateManagedEntitiesTable() {
    $caseType = civicrm_api3('CaseType', 'get', [
      'return' => ['id'],
      'name' => 'Application',
    ]);
    if($caseType['count']===0){
      return;
    }
    $dao = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_managed where name="Application"');
    while ($dao->fetch()) {
      $managedCaseTypeId = $dao->entity_id;
    }
    if ($managedCaseTypeId === $caseType['id']) {
      return;
    }
    $params = [1 => [$caseType['id'], 'Integer']];
    $updateSql = 'UPDATE civicrm_managed SET entity_id = %1 where name="Application"';
    CRM_Core_DAO::executeQuery($updateSql, $params);
  }

  /**
   * disables and then Uninstalls the Recruitment Extensions
   */
  private function up1031_disableAndUninstallRecruitment() {
    if (!ExtensionHelper::isExtensionEnabled('org.civicrm.hrrecruitment')) {
      return;
    }
    civicrm_api3('Extension', 'disable', [
      'keys' => 'org.civicrm.hrrecruitment',
      'api.Extension.uninstall' => ['keys' => 'org.civicrm.hrrecruitment'],
    ]);
  }

}
