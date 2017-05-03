<?php

/**
 * Collection of upgrade steps
 */
class CRM_HRRecruitment_Upgrader extends CRM_HRRecruitment_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   *
  public function install() {
    $this->executeSqlFile('sql/myinstall.sql');
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a couple simple queries
   *
   * @return TRUE on success
   * @throws Exception
   *
  public function upgrade_4200() {
    $this->ctx->log->info('Applying update 4200');
    CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
    CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
    return TRUE;
  } // */


  /**
   * Example: Run an external SQL script
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */
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
