<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.4                                                 |
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
    //Add job import navigation menu
    $weight = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Import Contacts', 'weight', 'name');
    $contactNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');
    $importJobNavigation = new CRM_Core_DAO_Navigation();
    $params = array (
      'domain_id'  => CRM_Core_Config::domainID(),
      'label'      => ts('Import Jobs'),
      'name'       => 'jobImport',
      'url'        => null,
      'parent_id'  => $contactNavId,
      'weight'     => $weight+1,
      'permission' => 'access HRJobs',
      'separator'  => 1,
      'is_active'  => 1
    );
    $importJobNavigation->copyValues($params);
    $importJobNavigation->save();
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

  public function upgrade_1400() {
    $this->ctx->log->info('Applying update 1400');
    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob', 'notice_amount_employee')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob ADD COLUMN notice_amount_employee double COMMENT "Amount of time allocated for notice period. Number part without the unit e.g 3 in 3 Weeks." AFTER notice_unit');
    }
    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob', 'notice_unit_employee')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob ADD COLUMN notice_unit_employee VARCHAR(63) COMMENT "Unit of a notice period assigned to a quantity e.g Week in 3 Weeks." AFTER notice_amount_employee');
    }

    //HR-397 -- Add Pay scale field
    $params = array(
      'name' => 'hrjob_pay_scale',
      'title' => 'Pay Scale',
      'is_active' => 1,
      'is_reserved' => 1,
    );
    civicrm_api3('OptionGroup', 'create', $params);
    $optionsValue = array('NJC pay scale', 'JNC pay scale', 'Soulbury Pay Agreement');
    foreach ($optionsValue as $key => $value) {
      $opValueParams = array(
        'option_group_id' => 'hrjob_pay_scale',
        'name' => $value,
        'label' => $value,
        'value' => $value,
      );
      civicrm_api3('OptionValue', 'create', $opValueParams);
    }
    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_pay', 'pay_scale')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_pay ADD COLUMN pay_scale VARCHAR(63) COMMENT "NJC pay scale, JNC pay scale, Soulbury Pay Agreement" AFTER job_id');
    }

    $i = 4;
    $params = array(
      'option_group_id' => 'hrjob_contract_type',
      'name' => 'Employee_Permanent',
      'weight' => $i,
      'label' => 'Employee - Permanent',
      'value' => 'Employee - Permanent',
    );
    civicrm_api3('OptionValue', 'create',$params);
    $empoption_id = civicrm_api3('OptionValue', 'getsingle', array('return' => "id",'option_group_id' => 'hrjob_contract_type', 'name' => "Employee"));
    civicrm_api3('OptionValue', 'create',array('id' => $empoption_id['id'],'name' => "Employee_Temporary",'label' => 'Employee - Temporary', 'value' => 'Employee - Temporary'));
    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjob_contract_type', 'id', 'name');

    foreach (array('Intern','Trustee','Volunteer') as $opName) {
      $i++;
      CRM_Core_DAO::executeQuery("UPDATE civicrm_option_value SET weight = {$i} WHERE name = '{$opName}' and option_group_id = {$optionGroupID}");
    }

    $optionGroupId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjob_hours_type', 'id', 'name');

    //change value of stored hours type
    CRM_Core_DAO::executeQuery("UPDATE civicrm_hrjob_hour SET hours_type = CASE hours_type WHEN 'full' THEN 8 WHEN 'part' THEN 4 WHEN 'casual' THEN 0 ELSE NULL END");
    $sql = "UPDATE civicrm_option_value SET civicrm_option_value.value = CASE civicrm_option_value.value WHEN 'full' THEN 8 WHEN 'part' THEN 4 WHEN 'casual' THEN 0 ELSE NULL END WHERE option_group_id = $optionGroupId";
    CRM_Core_DAO::executeQuery($sql);
    return TRUE;
  }

  public function upgrade_1401() {
    $this->ctx->log->info('Applying update 1401');
    $administerNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Dropdown Options', 'id', 'name');
    $params = array(
      'label'      => ts('Hours Types'),
      'name'       => 'hoursType',
      'url'        => 'civicrm/hour/editoption',
      'permission' => 'administer CiviCRM',
      'parent_id'  => $administerNavId,
      'is_active' => 1,
    );
    CRM_Core_BAO_Navigation::add($params);
    CRM_Core_BAO_Navigation::resetNavigation();
    return TRUE;
  }

  public function upgrade_1402() {
    $this->ctx->log->info('Applying update 1402');
    //Upgrade for HR-394 and HR-395
    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjob_region', 'id', 'name');
    if (!$optionGroupID) {
      $params = array(
        'name' => 'hrjob_region',
        'title' => 'Region',
        'is_active' => 1,
      );
      civicrm_api3('OptionGroup', 'create', $params);
    }
    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_role', 'role_hours_unit')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_role ADD COLUMN role_hours_unit VARCHAR(63) COMMENT "Period during which hours are allocated (eg 5 hours per day; 5 hours per week)" AFTER hours');
    }

    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_role', 'funder')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_role ADD COLUMN funder VARCHAR(127) COMMENT "FK to Contact ID" AFTER cost_center');

      CRM_Core_DAO::executeQuery("INSERT INTO civicrm_contact (display_name, contact_type)
        (SELECT distinct(organization), 'Organization'  FROM civicrm_hrjob_role chr
          WHERE NOT EXISTS (SELECT display_name
            FROM civicrm_contact WHERE display_name = chr.organization))");

      CRM_Core_DAO::executeQuery('UPDATE civicrm_hrjob_role chr SET funder = (SELECT id FROM civicrm_contact cc where chr.organization = cc.display_name)');
    }

    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_role', 'percent_pay_role')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_role ADD COLUMN percent_pay_role decimal(20,2)   DEFAULT 0 COMMENT "Percentage of Pay Assigned to this Role" AFTER funder');
    }

    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_role', 'percent_pay_funder')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_role ADD COLUMN percent_pay_funder varchar(127)   DEFAULT 0 COMMENT "Percentage of Pay Assigned to this funder" AFTER funder');
    }
    //IMP: Previous data will not be recorded HR-394
    if (CRM_Core_DAO::checkFieldExists('civicrm_hrjob_role', 'region')) {
      CRM_Core_DAO::executeQuery('UPDATE civicrm_hrjob_role chr SET region = NULL');
    }

    if (CRM_Core_DAO::checkFieldExists('civicrm_hrjob', 'is_tied_to_funding')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob DROP COLUMN is_tied_to_funding');
    }

    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_role', 'level_type')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_role ADD COLUMN level_type VARCHAR(63) COMMENT "Junior manager, senior manager, etc." AFTER department');
    }

    if (CRM_Core_DAO::checkFieldExists('civicrm_hrjob', 'funding_org_id')) {
      $dao = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_hrjob');
      while ($dao->fetch()) {
        $manager = $dao->manager_contact_id ? $dao->manager_contact_id : 'null';
        CRM_Core_DAO::executeQuery("INSERT INTO civicrm_hrjob_role (job_id, title, funder, location, department, manager_contact_id, level_type)
          VALUES ({$dao->id}, '{$dao->position}', IFNULL('{$dao->funding_org_id}', NULL), IFNULL('{$dao->location}',NULL), '{$dao->department}', {$manager}, IFNULL('{$dao->level_type}', NULL))");
      }
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob DROP FOREIGN KEY FK_civicrm_hrjob_funding_org_id');
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob DROP COLUMN funding_org_id');
    }

    if (CRM_Core_DAO::checkFieldExists('civicrm_hrjob', 'department')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob DROP COLUMN department');
    }
    if (CRM_Core_DAO::checkFieldExists('civicrm_hrjob', 'level_type')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob DROP COLUMN level_type');
    }
    if (CRM_Core_DAO::checkFieldExists('civicrm_hrjob', 'manager_contact_id')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob DROP FOREIGN KEY FK_civicrm_hrjob_manager_contact_id');
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob DROP manager_contact_id');
    }

    return TRUE;
  }

  public function upgrade_1403() {
    $this->ctx->log->info('Applying update 1403');
    if (CRM_Core_DAO::checkTableExists("civicrm_hrjob_hour")) {
      if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_hour', 'fte_num')) {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_hour ADD COLUMN fte_num int unsigned DEFAULT 1 COMMENT "." AFTER hours_fte');
      }
      if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_hour', 'fte_denom')) {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_hour ADD COLUMN fte_denom int unsigned DEFAULT 1 COMMENT "." AFTER fte_num');
      }
    }
    $result = civicrm_api3('HRJobHour', 'get');
    foreach ($result['values'] as $key => $value) {
      $fteFraction = CRM_HRJob_Upgrader::decToFraction($value['hours_fte']);
      CRM_Core_DAO::executeQuery("update civicrm_hrjob_hour set fte_num={$fteFraction[0]} , fte_denom={$fteFraction[1]} where id = {$value['id']}");
    }
    return TRUE;
  }

  function decToFraction($fte) {
    $fteDecimalPart = explode('.', $fte);
    $array  = str_split($fteDecimalPart[1]);
    $numerators = array(0, 1);
    $denominators = array(1, 0);
    $tempFte = $fte;
    $result= '';
    //check whether same value is repeating  in decimal like 3 is repeating in 0.33333 0.33 and have value in decimal more than 1
    if(count(array_unique($array)) == 1 && count($array) != 1) {
      $repeatNum = array_unique($array);
      $num = $repeatNum[0];
      $denom = 9;
      $gcd = CRM_HRJob_Upgrader::commonDivisor($num,$denom);
      $val = array($num/$gcd, $denom/$gcd);
      return $val;
    }
    else {
      for ($i = 2; $i < 1000; $i++) {
        $floorFte = floor($tempFte);
        $numerators[$i] = $floorFte * $numerators[$i-1] + $numerators[$i-2];
        $denominators[$i] = $floorFte * $denominators[$i-1] + $denominators[$i-2];
        $result = $numerators[$i] / $denominators[$i];
        if ((string)$result == (string)$fte) {
          $num = $numerators[$i];
          $denom = $denominators[$i];
          $val = array($num, $denom);
          return $val;
        }
        $tempFte = 1/($tempFte-$floorFte);
      }
    }
  }

  function commonDivisor($a,$b) {
    return ($a % $b) ? CRM_HRJob_Upgrader::commonDivisor($b,$a % $b) : $b;
  }

  public function upgrade_1404() {
    $this->ctx->log->info('Applying update 1404');

    $optionGroupId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjob_pay_grade', 'id', 'name');
    $sql = "UPDATE civicrm_option_value SET civicrm_option_value.value = CASE civicrm_option_value.value WHEN 'paid' THEN 1 WHEN 'unpaid' THEN 0 ELSE NULL END WHERE option_group_id = $optionGroupId";
    CRM_Core_DAO::executeQuery($sql);

    if (!CRM_Core_DAO::checkFieldExists('civicrm_hrjob_pay', 'is_paid')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_pay ADD COLUMN is_paid int unsigned DEFAULT 0 COMMENT "Paid, Unpaid, etc." AFTER pay_scale');
      CRM_Core_DAO::executeQuery("UPDATE civicrm_hrjob_pay SET is_paid = CASE pay_grade WHEN 'paid' THEN 1 WHEN 'unpaid' THEN 0 ELSE NULL END");
    }

    if (CRM_Core_DAO::checkFieldExists('civicrm_hrjob_pay', 'pay_grade')) {
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_hrjob_pay DROP COLUMN pay_grade');
    }
    CRM_Core_DAO::triggerRebuild();
    return TRUE;
  }

}
