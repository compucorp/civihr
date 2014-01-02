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
    //$this->executeSqlFile('sql/myinstall.sql');
/*  $components = CRM_Core_Component::getComponents();
  	if(!array_key_exists("CiviTimesheet",$components))
  	{
  		CRM_Core_DAO::executeQuery("insert into civicrm_component (name, namespace) values ('CiviTimesheet', 'CRM_Timesheet')");
  	}
  	
  	try{
  		$result = civicrm_api3('activity_type', 'get', array());
  		$weight = count($result["values"]);
  		if(!array_search("Public Holiday", $result["values"])) {
  			$weight = $weight+1;
  			$params = array(
  					'weight' => $weight,
  					'label' => 'Public Holiday',
  					'filter' => 0,
  					'is_active' => 1,
  					'is_optgroup' => 0,
  					'is_default' => 0,
  			);
  			$resultCreateActivityType = civicrm_api3('activity_type', 'create', $params);
  		}
  	
  		if(!array_search("Absence", $result["values"]) && array_key_exists("CiviTimesheet",$components)) {
  			$weight = $weight+1;
  			$params = array(
  					'weight' => $weight,
  					'label' => 'Absence',
  					'filter' => 0,
  					'is_active' => 1,
  					'is_optgroup' => 0,
  					'is_default' => 0,
  					'component_id' => $components["CiviTimesheet"]->componentID,
  			);
  			$resultCreateActivityType = civicrm_api3('activity_type', 'create', $params);
  		}
  	}
  	catch (CiviCRM_API3_Exception $e) {
  		// handle error here
  		$errorMessage = $e->getMessage();
  		$errorCode = $e->getErrorCode();
  		$errorData = $e->getExtraParams();
  		return array('error' => $errorMessage, 'error_code' => $errorCode, 'error_data' => $errorData);
  	} */
/*   	$result = civicrm_api3('activity_type', 'get', array());
  	if(!empty($result["values"])) {
  		$weight = count($result["values"]);
  		if(!array_search("Public Holiday", $result["values"])) {
  			$weight = $weight+1;
  			$params = array(
  					'weight' => $weight,
  					'label' => 'Public Holiday',
  					'filter' => 0,
  					'is_active' => 1,
  					'is_optgroup' => 0,
  					'is_default' => 0,
  			);
  			$resultCreateActivityType = civicrm_api3('activity_type', 'create', $params);
  		}
  	
  		if(!array_search("Absence", $result["values"]) && array_key_exists("CiviTimesheet",$components)) {
  			$weight = $weight+1;
  			$params = array(
  					'weight' => $weight,
  					'label' => 'Absence',
  					'filter' => 0,
  					'is_active' => 1,
  					'is_optgroup' => 0,
  					'is_default' => 0,
  					'component_id' => $components["CiviTimesheet"]->componentID,
  			);
  			$resultCreateActivityType = civicrm_api3('activity_type', 'create', $params);
  		}
  	} */
  	// get API 
  	
  	
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

}
