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

  /**
   * Remove the "Application" assignment type and the managed entity.
   *
   * @return bool
   */
  public function upgrade_1402() {
    // remove the managed entity entry
    $query = 'DELETE FROM civicrm_managed ' .
      'WHERE module = %1 AND name = %2 AND entity_type = %3';
    $params = [
      1 => ['org.civicrm.hrrecruitment', 'String'],
      2 => ['Application', 'String'],
      3 => ['CaseType', 'String']
    ];
    CRM_Core_DAO_Managed::executeQuery($query, $params);

    $result = civicrm_api3('CaseType', 'get', [
      'name' => 'Application',
    ]);

    // Application type doesn't exist, so our job is done
    if ($result['count'] < 1) {
      return TRUE;
    }

    $applicationCaseType = array_shift($result['values']);

    // remove Assignments of type 'Application'
    civicrm_api3('Assignment', 'get', [
      'case_type_id' => "Application",
      'options' => ['limit' => 0],
      'api.Assignment.delete' => ['id' => '$value.id'],
    ]);

    // remove the 'Application' case type
    civicrm_api3('CaseType', 'delete', [
      'id' => $applicationCaseType['id']
    ]);

    return TRUE;
  }

}
