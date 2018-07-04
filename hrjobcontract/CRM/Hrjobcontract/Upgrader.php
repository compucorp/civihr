<?php

/**
 * Collection of upgrade steps
 */
class CRM_Hrjobcontract_Upgrader extends CRM_Hrjobcontract_Upgrader_Base {

  public function install() {
    // $this->executeCustomDataFile('xml/customdata.xml');
    $this->executeSqlFile('sql/install.sql');
    $this->upgradeBundle();
  }

  protected function checkTableExists(array $tables)
  {
      $result = array();

      foreach ($tables as $table)
      {
          $result[$table] = CRM_Core_DAO::checkTableExists($table);
      }

      return $result;
  }

  protected function populateTableWithEntity($tableName, $entity, array $fields, $revisionId)
  {
    $insertQuery = "INSERT INTO {$tableName} SET ";
    $insertParams = array(1 => array($revisionId, 'Integer'));

    foreach ($fields as $name => $type)
    {
        $value = $entity->{$name};
        if ($value !== null)
        {
            switch ($type)
            {
                case 'String':
                case 'Date':
                case 'Timestamp':
                    $value = '"' . $value . '"';
                break;
            }
        }
        else
        {
            $value = 'NULL';
        }

        $insertQuery .= "{$name} = {$value},";
    }
    $insertQuery .= "jobcontract_revision_id = %1";

    return CRM_Core_DAO::executeQuery($insertQuery, $insertParams);
  }

  public function getPayScaleId($payScale)
  {
    if (!$payScale)
    {
        return null;
    }

    $selectPayScaleQuery = 'SELECT id FROM civicrm_hrpay_scale WHERE pay_scale = %1 LIMIT 1';
    $selectPayScaleParams = array(
        1 => array($payScale, 'String'),
    );
    $payScaleResult = CRM_Core_DAO::executeQuery($selectPayScaleQuery, $selectPayScaleParams, false);

    $payScaleId = null;
    if ($payScaleResult->fetch())
    {
        $payScaleId = $payScaleResult->id;
    }
    else
    {
        $insertPayScaleQuery = 'INSERT INTO civicrm_hrpay_scale SET pay_scale = %1';
        CRM_Core_DAO::executeQuery($insertPayScaleQuery, $selectPayScaleParams, false);

        $payScaleId = (int)CRM_Core_DAO::singleValueQuery('SELECT LAST_INSERT_ID()');
    }

    return $payScaleId;
  }

