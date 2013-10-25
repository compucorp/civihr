<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.0                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2013                                |
+--------------------------------------------------------------------+
| This file is a part of CiviCRM.                                    |
|                                                                    |
| CiviCRM is free software; you can copy, modify, and distribute it  |
| under the terms of the GNU Affero General Public License           |
| Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
|                                                                    |
| CiviCRM is distributed in the hope that it will be useful, but     |
| WITHOUT ANY WARRANTY; without even the implied warranty of         |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
| See the GNU Affero General Public License for more details.        |
|                                                                    |
| You should have received a copy of the GNU Affero General Public   |
| License and the CiviCRM Licensing Exception along                  |
| with this program; if not, contact CiviCRM LLC                     |
| at info[AT]civicrm[DOT]org. If you have questions about the        |
| GNU Affero General Public License or the licensing of CiviCRM,     |
| see the CiviCRM license FAQ at http://civicrm.org/licensing        |
+--------------------------------------------------------------------+
*/

/**
 * Collection of upgrade steps
 */
class CRM_HRJob_Upgrader extends CRM_HRJob_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
    // $this->executeCustomDataFile('xml/customdata.xml');
    // $this->executeSqlFile('sql/myinstall.sql');
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
  
  public function upgrade_4400() {
    $this->ctx->log->info('Applying update 4400');
  	if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_pay', 'pay_currency')) {
  	  CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_pay ADD COLUMN pay_currency VARCHAR(63) COMMENT "Job Pay Currency" AFTER pay_unit');
  	}
  	return TRUE;
  }

  public function upgrade_1101() {
    $this->ctx->log->info('Applying update 1101');
    $this->executeCustomDataFile('xml/1101_departments.xml');
    return TRUE;
  }

  public function upgrade_1102() {
    $this->ctx->log->info('Applying update 1102');
    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob', 'funding_org_id')) {
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_hrjob ADD COLUMN funding_org_id int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID', ADD CONSTRAINT `FK_civicrm_hrjob_funding_org_id` FOREIGN KEY (`funding_org_id`)  REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL");
    }
    return TRUE;
  }
  public function upgrade_4405() {
    $this->ctx->log->info('Applying update 4405');
    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_pension', 'pension_type')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_pension ADD COLUMN pension_type VARCHAR(63) COMMENT "Pension Type"');
    }
    if (!CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup','hrjob_pension_type', 'name')) {
      $this->executeCustomDataFile('xml/4405_pension_type.xml');
    }
    return TRUE;
  } 
  public function upgrade_4407() {
    $this->ctx->log->info('Applying update 4407');
    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_pension', 'ee_contrib_abs')) {
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_hrjob_pension ADD COLUMN ee_contrib_abs decimal(20,2) unsigned DEFAULT NULL COMMENT 'Employee Contribution Absolute Amount'");
    }
    return TRUE;
  }  
}
