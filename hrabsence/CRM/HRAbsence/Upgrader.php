<?php

/**
 * Collection of upgrade steps
 */
class CRM_HRAbsence_Upgrader extends CRM_HRAbsence_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
    $this->installActivityTypes();
    $this->addDefaultPeriod();
    $this->installAbsenceTypes();

    //$this->executeSqlFile('sql/myinstall.sql');
  }

  public function installActivityTypes() {
    $activityTypesResult = civicrm_api3('activity_type', 'get', array());
    $weight = count($activityTypesResult["values"]);

    if (!in_array("Public Holiday", $activityTypesResult["values"])) {
      $weight = $weight + 1;
      $params = array(
        'weight' => $weight,
        'label' => 'Public Holiday',
        'filter' => 1,
        'is_active' => 1,
        'is_optgroup' => 0,
        'is_default' => 0,
        'grouping' => 'Timesheet',
      );
      $resultCreatePublicHoliday = civicrm_api3('activity_type', 'create', $params);
    }

    if (!in_array("Absence", $activityTypesResult["values"])) {
      $weight = $weight + 1;
      $params = array(
        'weight' => $weight,
        'label' => 'Absence',
        'filter' => 1,
        'is_active' => 1,
        'is_optgroup' => 0,
        'is_default' => 0,
        'grouping' => 'Timesheet',
      );
      $resultCreateAbsence = civicrm_api3('activity_type', 'create', $params);
    }
  }
  
  public function addDefaultPeriod() {
    if (CRM_HRAbsence_BAO_HRAbsencePeriod::getRecordCount($params = array()) == 0) {
      $currentYear = date('Y');
      $params = array(
        'name' => $currentYear,
        'title' => $currentYear.' (Jan 1 to Dec 31)',
        'start_date' => $currentYear.'-01-01 00:00:00',
        'end_date' => $currentYear.'-12-31 23:59:59',
      );
      CRM_HRAbsence_BAO_HRAbsencePeriod::create($params);
    }
  }

  public function installAbsenceTypes() {
    $leaves = TRUE;
    $weight = 0;
    $values = '';
    $options =  CRM_Core_OptionGroup::values('hrjob_leave_type', TRUE, FALSE);
    if(empty($options)) {
      $leaves = FALSE;
      $options = array(
        "Sick" => "Sick",
        "Vacation" => "Vacation",
        "Maternity" => "Maternity",
        "Paternity" => "Paternity",
        "TOIL" => "TOIL",
        "Other" => "Other"
      );
    }

    foreach ($options as $orgKey => $orgValue) {
      $params = array(
        'title' => $orgValue,
        'is_active' => 1,
        'allow_debits' => 1
      );
      if ($orgKey == "TOIL") {
        $params['allow_credits'] = 1;
      }
      $absenceTypes = CRM_HRAbsence_BAO_HRAbsenceType::create($params);
      $values .= " WHEN '{$orgValue}' THEN '{$absenceTypes->id}'";
    }
    if (CRM_Core_DAO::checkTableExists("civicrm_hrjob_leave") && $leaves) {
      $query = "UPDATE civicrm_hrjob_leave
        SET leave_type = CASE leave_type
        {$values}
        END;";
    }
    CRM_Core_OptionGroup::deleteAssoc('hrjob_leave_type');
  }

  /**
   * Example: Run a simple query when a module is enabled
   **/
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE civicrm_hrabsence_type SET is_active = 1');
  }

  /**
   * Example: Run a simple query when a module is disabled
   **/
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE civicrm_hrabsence_type SET is_active = 0');
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled
   **/
  public function uninstall() {
    $dao = CRM_Core_DAO::executeQuery('SELECT * from civicrm_hrabsence_type');
    while($dao->fetch()) {
      if($dao->credit_activity_type_id) {
        $query = "DELETE FROM civicrm_activity WHERE activity_type_id IN ( {$dao->credit_activity_type_id} )";
        CRM_Core_BAO_OptionValue::del($dao->credit_activity_type_id);
      }
      if($dao->debit_activity_type_id) {
        $query = "DELETE FROM civicrm_activity WHERE activity_type_id IN ( {$dao->debit_activity_type_id} )";
        CRM_Core_BAO_OptionValue::del($dao->debit_activity_type_id);
      }
    }
    CRM_Core_DAO::executeQuery('DROP TABLE civicrm_hrabsence_entitlement');
    CRM_Core_DAO::executeQuery('DROP TABLE civicrm_hrabsence_period');
    CRM_Core_DAO::executeQuery('DROP TABLE civicrm_hrabsence_type');
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

}