  public function upgradeBundle() {
    //$this->ctx->log->info('Applying update 0999');
    $this->executeCustomDataFile('xml/option_group_install.xml');

    //$this->ctx->log->info('Applying update 1101');
    $this->executeCustomDataFile('xml/1101_departments.xml');

    //$this->ctx->log->info('Applying update 1105');
    if (!CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup','hrjc_pension_type', 'name')) {
      $this->executeCustomDataFile('xml/1105_pension_type.xml');
    }

    $i = 4;
    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjc_contract_type', 'id', 'name');
    foreach (array('Intern','Trustee','Volunteer') as $opName) {
      $i++;
      CRM_Core_DAO::executeQuery("UPDATE civicrm_option_value SET weight = {$i} WHERE name = '{$opName}' and option_group_id = {$optionGroupID}");
    }
    $optionGroupId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjc_hours_type', 'id', 'name');
    //change value of stored hours type
    CRM_Core_DAO::executeQuery("UPDATE civicrm_hrjobcontract_hour SET hours_type = CASE hours_type WHEN 'full' THEN 8 WHEN 'part' THEN 4 WHEN 'casual' THEN 0 ELSE hours_type END");

    //$this->ctx->log->info('Applying update 1402');
    //Upgrade for HR-394 and HR-395
    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjc_region', 'id', 'name');
    if (!$optionGroupID) {
      $params = array(
        'name' => 'hrjc_region',
        'title' => 'Region',
        'is_active' => 1,
      );
      $newRegionGroupResult = civicrm_api3('OptionGroup', 'create', $params);
    }

    $result = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_hrjobcontract_hour ORDER BY id ASC');
    while ($result->fetch())
    {
        $fteFraction = CRM_Hrjobcontract_Upgrader::decToFraction($result->hours_fte);
        CRM_Core_DAO::executeQuery("UPDATE civicrm_hrjobcontract_hour SET fte_num={$fteFraction[0]} , fte_denom={$fteFraction[1]} WHERE id = {$result->id}");
    }

    //$this->ctx->log->info('Applying update 1404');
    $optionGroupId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjc_pay_grade', 'id', 'name');
    $sql = "UPDATE civicrm_option_value SET civicrm_option_value.value = CASE civicrm_option_value.label WHEN 'Paid' THEN 1 WHEN 'Unpaid' THEN 0 END WHERE option_group_id = $optionGroupId";
    CRM_Core_DAO::executeQuery($sql);
    CRM_Core_DAO::triggerRebuild();

    $reportTemplateOptionGroup = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_option_group WHERE name='report_template' AND is_active = 1 LIMIT 1");
    if ($reportTemplateOptionGroup->fetch())
    {
        $hrjobcontractReportTemplateQuery = "INSERT INTO `civicrm_option_value` (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `domain_id`, `visibility_id`) VALUES
        (%1, 'JobContract Revision Report', 'hrjobcontract/summary', 'CRM_Hrjobcontract_Report_Form_Summary', NULL, 0, 0, 54, 'JobContract Revision Report', 0, 0, 1, NULL, NULL, NULL)";
        $hrjobcontractReportTemplateParams = array(
            1 => array($reportTemplateOptionGroup->id, 'Integer'),
        );
    CRM_Core_DAO::executeQuery($hrjobcontractReportTemplateQuery, $hrjobcontractReportTemplateParams);
    }

    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjobcontract_hour` ADD `location_type` INT(3) NULL DEFAULT NULL AFTER `id`");

    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjobcontract_hour` CHANGE `location_type` `location_standard_hours` INT(3) NULL DEFAULT NULL");

    CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS civicrm_hrhours_location");
    CRM_Core_DAO::executeQuery("
      CREATE TABLE IF NOT EXISTS `civicrm_hrhours_location` (
        `id` int(10) unsigned NOT NULL,
        `location` varchar(63) DEFAULT NULL,
        `standard_hours` int(4) DEFAULT NULL,
        `periodicity` varchar(63) DEFAULT NULL,
        `is_active` tinyint(4) DEFAULT '1'
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
    ");
    CRM_Core_DAO::executeQuery("
      INSERT INTO `civicrm_hrhours_location` (`id`, `location`, `standard_hours`, `periodicity`, `is_active`)
      VALUES (1, 'Head office', 40, 'Week', 1)
    ");

    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjc_revision_change_reason', 'id', 'name');
    if (!$optionGroupID) {
      $params = array(
        'name' => 'hrjc_revision_change_reason',
        'title' => 'Job Contract Revision Change Reason',
        'is_active' => 1,
        'is_reserved' => 1,
      );
      civicrm_api3('OptionGroup', 'create', $params);
    }

    CRM_Core_DAO::executeQuery("
        ALTER TABLE `civicrm_hrjobcontract_pay` ADD `annual_benefits` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `pay_is_auto_est`, ADD `annual_deductions` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `annual_benefits`
    ");

    // pay_cycle:
    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjc_pay_cycle', 'id', 'name');
    if (!$optionGroupID) {
        $params = array(
          'name' => 'hrjc_pay_cycle',
          'title' => 'Job Contract Pay Cycle',
          'is_active' => 1,
          'is_reserved' => 1,
        );
        civicrm_api3('OptionGroup', 'create', $params);
        $optionsValue = array(
            1 => 'Weekly',
            2 => 'Monthly',
        );
        foreach ($optionsValue as $key => $value) {
          $opValueParams = array(
            'option_group_id' => 'hrjc_pay_cycle',
            'name' => $value,
            'label' => $value,
            'value' => $key,
          );
          civicrm_api3('OptionValue', 'create', $opValueParams);
        }
    }

    // benefit_name:
    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjc_benefit_name', 'id', 'name');
    if (!$optionGroupID) {
        $params = array(
          'name' => 'hrjc_benefit_name',
          'title' => 'Job Contract Benefit Name',
          'is_active' => 1,
          'is_reserved' => 1,
        );
        civicrm_api3('OptionGroup', 'create', $params);
        $optionsValue = array(
            1 => 'Bike',
            2 => 'Medical',
        );
        foreach ($optionsValue as $key => $value) {
          $opValueParams = array(
            'option_group_id' => 'hrjc_benefit_name',
            'name' => $value,
            'label' => $value,
            'value' => $key,
          );
          civicrm_api3('OptionValue', 'create', $opValueParams);
        }
    }

    // benefit_type:
    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjc_benefit_type', 'id', 'name');
    if (!$optionGroupID) {
        $params = array(
          'name' => 'hrjc_benefit_type',
          'title' => 'Job Contract Benefit Type',
          'is_active' => 1,
          'is_reserved' => 1,
        );
        civicrm_api3('OptionGroup', 'create', $params);
        $optionsValue = array(
            1 => 'Fixed',
            2 => '%',
        );
        foreach ($optionsValue as $key => $value) {
          $opValueParams = array(
            'option_group_id' => 'hrjc_benefit_type',
            'name' => $value,
            'label' => $value,
            'value' => $key,
          );
          civicrm_api3('OptionValue', 'create', $opValueParams);
        }
    }

    // deduction_name:
    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjc_deduction_name', 'id', 'name');
    if (!$optionGroupID) {
        $params = array(
          'name' => 'hrjc_deduction_name',
          'title' => 'Job Contract Deduction Name',
          'is_active' => 1,
          'is_reserved' => 1,
        );
        civicrm_api3('OptionGroup', 'create', $params);
        $optionsValue = array(
            1 => 'Bike',
            2 => 'Medical',
        );
        foreach ($optionsValue as $key => $value) {
          $opValueParams = array(
            'option_group_id' => 'hrjc_deduction_name',
            'name' => $value,
            'label' => $value,
            'value' => $key,
          );
          civicrm_api3('OptionValue', 'create', $opValueParams);
        }
    }

    // deduction_type:
    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjc_deduction_type', 'id', 'name');
    if (!$optionGroupID) {
        $params = array(
          'name' => 'hrjc_deduction_type',
          'title' => 'Job Contract Deduction Type',
          'is_active' => 1,
          'is_reserved' => 1,
        );
        civicrm_api3('OptionGroup', 'create', $params);
        $optionsValue = array(
            1 => 'Fixed',
            2 => '%',
        );
        foreach ($optionsValue as $key => $value) {
          $opValueParams = array(
            'option_group_id' => 'hrjc_deduction_type',
            'name' => $value,
            'label' => $value,
            'value' => $key,
          );
          civicrm_api3('OptionValue', 'create', $opValueParams);
        }
    }

    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjobcontract_pay`  ADD `pay_cycle` INT(4) DEFAULT NULL  AFTER `annual_deductions`,  ADD `pay_per_cycle_gross` DECIMAL(10,2)  DEFAULT NULL  AFTER `pay_cycle`,  ADD `pay_per_cycle_net` DECIMAL(10,2)  DEFAULT NULL  AFTER `pay_per_cycle_gross`");

    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjobcontract_revision` ADD `editor_uid` INT(10) NULL DEFAULT NULL AFTER `jobcontract_id`");

    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjobcontract` ADD `deleted` INT(2) UNSIGNED NOT NULL DEFAULT '0'");
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjobcontract_revision` ADD `deleted` INT(2) UNSIGNED NOT NULL DEFAULT '0'");


    // Navigation items:
    CRM_Core_DAO::executeQuery("DELETE FROM `civicrm_navigation` WHERE name IN ('hoursType', 'pay_scale', 'hours_location', 'hrjc_contract_type', 'hrjc_location', 'hrjc_pay_cycle', 'hrjc_benefit_name', 'hrjc_benefit_type', 'hrjc_deduction_name', 'hrjc_deduction_type', 'hrjc_health_provider', 'hrjc_life_provider', 'hrjc_pension_type', 'hrjc_revision_change_reason')");
    // Add administer options
    $administerNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Dropdown Options', 'id', 'name');

    $jobContractOptionsMenuTree = array(
      array(
        'label'      => ts('Hours Types'),
        'name'       => 'hoursType',
        'url'        => 'civicrm/hour/editoption',
        'permission' => 'administer CiviCRM',
        'parent_id'  => $administerNavId,
      ),
      array(
        'label'      => ts('Job Contract Pay Scale'),
        'name'       => 'pay_scale',
        'url'        => 'civicrm/pay_scale',
        'permission' => 'administer CiviCRM',
        'parent_id'  => $administerNavId,
      ),
      array(
        'label'      => ts('Job Contract Hours/Location'),
        'name'       => 'hours_location',
        'url'        => 'civicrm/hours_location',
        'permission' => 'administer CiviCRM',
        'parent_id'  => $administerNavId,
      ),
    );

    // hrjc_contract_type:
    $result = civicrm_api3('OptionGroup', 'get', array(
      'sequential' => 1,
      'name' => "hrjc_contract_type",
    ));
    if (!empty($result['id'])) {
      $jobContractOptionsMenuTree[] = array(
        'label'      => ts('Contract Type'),
        'name'       => 'hrjc_contract_type',
        'url'        => 'civicrm/admin/options?gid=' . $result['id'],
        'permission' => 'administer CiviCRM',
        'parent_id'  => $administerNavId,
      );
    }

    // hrjc_location:
    $result = civicrm_api3('OptionGroup', 'get', array(
      'sequential' => 1,
      'name' => "hrjc_location",
    ));
    if (!empty($result['id'])) {
        $jobContractOptionsMenuTree[] = array(
          'label'      => ts('Normal place of work'),
          'name'       => 'hrjc_location',
          'url'        => 'civicrm/admin/options?gid=' . $result['id'],
          'permission' => 'administer CiviCRM',
          'parent_id'  => $administerNavId,
        );

    }

    // hrjc_pay_cycle:
    $result = civicrm_api3('OptionGroup', 'get', array(
      'sequential' => 1,
      'name' => "hrjc_pay_cycle",
    ));
    if (!empty($result['id'])) {
      $jobContractOptionsMenuTree[] = array(
        'label'      => ts('Pay cycle'),
        'name'       => 'hrjc_pay_cycle',
        'url'        => 'civicrm/admin/options?gid=' . $result['id'],
        'permission' => 'administer CiviCRM',
        'parent_id'  => $administerNavId,
      );
    }

    // hrjc_benefit_name:
    $result = civicrm_api3('OptionGroup', 'get', array(
      'sequential' => 1,
      'name' => "hrjc_benefit_name",
    ));
    if (!empty($result['id'])) {
      $jobContractOptionsMenuTree[] = array(
        'label'      => ts('Benefits'),
        'name'       => 'hrjc_benefit_name',
        'url'        => 'civicrm/admin/options?gid=' . $result['id'],
        'permission' => 'administer CiviCRM',
        'parent_id'  => $administerNavId,
      );
    }

    // hrjc_benefit_type:
    $result = civicrm_api3('OptionGroup', 'get', array(
      'sequential' => 1,
      'name' => "hrjc_benefit_type",
    ));
    if (!empty($result['id'])) {
      $jobContractOptionsMenuTree[] = array(
        'label'      => ts('Benefit type'),
        'name'       => 'hrjc_benefit_type',
        'url'        => 'civicrm/admin/options?gid=' . $result['id'],
        'permission' => 'administer CiviCRM',
        'parent_id'  => $administerNavId,
      );
    }

    // hrjc_deduction_name:
    $result = civicrm_api3('OptionGroup', 'get', array(
      'sequential' => 1,
      'name' => "hrjc_deduction_name",
    ));
    if (!empty($result['id'])) {
      $jobContractOptionsMenuTree[] = array(
        'label'      => ts('Deductions'),
        'name'       => 'hrjc_deduction_name',
        'url'        => 'civicrm/admin/options?gid=' . $result['id'],
        'permission' => 'administer CiviCRM',
        'parent_id'  => $administerNavId,
      );
    }

    // hrjc_deduction_type:
    $result = civicrm_api3('OptionGroup', 'get', array(
      'sequential' => 1,
      'name' => "hrjc_deduction_type",
    ));
    if (!empty($result['id'])) {
      $jobContractOptionsMenuTree[] = array(
        'label'      => ts('Deduction type'),
        'name'       => 'hrjc_deduction_type',
        'url'        => 'civicrm/admin/options?gid=' . $result['id'],
        'permission' => 'administer CiviCRM',
        'parent_id'  => $administerNavId,
      );
    }

    // hrjc_pension_type:
    $result = civicrm_api3('OptionGroup', 'get', array(
      'sequential' => 1,
      'name' => "hrjc_pension_type",
    ));
    if (!empty($result['id'])) {
      $jobContractOptionsMenuTree[] = array(
        'label'      => ts('Pension provider type'),
        'name'       => 'hrjc_pension_type',
        'url'        => 'civicrm/admin/options?gid=' . $result['id'],
        'permission' => 'administer CiviCRM',
        'parent_id'  => $administerNavId,
      );
    }

    // hrjc_revision_change_reason:
    $result = civicrm_api3('OptionGroup', 'get', array(
      'sequential' => 1,
      'name' => "hrjc_revision_change_reason",
    ));
    if (!empty($result['id'])) {
      $jobContractOptionsMenuTree[] = array(
        'label'      => ts('Reason for change'),
        'name'       => 'hrjc_revision_change_reason',
        'url'        => 'civicrm/admin/options?gid=' . $result['id'],
        'permission' => 'administer CiviCRM',
        'parent_id'  => $administerNavId,
      );
    }

    foreach ($jobContractOptionsMenuTree as $key => $menuItems) {
      $menuItems['is_active'] = 1;
      CRM_Core_BAO_Navigation::add($menuItems);
    }

    CRM_Core_BAO_Navigation::resetNavigation();

    $this->upgrade_1001();
    $this->upgrade_1002();
    $this->upgrade_1003();
    $this->upgrade_1004();
    $this->upgrade_1005();
    $this->upgrade_1006();
    $this->upgrade_1008();
    $this->upgrade_1009();
    $this->upgrade_1011();
    $this->upgrade_1012();
    $this->upgrade_1014();
    $this->upgrade_1015();
    $this->upgrade_1016();
    $this->upgrade_1017();
    $this->upgrade_1020();
    $this->upgrade_1025();
    $this->upgrade_1026();
    $this->upgrade_1027();
    $this->upgrade_1028();
    $this->upgrade_1029();
    $this->upgrade_1030();
    $this->upgrade_1032();
    $this->upgrade_1033();
    $this->upgrade_1034();
    $this->upgrade_1035();
    $this->upgrade_1036();
  }

  function upgrade_1001() {
    // Install JobContract Dates as Contact custom fields
    $this->executeCustomDataFile('xml/jobcontract_dates.xml');

    return TRUE;
  }

  function upgrade_1002() {
    // Fill JobContract Dates custom fields with start and end dates of contracts
    $jobContracts = CRM_Core_DAO::executeQuery(
        'SELECT id, contact_id FROM civicrm_hrjobcontract ORDER BY id ASC'
    );

    $today = date('Y-m-d');
    while ($jobContracts->fetch())
    {
        $revision = CRM_Core_DAO::executeQuery(
            'SELECT * FROM civicrm_hrjobcontract_revision '
            . 'WHERE jobcontract_id = %1 '
            . 'AND effective_date <= %2 '
            . 'AND deleted = 0 '
            . 'ORDER BY effective_date DESC LIMIT 1',
            array(
                1 => array($jobContracts->id, 'Integer'),
                2 => array($today, 'String'),
            )
        );
        if (!$revision->fetch())
        {
            $revision = CRM_Core_DAO::executeQuery(
                'SELECT details_revision_id FROM civicrm_hrjobcontract_revision '
                . 'WHERE jobcontract_id = %1 '
                . 'AND deleted = 0 '
                . 'ORDER BY effective_date ASC, id DESC LIMIT 1',
                array(
                    1 => array($jobContracts->id, 'Integer'),
                )
            );
            $revision->fetch();
        }

        if (!$revision->details_revision_id)
        {
            continue;
        }

        $details = CRM_Core_DAO::executeQuery(
            'SELECT period_start_date, period_end_date FROM civicrm_hrjobcontract_details '
            . 'WHERE jobcontract_revision_id = %1 '
            . 'LIMIT 1',
            array(
                1 => array($revision->details_revision_id, 'Integer'),
            )
        );
        if ($details->fetch())
        {
            CRM_Hrjobcontract_JobContractDates::setDates($jobContracts->contact_id, $jobContracts->id, $details->period_start_date, $details->period_end_date);
        }
    }

    return TRUE;
  }

  function upgrade_1003() {
    CRM_Core_DAO::executeQuery("DELETE FROM `civicrm_option_value` WHERE name = 'CRM_Hrjobcontract_Report_Form_Summary'");
    $reportTemplateOptionGroup = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_option_group WHERE name='report_template' AND is_active = 1 LIMIT 1");
    if ($reportTemplateOptionGroup->fetch())
    {
        $hrjobcontractReportTemplateQuery = "INSERT INTO `civicrm_option_value` (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `domain_id`, `visibility_id`) VALUES
        (%1, 'JobContract Revision Report', 'hrjobcontract/summary', 'CRM_Hrjobcontract_Report_Form_Summary', NULL, 0, 0, 54, 'JobContract Revision Report', 0, 0, 1, NULL, NULL, NULL)";
        $hrjobcontractReportTemplateParams = array(
            1 => array($reportTemplateOptionGroup->id, 'Integer'),
        );
        CRM_Core_DAO::executeQuery($hrjobcontractReportTemplateQuery, $hrjobcontractReportTemplateParams);
    }
    return TRUE;
  }

  function upgrade_1004() {
    $jobcontractDatesCustomGroup = CRM_Core_DAO::executeQuery("SELECT id FROM `civicrm_custom_group` WHERE name='HRJobContract_Dates' AND is_active = 1 LIMIT 1");
    if ($jobcontractDatesCustomGroup->fetch())
    {
      CRM_Core_DAO::executeQuery("UPDATE `civicrm_custom_group` SET is_active = 0 WHERE name = 'HRJobContract_Dates'");
      $jobcontractDatesCustomFieldQuery = "UPDATE `civicrm_custom_field` SET is_required = 0 WHERE custom_group_id = %1 AND name = 'Contract_ID'";
      $jobcontractDatesCustomFieldParams = array(
          1 => array($jobcontractDatesCustomGroup->id, 'Integer'),
      );
      CRM_Core_DAO::executeQuery($jobcontractDatesCustomFieldQuery, $jobcontractDatesCustomFieldParams);
    }
    return TRUE;
  }

  function upgrade_1005() {
      CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjobcontract_leave` CHANGE `leave_amount` `leave_amount` DECIMAL( 8, 2 ) UNSIGNED NULL DEFAULT NULL COMMENT 'The number of leave days'");
      return TRUE;
  }

  /**
   * Populates old Job Summary dates into Job Contract period_start_date and period_end_date fields.
   *
   * @return boolean
   */
  function upgrade_1006() {
    $tableExists = $this->checkTableExists(array(
        'civicrm_value_job_summary_10',
    ));
    if (!$tableExists['civicrm_value_job_summary_10']) {
        return TRUE;
    }
    $jobSummary = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_value_job_summary_10 ORDER BY entity_id ASC");
    while ($jobSummary->fetch()) {
      $jobContractSummaryDates = array(
        'startDate' => $jobSummary->initial_join_date_56 ? date('Y-m-d', strtotime($jobSummary->initial_join_date_56)) : null,
        'endDate' => $jobSummary->final_termination_date_57 ? date('Y-m-d', strtotime($jobSummary->final_termination_date_57)) : null,
      );
      $jobContractsResult = civicrm_api3('HRJobContract', 'get', array(
        'sequential' => 1,
        'contact_id' => $jobSummary->entity_id,
        'return' => "period_start_date,period_end_date",
      ));
      foreach ($jobContractsResult['values'] as $jobContract) {
        if ($jobContract['is_current'] && !$jobContract['deleted']) {
          $createParams = array(
            'sequential' => 1,
            'jobcontract_id' => $jobContract['id'],
          );
          if (empty($jobContract['period_start_date']) && !empty($jobContractSummaryDates['startDate'])) {
            $createParams['period_start_date'] = $jobContractSummaryDates['startDate'];
          }
          if (
            (
                empty($jobContract['period_end_date']) ||
                ($jobContract['period_end_date'] > $jobContractSummaryDates['endDate'])
            )
            && !empty($jobContractSummaryDates['endDate'])
           ) {
            $createParams['period_end_date'] = $jobContractSummaryDates['endDate'];
          }
          $result = civicrm_api3('HRJobDetails', 'create', $createParams);
        }
      }
    }

    return TRUE;
  }

  function upgrade_1008() {
      CRM_Core_DAO::executeQuery("ALTER TABLE  `civicrm_hrhours_location` CHANGE  `standard_hours`  `standard_hours` DECIMAL( 8, 2 ) NULL DEFAULT NULL");
      return TRUE;
  }

  function upgrade_1009() {
      CRM_Core_DAO::executeQuery("ALTER TABLE  `civicrm_hrjobcontract_leave` CHANGE  `leave_amount`  `leave_amount` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT  'The number of leave days'");
      return TRUE;
  }

  function upgrade_1010() {
      CRM_Hrjobcontract_JobContractDates::rewriteContactIds();
      return TRUE;
  }

  /**
   * Adding 'end_reason' field for Job Contract Details,
   * 'hrjc_contract_end_reason' Option Group with three Option Values
   * and Administration Menu entry for managing the Option Group values.
   *
   * @return boolean TRUE
   */
  function upgrade_1011() {
    // Adding 'end_reason' field into the 'civicrm_hrjobcontract_details' db table.
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjobcontract_details` ADD `end_reason` INT(3) NULL DEFAULT NULL AFTER `period_end_date`");

    // Creating Option Group named 'hrjc_contract_end_reason' for storing Contract End reason values.
    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjc_contract_end_reason', 'id', 'name');
    if (!$optionGroupID) {
        $params = array(
          'name' => 'hrjc_contract_end_reason',
          'title' => 'Job Contract End Reason',
          'is_active' => 1,
          'is_reserved' => 1,
        );
        civicrm_api3('OptionGroup', 'create', $params);
        // An array with three detault Contract End reasons:
        $optionsValue = array(
            1 => 'Voluntary',
            2 => 'Involuntary',
            3 => 'Planned',
        );
        // Attaching the Option Values to the Option Group.
        foreach ($optionsValue as $key => $value) {
          $opValueParams = array(
            'option_group_id' => 'hrjc_contract_end_reason',
            'name' => $value,
            'label' => $value,
            'value' => $key,
          );
          civicrm_api3('OptionValue', 'create', $opValueParams);
        }
    }

    // Adding newly created Option Group into the Administration Menu (Dropdown Options).
    $administerNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Dropdown Options', 'id', 'name');
    $jobContractOptionsMenuTree = array();
    $result = civicrm_api3('OptionGroup', 'get', array(
      'sequential' => 1,
      'name' => "hrjc_contract_end_reason",
    ));
    if (!empty($result['id'])) {
      $jobContractOptionsMenuTree[] = array(
        'label'      => ts('Reason for Job Contract end'),
        'name'       => 'hrjc_contract_end_reason',
        'url'        => 'civicrm/admin/options?gid=' . $result['id'],
        'permission' => 'administer CiviCRM',
        'parent_id'  => $administerNavId,
      );
    }
    foreach ($jobContractOptionsMenuTree as $key => $menuItems) {
      $menuItems['is_active'] = 1;
      CRM_Core_BAO_Navigation::add($menuItems);
    }

    // Refreshing the Navigation menu.
    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }

  /**
   * Install 'length_of_service' Custom Field for 'Individual' Contact entity.
   */
  function upgrade_1012() {
      $this->executeCustomDataFile('xml/length_of_service.xml');
      return TRUE;
  }

  /**
   * Add 'effective_end_date' field to Job Contract Revisions table
   * and generate effective end date values for current Job Contracts Revisions.
   * Also add 'overrided' field to Job Contract Revisions table
   * telling if the revision is overrided by the other one with the same
   * 'effective_date' value.
   *
   * @return TRUE
   */
  function upgrade_1013() {
    // Adding 'effective_end_date' field to 'civicrm_hrjobcontract_revision' table.
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjobcontract_revision` ADD `effective_end_date` DATE NULL DEFAULT NULL AFTER `effective_date`");
    // Adding 'overrided' field to 'civicrm_hrjobcontract_revision' table.
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjobcontract_revision` ADD `overrided` BOOLEAN NOT NULL DEFAULT FALSE AFTER `deleted`");

    // Filling 'effective_end_date' column for each existing Job Contract.
    $query = "SELECT id FROM civicrm_hrjobcontract ORDER BY id ASC";
    $jobcontracts = CRM_Core_DAO::executeQuery($query);
    while ($jobcontracts->fetch()) {
        CRM_Hrjobcontract_BAO_HRJobContractRevision::updateEffectiveEndDates($jobcontracts->id);
    }
    return TRUE;
  }

  /**
   * Create a CiviCRM daily scheduled job which updates Contacts length of service values.
   *
   * @return TRUE
   */
  function upgrade_1014() {
    $dao = new CRM_Core_DAO_Job();
    $dao->api_entity = 'HRJobContract';
    $dao->api_action = 'updatelengthofservice';
    $dao->find(TRUE);
    if (!$dao->id)
    {
      $dao = new CRM_Core_DAO_Job();
      $dao->domain_id = CRM_Core_Config::domainID();
      $dao->run_frequency = 'Daily';
      $dao->parameters = null;
      $dao->name = 'Length of service updater';
      $dao->description = 'Updates Length of service value for each Contact';
      $dao->api_entity = 'HRJobContract';
      $dao->api_action = 'updatelengthofservice';
      $dao->is_active = 1;
      $dao->save();
    }
    return TRUE;
  }

  function upgrade_1015() {
    // Adding 'add_public_holidays' field to 'civicrm_hrjobcontract_leave' table.
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjobcontract_leave` ADD `add_public_holidays` TINYINT NOT NULL DEFAULT 0");

    return true;
  }

  function upgrade_1016() {
    /* on civicrm 4.7.7 this activity type (Contact Deleted by Merge) is not created
   * as a part of civicrm installation but it should be, since it's used in
   * contact merge code in core civicrm files. So here we just insure that it will
   * be created for existing installations.
   */
    try {
      $result = civicrm_api3('OptionValue', 'getsingle', array(
        'sequential' => 1,
        'name' => "Contact Deleted by Merge",
      ));
      $is_error = !empty($result['is_error']);
    } catch (CiviCRM_API3_Exception $e) {
      $is_error = true;
    }

    if ($is_error)  {
      civicrm_api3('OptionValue', 'create', array(
        'sequential' => 1,
        'option_group_id' => "activity_type",
        'name' => "Contact Deleted by Merge",
        'label' => "Contact Deleted by Merge",
        'filter' => 1,
        'description' => "Contact was merged into another contact",
        'is_reserved' => 1,
      ));
    }
    return true;
  }

  /**
   * Create job contract import/export navigation menus
   *
   */
  function upgrade_1017() {
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_navigation WHERE name IN ('job_contracts', 'import_export_job_contracts')");

    $contactsNavID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');
    $importContactWeight = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Import Contacts', 'weight', 'name');
    $params = [
      'name' => 'import_export_job_contracts',
      'label' => ts('Import / Export'),
      'url' => NULL,
      'parent_id' => $contactsNavID,
      'is_active' => TRUE,
      'weight' => $importContactWeight,
      'permission' => 'access HRJobs',
      'domain_id' => CRM_Core_Config::domainID(),
    ];
    $navigation = new CRM_Core_DAO_Navigation();
    $navigation->copyValues($params);
    $importExportMenu = $navigation->save();

    if (!empty($importExportMenu->id)) {
      $toCreate = [
        [
          'name' => 'import_job_contracts',
          'label' => ts('Import Job Contracts'),
          'url' => "civicrm/job/import",
          'parent_id' => $importExportMenu->id,
          'is_active' => TRUE,
          'permission' => [
            'access HRJobs',
          ]
        ]
      ];
      foreach($toCreate as $item) {
        CRM_Core_BAO_Navigation::add($item);
      }
    }

    return true;
  }

  /**
   * Remove (Job Contract Report) menu item if exist
   *
   */
  function upgrade_1018() {
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_navigation WHERE name IN ('export_job_contracts')");
    CRM_Core_BAO_Navigation::resetNavigation();

    return true;
  }

  /**
   * Upgrade Length of Service values.
   *
   * @return TRUE
   */
  function upgrade_1019() {
    CRM_Hrjobcontract_BAO_HRJobContract::updateLengthOfServiceAllContacts();

    return true;
  }


  /**
   * Create civicrm_hrpay_scale table and its default data if it is not exist
   *
   */
  function upgrade_1020() {
    CRM_Core_DAO::executeQuery("
        CREATE TABLE IF NOT EXISTS `civicrm_hrpay_scale` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `pay_scale` VARCHAR(63) DEFAULT NULL,
          `pay_grade` VARCHAR(63) DEFAULT NULL,
          `currency` VARCHAR(63) DEFAULT NULL,
          `amount` DECIMAL(10,2) DEFAULT NULL,
          `periodicity` VARCHAR(63) DEFAULT NULL,
          `is_active` tinyint(4) DEFAULT '1',
          PRIMARY KEY(id)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
      ");

    $data = CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) FROM `civicrm_hrpay_scale`");

    if (empty($data)) {
      CRM_Core_DAO::executeQuery("
        INSERT INTO `civicrm_hrpay_scale` (`pay_scale`, `pay_grade`, `currency`, `amount`, `periodicity`, `is_active`)
        VALUES ('Not Applicable', NULL, NULL, NULL, NULL, 1)
    ");
    }

    return true;
  }

  /**
   * Remove unnecessary constraints from hrjobcontract entity tables.
   *
   * @return TRUE
   */
  function upgrade_1022() {
    $constraints = [
      'FK_civicrm_hrjobcontract_contact_id' => 'civicrm_hrjobcontract',
      'FK_civicrm_hrjobcontract_revision_jobcontract_id' => 'civicrm_hrjobcontract_revision',
      'FK_civicrm_hrjobcontract_details_contract_revision_id' => 'civicrm_hrjobcontract_details',
      'FK_civicrm_hrjobcontract_details_jobcontract_revision_id' => 'civicrm_hrjobcontract_details',
      'FK_civicrm_hrjobcontract_health_jobcontract_revision_id' => 'civicrm_hrjobcontract_health',
      'FK_civicrm_hrjobcontract_hour_jobcontract_revision_id' => 'civicrm_hrjobcontract_hour',
      'FK_civicrm_hrjobcontract_leave_jobcontract_revision_id' => 'civicrm_hrjobcontract_leave',
      'FK_civicrm_hrjobcontract_pay_jobcontract_revision_id' => 'civicrm_hrjobcontract_pay',
      'FK_civicrm_hrjobcontract_pension_jobcontract_revision_id' => 'civicrm_hrjobcontract_pension',
      'FK_civicrm_hrjobcontract_role_jobcontract_revision_id' => 'civicrm_hrjobcontract_role',
    ];
    $messages = [];

    foreach ($constraints as $index => $table) {
      // We use try/catch block because removing constraint which doesn't exist
      // causes an exception throwing which breaks the code execution.
      try {
        CRM_Core_DAO::executeQuery(
          "ALTER TABLE `{$table}` DROP FOREIGN KEY `$index`"
        );
      } catch (Exception $e) {
        $messages[] = "Error while removing {$index} key from {$table} table: " . $e->getMessage();
      }
    }

    if (!empty($messages) && function_exists('drupal_set_message')) {
      foreach ($messages as $message) {
        drupal_set_message($message);
      }
    }

    return TRUE;
  }

  /**
   * Changes `change reason` field type, from integer to string, to support
   * custom values
   *
   * @return TRUE
   */
  function upgrade_1023() {
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_hrjobcontract_revision` CHANGE `change_reason` `change_reason` VARCHAR(512) NULL DEFAULT NULL;");
    CRM_Core_BAO_Navigation::resetNavigation();

    return true;
  }

  /**
   * Creates Option Group and Option Values for Health an Life Insurance Plan
   * Types.
   *
   * @return boolean TRUE
   */
  public function upgrade_1025() {
    $this->createInsurancePlanTypes();
    return TRUE;
  }

  /**
   * Alters civicrm_hrjobcontract_hour table to make default values for fte_num
   * and fte_denom equal to 0.
   *
   * @return boolean
   *   True on success
   */
  public function upgrade_1026() {
    $query = "
      ALTER TABLE `civicrm_hrjobcontract_hour`
      CHANGE `fte_num` `fte_num` INT(10) UNSIGNED NULL DEFAULT '0' COMMENT '.',
      CHANGE `fte_denom` `fte_denom` INT(10) UNSIGNED NULL DEFAULT '0' COMMENT '.'
    ";
    CRM_Core_DAO::executeQuery($query);
    return true;
  }

  /**
   * Add options to choose bi-monthly or bi-weekly for pay cycle
   *
   * @return bool
   */
  public function upgrade_1027() {
    $optionsValue = [
      3 => 'Bi-Weekly',
      4 => 'Bi-Monthly',
    ];
    foreach ($optionsValue as $value => $name) {
      $opValueParams = [
        'option_group_id' => 'hrjc_pay_cycle',
        'name' => $name,
        'label' => $name,
        'value' => $value
      ];
      civicrm_api3('OptionValue', 'create', $opValueParams);
    }

    return TRUE;
  }

  /**
   * Changes Pension Provider to be a contact, instead of an option_value.  All
   * required changes are wrapped in a transaction and rolled-back if any
   * problems arise.
   *
   * @return boolean
   *   true on success
   *
   * @throws Exception
   */
  function upgrade_1028() {
    $pensionsTableName = CRM_Hrjobcontract_BAO_HRJobPension::getTableName();

    $tx = new CRM_Core_Transaction();
    try {
      $this->createPensionProviderType();
      $this->replacePensionProviders($pensionsTableName);
      $this->removePensionTypeOptionGroup();
    } catch (Exception $ex) {
      $tx->rollback();
      throw new Exception('Cannot do upgrade - ' . $ex->getMessage());
    }

    $this->alterPensionsTable($pensionsTableName);

    return true;
  }

  /**
   * Upgrader to :
   *
   * - Remove unused pay scales except 'Not Applicable'
   * - Remove unused Hour Locations except 'Head Office'
   * - Remove Duplicated 'Employee - Permanent' Contract Type
   * - Add 'Fixed Term' Contract Type
   * - Sort contract types alphabetically
   * - Add 'Retirement' contract end reason
   * - Add new contract change reason options
   *
   * @return TRUE
   */
  public function upgrade_1029() {
    $this->up1029_removeDuplicateContractType();

    $optionValues = [
      'hrjc_contract_type' => ['Fixed Term'],
      'hrjc_contract_end_reason' => ['Retirement'],
      'hrjc_revision_change_reason' => ['Promotion', 'Increment', 'Disciplinary'],
    ];

    foreach ($optionValues as $optionGroup => $values) {
      $this->addOptionValues($optionGroup, $values);
    }

    $this->up1029_sortContractTypes();

    return true;
  }

  /**
   * Makes Hour Location id field autonumeric and adds id as a primary key.
   */
  public function upgrade_1030() {
    try {
      CRM_Core_DAO::executeQuery('ALTER TABLE `civicrm_hrhours_location` ADD PRIMARY KEY (`id`)');
      CRM_Core_DAO::executeQuery('ALTER TABLE `civicrm_hrhours_location` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT');
    } catch (PEAR_Exception $e) {
      $isDuplicatePrimaryKeyException = stripos($e->getCause()->userinfo, 'nativecode=1068');
      if ($isDuplicatePrimaryKeyException === false) {
        throw new Exception($e->getMessage() . ' - ' . $e->getCause()->userinfo);
      }
    }

    return true;
  }

  /**
   * Concats data in pay_grade field to pay_scale fieldand removes pay_grade
   * field from datbase.
   */
  public function upgrade_1031() {
    $query = "
      UPDATE civicrm_hrpay_scale
      SET pay_scale = CONCAT(pay_scale, ' - ', pay_grade)
      WHERE pay_scale NOT LIKE 'Not Applicable'
    ";
    CRM_Core_DAO::executeQuery($query);

    $dropQuery = 'ALTER TABLE `civicrm_hrpay_scale` DROP `pay_grade`';
    CRM_Core_DAO::executeQuery($dropQuery);

    return TRUE;
  }

  /**
   * Rename the 'periodicity' column
   *
   * @return bool
   */
  public function upgrade_1032() {
    $query = "ALTER TABLE civicrm_hrpay_scale CHANGE periodicity pay_frequency VARCHAR(63)";
    CRM_Core_DAO::executeQuery($query);

    return TRUE;
  }

  /**
   * Removes menu link pointing to an nonexistent option group and update
   * dropdown links to use option group names instead of IDs.
   *
   * @return bool
   */
  public function upgrade_1033() {
    $this->deletePensionTypeDropdownMenu();
    $this->updateDropdownMenuItemsLinkToUseOptionGroupName();

    return TRUE;
  }

  /**
   * Update CustomGroup, setting Contact_Length_Of_Service is_reserved to Yes
   *
   * @return bool
   */
  public function upgrade_1034() {
    $result = civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'return' => ['id'],
      'name' => 'Contact_Length_Of_Service',
    ]);

    civicrm_api3('CustomGroup', 'create', [
      'id' => $result['id'],
      'is_reserved' => 1,
    ]);

    return TRUE;
  }

  /**
   * Upgrade CustomGroup, setting HRJobContract_Dates, HRJob_Summary and
   * HRJobContract_Summary is_reserved value to Yes if it is existing.
   *
   * @return bool
   */
  public function upgrade_1035() {
    $customGroups = [
      'HRJobContract_Dates',
      'HRJob_Summary',
      'HRJobContract_Summary'
    ];
    
    $result = civicrm_api3('CustomGroup', 'get', [
      'return' => ['id', 'name'],
      'name' => ['IN' => $customGroups],
    ]);

    if ($result['count'] > 0) {
      foreach ($result['values'] as $value) {
        $params = ['id' => $value['id'], 'is_reserved' => 1];

        /**
         * 'is_multiple' is added to prevent bug that changes it to false
         * @see https://issues.civicrm.org/jira/browse/CRM-21853
         */
        if ($value['name'] === 'HRJobContract_Dates') {
          $params['is_multiple'] = 1;
        }

        civicrm_api3('CustomGroup', 'create', $params);
      }
    }

    return TRUE;
  }

  /**
   * Adds a submenu containing links to edit job contract option groups
   *
   * @return bool
   */
  public function upgrade_1036() {
    $domain = CRM_Core_Config::domainID();
    $params = ['return' => 'id', 'name' => 'Administer', 'domain_id' => $domain];
    $administerId = (int) civicrm_api3('Navigation', 'getvalue', $params);

    $permission = 'access CiviCRM';
    $parent = $this->createNavItem('Job Contract', $permission, $administerId);
    $parentId = $parent['id'];

    // Weight cannot be set when creating for the first time
    civicrm_api3('Navigation', 'create', ['id' => $parentId, 'weight' => -100]);

    // If we don't flush it will not recognize newly created parent_id
    CRM_Core_PseudoConstant::flush();

    // returns the link to an option group edit page
    $optGroupLinker = function ($groupName) {
      return 'civicrm/admin/options/' . $groupName . '?reset=1';
    };

    $childLinks = [
      'Contract Types' => $optGroupLinker('hrjc_contract_type'),
      'Normal Places of Work' => $optGroupLinker('hrjc_location'),
      'Contract End Reasons' => $optGroupLinker('hrjc_contract_end_reason'),
      'Contract Revision Reasons' => $optGroupLinker('hrjc_revision_change_reason'),
      'Standard Full Time Hours' => 'civicrm/hours_location',
      'Pay Scales' => 'civicrm/pay_scale',
      'Benefits' => $optGroupLinker('hrjc_benefit_name'),
      'Deductions' => $optGroupLinker('hrjc_deduction_name'),
      'Insurance Plan Types' => $optGroupLinker('hrjc_insurance_plantype'),
    ];

    foreach ($childLinks as $itemName => $link) {
      $this->createNavItem($itemName, $permission, $parentId, ['url' => $link]);
    }

    return TRUE;
  }

  /**
   * Creates a navigation menu item using the API
   *
   * @param string $name
   * @param string $permission
   * @param int $parentID
   * @param array $params
   *
   * @return array
   */
  private function createNavItem($name, $permission, $parentID, $params = []) {
    $params = array_merge([
      'name' => $name,
      'label' => ts($name),
      'permission' => $permission,
      'parent_id' => $parentID,
      'is_active' => 1,
    ], $params);

    $existing = civicrm_api3('Navigation', 'get', $params);

    if ($existing['count'] > 0) {
      return array_shift($existing['values']);
    }

    return civicrm_api3('Navigation', 'create', $params);
  }

  /**
   * Removes the "Pension Type" item from the
   *  "Administer -> Customize Data and Screens -> Dropdowns" menu
   *
   * The option group this menu item links to has been removed by PCHR-1820,
   * but the menu item itself wasn't, so we're deleting it now.
   *
   * @return bool
   */
  private function deletePensionTypeDropdownMenu() {
    civicrm_api3('Navigation', 'get', [
      'name' => 'hrjc_pension_type',
      'url' => ['LIKE' => 'civicrm/admin/options?gid%'],
      'api.Navigation.delete' => ['id' => '$value.id'],
    ]);

    CRM_Core_BAO_Navigation::resetNavigation();
  }

  /**
   * Update the URLs of all menu items pointing to Job Contracts options under
   * the "Administer -> Customize Data and Screens -> Dropdowns" menu to have
   * the option group name instead of its ID
   *
   * @return bool
   */
  private function updateDropdownMenuItemsLinkToUseOptionGroupName() {
    $dropdownMenuItems = [
      'hrjc_contract_type',
      'hrjc_location',
      'hrjc_pay_cycle',
      'hrjc_benefit_name',
      'hrjc_benefit_type',
      'hrjc_deduction_name',
      'hrjc_deduction_type',
      'hrjc_revision_change_reason',
      'hrjc_contract_end_reason',
    ];

    foreach ($dropdownMenuItems as $menuItem) {
      civicrm_api3('Navigation', 'get', [
        'name' => $menuItem,
        'url' => ['LIKE' => 'civicrm/admin/options?gid%'],
        'api.Navigation.create' => [
          'id' => '$value.id',
          'url' => "civicrm/admin/options/{$menuItem}?reset=1"
        ],
      ]);
    }

    CRM_Core_BAO_Navigation::resetNavigation();
  }

  /**
   * Alters pension_type column to be an unsigned integer and adds a foreign
   * key referencing civicrm_contact.
   *
   * @param type $pensionsTableName
   *   Name of pension data table
   *
   * @throws Exception
   */
  private function alterPensionsTable($pensionsTableName) {
    try {
      CRM_Core_DAO::executeQuery("
        ALTER TABLE $pensionsTableName
        MODIFY pension_type INT(10) unsigned,
        ADD CONSTRAINT pension_type_contact_id_fk
        FOREIGN KEY(pension_type)
        REFERENCES civicrm_contact(id)
        ON DELETE SET NULL ON UPDATE CASCADE;
      ");
    } catch (Exception $e) {
      $civiDBSettings = parse_url(CIVICRM_DSN);
      $civiDBName = trim($civiDBSettings['path'], '/');

      $fkCheck = CRM_Core_DAO::executeQuery($q = "
        SELECT *
        FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = '$civiDBName'
        AND TABLE_NAME = '$pensionsTableName'
        AND REFERENCED_TABLE_NAME = 'civicrm_contact'
        AND CONSTRAINT_NAME = 'pension_type_contact_id_fk'
      ");

      if ($fkCheck->N == 0) {
        throw new Exception("Error updating $pensionsTableName table: " . $e->getMessage());
      }

      $pensionTypeColumnCheck = CRM_Core_DAO::executeQuery("DESC $pensionsTableName");
      while ($pensionTypeColumnCheck->fetch()) {
        if ($pensionTypeColumnCheck->Field == 'pension_type' && $pensionTypeColumnCheck->Type != 'int(10) unsigned') {
          throw new Exception("Error updating $pensionsTableName table: " . $e->getMessage());
        }
      }
    }
  }

  /**
   * Deletes hrjc_pension_type option group.
   *
   * @throws Exception
   */
  private function removePensionTypeOptionGroup() {
    // Remove pension type option group
    try {
      civicrm_api3('OptionGroup', 'get', [
        'sequential' => 1,
        'name' => 'hrjc_pension_type',
        'api.OptionGroup.delete' => ['id' => '$value.id'],
      ]);
    } catch (Exception $e) {
      $pensionOptionGroup = civicrm_api3('OptionGroup', 'get', [
        'sequential' => 1,
        'name' => 'hrjc_pension_type',
      ]);

      if ($pensionOptionGroup['count'] > 0) {
        throw new Exception('Error deleting hrjc_pension_type option group: ' . $e->getMessage());
      }
    }
  }

  /**
   * Fetch pension provider option values and create a contact for each pension
   * provider type.  Also replaces option_values for contact id's in existing
   * contracts.
   *
   * @throws Exception
   */
  private function replacePensionProviders($pensionsTableName) {
    try {
      $pensionTypeOptions = civicrm_api3('OptionValue', 'get', [
        'sequential' => 1,
        'return' => ['name', 'label', 'value'],
        'option_group_id' => 'hrjc_pension_type',
      ]);
    } catch (Exception $e) {
      $pensionTypeOptionGroup = civicrm_api3('OptionGroup', 'get', array(
        'name' => 'hrjc_pension_type',
      ));
      if ($pensionTypeOptionGroup['count'] > 0) {
        throw new Exception('Error obtaining option values for "hrjc_pension_type" option group: ' . $e->getMessage());
      }
    }

    $pensionTypesMapping = [];
    foreach($pensionTypeOptions['values'] as $pensionType) {
      $contact = civicrm_api3('Contact', 'create', [
        'organization_name' => $pensionType['label'],
        'first_name' => $pensionType['label'],
        'display_name' => $pensionType['label'],
        'source' => $pensionType['name'] . ',' .$pensionType['value'],
        'contact_type' => 'Organization',
        'contact_sub_type' => 'Pension_Provider',
      ]);

      $pensionTypesMapping[$pensionType['value']] = $contact['id'];
    }

    // Replace pension_type value to the corresponding contact ID
    if (!empty($pensionTypesMapping)) {

      foreach ($pensionTypesMapping as $optionValue => $contactID) {
        CRM_Core_DAO::executeQuery("
          UPDATE {$pensionsTableName}
          SET pension_type = {$contactID}
          WHERE pension_type = '{$optionValue}'
        ");
      }
    }
  }

  /**
   * Creates 'Pension Provider' as a Contact Type
   *
   * @throws Exception
   *   If Pension_Provider contact type is not created.
   */
  private function createPensionProviderType() {
    try {
      civicrm_api3('ContactType', 'create', [
        'name' => 'Pension_Provider',
        'label' => 'Pension Provider',
        'parent_id' => 'Organization',
        'is_active' => 1,
        'is_reserved' => 1,
      ]);
    } catch (Exception $e) {
      $pensionProviderType = civicrm_api3('ContactType', 'get', array(
        'name' => 'Pension_Provider',
      ));

      if ($pensionProviderType['count'] < 1) {
        throw new Exception('Error creating Pension_Provider contact type: ' . $e->getMessage());
      }
    }
  }

  /**
   * Creates Option Group for Insurance Plan Types.
   */
  public function createInsurancePlanTypes() {
    try {
      civicrm_api3('OptionGroup', 'create', [
        'name' => 'hrjc_insurance_plantype',
        'title' => 'Insurance Plan Type',
        'is_active' => 1
      ]);

      $planTypes = [
        'Family',
        'Individual'
      ];

      foreach ($planTypes as $type) {
        civicrm_api3('OptionValue', 'create', [
          'option_group_id' => 'hrjc_insurance_plantype',
          'label' => $type,
          'value' => $type,
          'name' => $type
        ]);
      }
    } catch(Exception $e) {
      // OptionGroup already exists
      // Skip this
    }
  }

  /**
   * Removes duplicates for 'Employee - Permanent' contract type.
   */
  private function up1029_removeDuplicateContractType() {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => 'hrjc_contract_type',
      'value' => 'Employee - Permanent',
    ]);

    // We only delete if we find two ore more "Employee - Permanent" values
    if ($result['count'] > 1) {

      // Starts at $i = 1 to skip first value
      for ($i = 1; $i < $result['count']; $i++) {
        civicrm_api3('OptionValue', 'delete', [
          'id' => $result['values'][$i]['id'],
        ]);
      }
    }
  }

  /**
   * Sorts contract types alphabetically
   */
   private function up1029_sortContractTypes() {
    // fetch all contract types sorted alphabetically ( by their labels )
    // hence ['sort' => 'label asc']
    $prefixes = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'return' => ['id'],
      'option_group_id' => 'hrjc_contract_type',
      'options' => ['limit' => 0, 'sort' => 'label asc']
    ]);

    // update options weight
    $weight = 1;
    if (!empty($prefixes['values'])) {
      foreach($prefixes['values'] as $prefix) {
        civicrm_api3('OptionValue', 'create', [
          'id' => $prefix['id'],
          'weight' => $weight++
        ]);
      }
    }
  }

  /**
   * Creates a set of option values
   *
   * @param string $groupName
   *   Option group name
   * @param array $optionsToAdd
   *   option values to add to the option group
   */
  private function addOptionValues($groupName, $optionsToAdd) {
    foreach ($optionsToAdd as $option) {
      $optionValue = civicrm_api3('OptionValue', 'get',[
        'sequential' => 1,
        'return' => ['id'],
        'option_group_id' => $groupName,
        'name' => $option,
        'options' => ['limit' => 1]
      ]);

      // create the option value only if it is not exist
      if (empty($optionValue['id'])) {
        civicrm_api3('OptionValue', 'create',[
          'option_group_id' => $groupName,
          'name' => $option,
          'label' => $option,
        ]);
      }
    }
  }

  private function decToFraction($fte) {
    $fteDecimalPart = explode('.', $fte);
    $array = array();
    if (!empty($fteDecimalPart[1])) {
        $array  = str_split($fteDecimalPart[1]);
    }
    $numerators = array(0, 1);
    $denominators = array(1, 0);
    $tempFte = $fte;
    $result= '';
    //check whether same value is repeating  in decimal like 3 is repeating in 0.33333 0.33 and have value in decimal more than 1
    if(count(array_unique($array)) == 1 && count($array) != 1) {
      $repeatNum = array_unique($array);
      $num = $repeatNum[0];
      $denom = 9;
      $gcd = CRM_Hrjobcontract_Upgrader::commonDivisor($num,$denom);
      $val = array($num/$gcd, $denom/$gcd);
      return $val;
    }
    else {
      for ($i = 2; $i < 1000; $i++) {
        $floorFte = floor($tempFte);
        $numerators[$i] = $floorFte * $numerators[$i-1] + $numerators[$i-2];
        $denominators[$i] = $floorFte * $denominators[$i-1] + $denominators[$i-2];
        $result = '';
        if ($denominators[$i] != 0) {
          $result = $numerators[$i] / $denominators[$i];
        }
        if ((string)$result == (string)$fte) {
          $num = $numerators[$i];
          $denom = $denominators[$i];
          $val = array($num, $denom);
          return $val;
        }
        if ($tempFte-$floorFte != 0) {
          $tempFte = 1/($tempFte-$floorFte);
        }
      }
    }
  }

  private function commonDivisor($a,$b) {
    return ($a % $b) ? CRM_Hrjobcontract_Upgrader::commonDivisor($b,$a % $b) : $b;
  }

}
