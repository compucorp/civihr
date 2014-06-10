<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.3                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2014                                |
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

  public function upgrade_1100() {
    $this->ctx->log->info('Applying update 1100');
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
  public function upgrade_1103() {
    $this->ctx->log->info('Applying update 1103');
    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_health', 'provider_life_insurance')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_health ADD COLUMN provider_life_insurance VARCHAR(63) COMMENT "The organization or company which manages life insurance service"');
    }
    if(!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_health', 'plan_type_life_insurance')) {
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_hrjob_health ADD COLUMN plan_type_life_insurance  enum('Family','Individual') DEFAULT NULL COMMENT '.'");
    }
    if(!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_health', 'description_life_insurance')) {
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_hrjob_health ADD COLUMN description_life_insurance text DEFAULT NULL");
    }
    if(!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_health', 'dependents_life_insurance')) {
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_hrjob_health ADD COLUMN dependents_life_insurance text DEFAULT NULL");
    }
    if (!CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup','hrjob_life_provider', 'name')) {
      $this->executeCustomDataFile('xml/1103_life_provider.xml');
    }
    return TRUE;
  }
  public function upgrade_1105() {
    $this->ctx->log->info('Applying update 1105');
    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_pension', 'pension_type')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_pension ADD COLUMN pension_type VARCHAR(63) COMMENT "Pension Type"');
    }
    if (!CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup','hrjob_pension_type', 'name')) {
      $this->executeCustomDataFile('xml/1105_pension_type.xml');
    }
    return TRUE;
  }

  public function upgrade_1106() {
    $this->ctx->log->info('Applying update 1106');
    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_pension', 'ee_evidence_note')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_pension ADD COLUMN ee_evidence_note VARCHAR(127) COMMENT "Employee evidence note"');
    }
    return TRUE;
  }

  public function upgrade_1107() {
    $this->ctx->log->info('Applying update 1107');
    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_pension', 'ee_contrib_abs')) {
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_hrjob_pension ADD COLUMN ee_contrib_abs decimal(20,2) unsigned DEFAULT NULL COMMENT 'Employee Contribution Absolute Amount'");
    }
    return TRUE;
  }

  public function upgrade_1108() {
    $this->ctx->log->info('Applying update 1108');
    if (CRM_Core_DAO::checkFieldExists('civicrm_hrjob_health', 'provider') && CRM_Core_DAO::checkFieldExists('civicrm_hrjob_health', 'provider_life_insurance')) {
      $opt_grp_name = array(
        'hrjob_health_provider' => array(
          'name' => 'Health_Insurance_Provider',
          'label' => ts('Health Insurance Provider'),
          'column' => 'provider'
        ),
        'hrjob_life_provider' => array(
          'name' => 'Life_Insurance_Provider',
          'label' => ts('Life Insurance Provider'),
          'column' => 'provider_life_insurance'
        )
      );
      $org_id = array_search('Organization', CRM_Contact_BAO_ContactType::basicTypePairs(FALSE,'id'));
      $orgSubType = CRM_Contact_BAO_ContactType::subTypeInfo('Organization');

      foreach($opt_grp_name as $oKey => $oValue) {
        $subID = array_key_exists( $oValue['name'], $orgSubType );
        if(!$subID) {
          CRM_Contact_BAO_ContactType::add( array(
            'parent_id' => $org_id,
            'is_active' => 1,
            'name' => $oValue['name'],
            'label' => $oValue['label']
          ));
        }
        $options =  CRM_Core_OptionGroup::values($oKey, TRUE, FALSE);
        foreach($options as $orgKey => $orgValue) {
          $params = array(
            'organization_name' => $orgValue,
            'sort_name' => $orgValue,
            'display_name' => $orgValue,
            'legal_name' => $orgValue,
            'contact_type' => 'Organization',
            'contact_sub_type' => $oValue['name'],
          );
          $result = civicrm_api3('contact', 'create', $params);
          if($result['id']) {
            CRM_Core_DAO::executeQuery("UPDATE civicrm_hrjob_health SET {$oValue['column']} = {$result['id']} WHERE {$oValue['column']} LIKE '{$orgValue}'");
            CRM_Core_DAO::executeQuery("UPDATE civicrm_hrjob_health SET {$oValue['column']} = NULL WHERE {$oValue['column']} = ''");
          }
        }
        CRM_Core_OptionGroup::deleteAssoc($oKey);
      }
      CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjob_health`
        MODIFY COLUMN `provider` int(10) unsigned DEFAULT NULL,
        MODIFY COLUMN `provider_life_insurance` int(10) unsigned DEFAULT NULL,
        ADD CONSTRAINT `FK_civicrm_hrjob_health_provider` FOREIGN KEY (`provider`)  REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL,
        ADD CONSTRAINT `FK_civicrm_hrjob_health_provider_life_insurance` FOREIGN KEY (`provider_life_insurance`)  REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL");
    }
    return TRUE;
  }

  public function upgrade_1200() {
    $this->ctx->log->info('Applying update 1200');
    if (CRM_Core_DAO::checkTableExists("civicrm_hrjob_leave") && CRM_Core_DAO::checkTableExists("civicrm_hrabsence_type")) {
      CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjob_leave`
        MODIFY COLUMN `leave_type` int(10) unsigned DEFAULT NULL,
        ADD CONSTRAINT `FK_civicrm_hrjob_leave_leave_type` FOREIGN KEY (`leave_type`)  REFERENCES `civicrm_hrabsence_type`(`id`) ON DELETE SET NULL");
  	}
  	return TRUE;
  }

  public function upgrade_1201() {
    $this->ctx->log->info('Applying update 1201');

    //get all fields of Custom Group "HRJob_Summary"
    $params = array(
      'custom_group_id' => 'HRJob_Summary',
    );
    $results = civicrm_api3('CustomField', 'get', $params);

    foreach ($results['values'] as $result) {
      $result['is_view'] = 0; // make the field editable
      civicrm_api3('CustomField', 'create', $result);
    }

    //disable trigger
    CRM_Core_DAO::triggerRebuild();

    return TRUE;
  }

  public function upgrade_1202() {
    $this->ctx->log->info('Applying update 1202');

    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_pay', 'pay_annualized_est')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_pay ADD COLUMN pay_annualized_est decimal(40,2)   DEFAULT NULL COMMENT "Estimated Annual Pay" AFTER pay_currency');
    }

    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_pay', 'pay_is_auto_est')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_pay ADD COLUMN pay_is_auto_est tinyint   DEFAULT 1 COMMENT "Is the estimate automatically calculated" AFTER pay_annualized_est');
    }

    $defaults = array(
      'Year' => 1,
      'Month' => 12,
      'Week' => 50,
      'Day' => 50 * 5,
      'Hour' => 50 * 5 * 8,
    );
    foreach ($defaults as $unit => $multiple) {
      // See also: CRM_HRJob_Estimator::updateEstimate*
      // After HR-1.2.0 ships, don't make changes to the logic of upgrade_1202.
      CRM_Core_DAO::executeQuery('
        UPDATE civicrm_hrjob_pay p, civicrm_hrjob_hour h
        SET p.pay_annualized_est = %1 * h.hours_fte * p.pay_amount
        WHERE p.job_id = h.job_id
        AND p.pay_unit = %2
        AND p.pay_is_auto_est = 1
      ', array(
          1 => array($multiple, 'Float'),
          2 => array($unit, 'String'),
        )
      );
    }

    return TRUE;
  }

  public function upgrade_1301() {
    $this->ctx->log->info('Applying update 1301');
    if (CRM_Core_DAO::checkTableExists("civicrm_hrjob")) {
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_hrjob CHANGE period_type period_type VARCHAR( 63 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_hrjob CHANGE notice_unit notice_unit VARCHAR( 63 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
    }
    if (CRM_Core_DAO::checkTableExists("civicrm_hrjob_pay")) {
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_hrjob_pay CHANGE pay_unit pay_unit VARCHAR( 63 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
    }
    if (CRM_Core_DAO::checkTableExists("civicrm_hrjob_health")) {
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_hrjob_health CHANGE plan_type plan_type VARCHAR( 63 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_hrjob_health CHANGE plan_type_life_insurance plan_type_life_insurance VARCHAR( 63 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
    }
    if (CRM_Core_DAO::checkTableExists("civicrm_hrjob_hour")) {
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_hrjob_hour CHANGE hours_unit hours_unit VARCHAR( 63 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
    }
    return TRUE;
  }
}
