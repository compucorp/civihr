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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'hrjob.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrjob_civicrm_config(&$config) {
  _hrjob_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrjob_civicrm_xmlMenu(&$files) {
  _hrjob_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrjob_civicrm_install() {
  $cType = CRM_Contact_BAO_ContactType::basicTypePairs(false,'id');
  $org_id = array_search('Organization',$cType);
  $sub_type_name = array('Health Insurance Provider','Life Insurance Provider');
  $orgSubType = CRM_Contact_BAO_ContactType::subTypes('Organization', true);
  $orgSubType = CRM_Contact_BAO_ContactType::subTypeInfo('Organization');
  $params['parent_id'] = $org_id;
  $params['is_active'] = 1;

  if ($org_id) {
    foreach($sub_type_name as $sub_type_name) {
      $subTypeName = ucfirst(CRM_Utils_String::munge($sub_type_name));
      $subID = array_key_exists( $subTypeName, $orgSubType );
      if (!$subID) {
        $params['name'] = $subTypeName;
        $params['label'] = $sub_type_name;
        CRM_Contact_BAO_ContactType::add($params);
      }
      elseif ($subID && $orgSubType[$subTypeName]['is_active']==0) {
        CRM_Contact_BAO_ContactType::setIsActive($orgSubType[$subTypeName]['id'], 1);
      }
    }
  }

  //Add job import navigation menu
  $weight = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Import Contacts', 'weight', 'name');
  $contactNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');
  $administerNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Dropdown Options', 'id', 'name');

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
  $importJobMenuTree = array(
    array(
      'label'      => ts('Hours Types'),
      'name'       => 'hoursType',
      'url'        => 'civicrm/hour/editoption',
      'permission' => 'administer CiviCRM',
      'parent_id'  => $administerNavId,
    ),
  );
  foreach ($importJobMenuTree as $key => $menuItems) {
    $menuItems['is_active'] = 1;
    CRM_Core_BAO_Navigation::add($menuItems);
  }
  CRM_Core_BAO_Navigation::resetNavigation();

  return _hrjob_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrjob_civicrm_uninstall() {
  $subTypeInfo = CRM_Contact_BAO_ContactType::subTypeInfo('Organization');
  $sub_type_name = array('Health Insurance Provider','Life Insurance Provider');
  foreach($sub_type_name as $sub_type_name) {
    $subTypeName = ucfirst(CRM_Utils_String::munge($sub_type_name));
    $orid = array_key_exists($subTypeName, $subTypeInfo);
    if($orid) {
      $id = $subTypeInfo[$subTypeName]['id'];
      CRM_Contact_BAO_ContactType::del($id);
    }
  }
  //delete job import navigation menu
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_navigation WHERE name IN ('jobImport','hoursType')");
  CRM_Core_BAO_Navigation::resetNavigation();

  //delete custom groups and field
  $customGroup = civicrm_api3('CustomGroup', 'getsingle', array('return' => "id",'name' => "HRJob_Summary",));
  civicrm_api3('CustomGroup', 'delete', array('id' => $customGroup['id']));

  //delete all option group and values
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_option_group WHERE name IN ('hrjob_contract_type', 'hrjob_level_type', 'hrjob_department', 'hrjob_hours_type', 'hrjob_pay_grade', 'hrjob_health_provider', 'hrjob_life_provider', 'hrjob_location', 'hrjob_pension_type', 'hrjob_region', 'hrjob_pay_scale')");

  return _hrjob_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrjob_civicrm_enable() {
  _hrjob_setActiveFields(1);
  return _hrjob_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrjob_civicrm_disable() {
  _hrjob_setActiveFields(0);
  return _hrjob_civix_civicrm_disable();
}

function _hrjob_setActiveFields($setActive) {
  $sql = "UPDATE civicrm_navigation SET is_active= {$setActive} WHERE name IN ('jobs','jobImport','hoursType')";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_BAO_Navigation::resetNavigation();

  //disable/enable customgroup and customvalue
  $sql = "UPDATE civicrm_custom_field JOIN civicrm_custom_group ON civicrm_custom_group.id = civicrm_custom_field.custom_group_id SET civicrm_custom_field.is_active = {$setActive} WHERE civicrm_custom_group.name = 'HRJob_Summary'";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name = 'HRJob_Summary'");

  //disable/enable optionGroup and optionValue
  $query = "UPDATE civicrm_option_value JOIN civicrm_option_group ON civicrm_option_group.id = civicrm_option_value.option_group_id SET civicrm_option_value.is_active = {$setActive} WHERE civicrm_option_group.name IN ('hrjob_contract_type', 'hrjob_level_type', 'hrjob_department', 'hrjob_hours_type', 'hrjob_pay_grade', 'hrjob_health_provider', 'hrjob_life_provider', 'hrjob_location', 'hrjob_pension_type', 'hrjob_region', 'hrjob_pay_scale')";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$setActive} WHERE name IN ('hrjob_contract_type', 'hrjob_level_type', 'hrjob_department', 'hrjob_hours_type', 'hrjob_pay_grade', 'hrjob_health_provider', 'hrjob_life_provider', 'hrjob_location', 'hrjob_pension_type',  'hrjob_region', 'hrjob_pay_scale')");
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function hrjob_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrjob_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrjob_civicrm_managed(&$entities) {
  return _hrjob_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_tabs
 */
function hrjob_civicrm_tabs(&$tabs, $contactID) {
  if (!CRM_Core_Permission::check('edit HRJobs')) {
    return;
  }

  $contactType = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $contactID, 'contact_type', 'id');
  if ($contactType != 'Individual') {
    return;
  }

  CRM_HRJob_Page_JobsTab::registerScripts();
  $tab = array(
    'id' => 'hrjob',
    'url' => CRM_Utils_System::url('civicrm/contact/view/hrjob', array(
      'cid' => $contactID,
      'snippet' => 1,
    )),
    'title' => ts('Jobs'),
    'weight' => 10,
    'count' => CRM_HRJob_BAO_HRJob::getRecordCount(array(
      'contact_id' => $contactID
    )),
  );
  $tabs[] = $tab;
  CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrjob', 'js/hrjob.js');
  $selectedChild = CRM_Utils_Request::retrieve('selectedChild', 'String');
  CRM_Core_Resources::singleton()->addSetting(array(
    'tabs' => array(
      'selectedChild' => $selectedChild,
    ),
  ));
}

/**
 * Implementation of hook_civicrm_queryObjects
 */
function hrjob_civicrm_queryObjects(&$queryObjects, $type) {
  if ($type == 'Contact') {
    $queryObjects[] = new CRM_HRJob_BAO_Query();
  }
  elseif ($type == 'Report') {
    $queryObjects[] = new CRM_HRJob_BAO_ReportHook();
  }
}

/**
 * Implementation of hook_civicrm_entityTypes
 */
function hrjob_civicrm_entityTypes(&$entityTypes) {
  $entityTypes[] = array(
    'name' => 'HRJob',
    'class' => 'CRM_HRJob_DAO_HRJob',
    'table' => 'civicrm_hrjob',
  );
  $entityTypes[] = array(
    'name' => 'HRJobPay',
    'class' => 'CRM_HRJob_DAO_HRJobPay',
    'table' => 'civicrm_hrjob_pay',
  );
  $entityTypes[] = array(
    'name' => 'HRJobHealth',
    'class' => 'CRM_HRJob_DAO_HRJobHealth',
    'table' => 'civicrm_hrjob_health',
  );
  $entityTypes[] = array(
    'name' => 'HRJobHour',
    'class' => 'CRM_HRJob_DAO_HRJobHour',
    'table' => 'civicrm_hrjob_hour',
  );
  $entityTypes[] = array(
    'name' => 'HRJobLeave',
    'class' => 'CRM_HRJob_DAO_HRJobLeave',
    'table' => 'civicrm_hrjob_leave',
  );
  $entityTypes[] = array(
    'name' => 'HRJobPension',
    'class' => 'CRM_HRJob_DAO_HRJobPension',
    'table' => 'civicrm_hrjob_pension',
  );
  $entityTypes[] = array(
    'name' => 'HRJobRole',
    'class' => 'CRM_HRJob_DAO_HRJobRole',
    'table' => 'civicrm_hrjob_role',
  );
}

/*function hrjob_civicrm_triggerInfo(&$info, $tableName) {
  $info[] = array(
    'table' => array('civicrm_hrjob'),
    'when' => 'after',
    'event' => array('insert', 'update'),
    'sql' => "
      IF NEW.contact_id IS NOT NULL THEN
        SET @hrjob_joindate = (SELECT min(period_start_date) FROM civicrm_hrjob WHERE contact_id = NEW.contact_id);
        SET @hrjob_termdate = (SELECT max(period_end_date) FROM civicrm_hrjob WHERE contact_id = NEW.contact_id);
        INSERT INTO civicrm_value_job_summary_10 (entity_id,initial_join_date_56,final_termination_date_57)
          VALUES (NEW.contact_id, @hrjob_joindate, @hrjob_termdate)
          ON DUPLICATE KEY UPDATE
          initial_join_date_56 = @hrjob_joindate,
          final_termination_date_57 = @hrjob_termdate;
      END IF;
    ",
  );
  $info[] = array(
    'table' => array('civicrm_hrjob'),
    'when' => 'before',
    'event' => array('update', 'delete'),
    'sql' => "
      IF OLD.contact_id IS NOT NULL THEN
        SET @hrjob_joindate = (SELECT min(period_start_date) FROM civicrm_hrjob WHERE contact_id = OLD.contact_id);
        SET @hrjob_termdate = (SELECT max(period_end_date) FROM civicrm_hrjob WHERE contact_id = OLD.contact_id);
        INSERT INTO civicrm_value_job_summary_10 (entity_id,initial_join_date_56,final_termination_date_57)
          VALUES (OLD.contact_id, @hrjob_joindate, @hrjob_termdate)
          ON DUPLICATE KEY UPDATE
          initial_join_date_56 = @hrjob_joindate,
          final_termination_date_57 = @hrjob_termdate;
      END IF;
    ",
  );
}*/

/**
 * Implementation of hook_civicrm_permission
 *
 * @param array $permissions
 * @return void
 */
function hrjob_civicrm_permission(&$permissions) {
  $prefix = ts('CiviHRJob') . ': '; // name of extension or module
  $permissions += array(
    'access HRJobs' => $prefix . ts('access HRJobs'),
    'edit HRJobs' => $prefix . ts('edit HRJobs'),
    'delete HRJobs' => $prefix . ts('delete HRJobs'),
    'access own HRJobs' => $prefix . ts('access own HRJobs'),
  );
}

/**
 * Implementaiton of hook_civicrm_alterAPIPermissions
 *
 * @param $entity
 * @param $action
 * @param $params
 * @param $permissions
 * @return void
 */
function hrjob_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $session = CRM_Core_Session::singleton();
  $cid = $session->get('userID');

  if (substr($entity, 0, 7) == 'h_r_job' && $cid == $params['contact_id'] && $action == 'get') {
    $permissions[$entity]['get'] = array('access CiviCRM', array('access own HRJobs', 'access HRJobs'));
   } elseif (substr($entity, 0, 7) == 'h_r_job' && $action == 'get') {
    $permissions[$entity]['get'] = array('access CiviCRM', 'access HRJobs');
  }
  if (substr($entity, 0, 7) == 'h_r_job') {
    $permissions[$entity]['create'] = array('access CiviCRM', 'edit HRJobs');
    $permissions[$entity]['update'] = array('access CiviCRM', 'edit HRJobs');
    $permissions[$entity]['replace'] = array('access CiviCRM', 'edit HRJobs');
    $permissions[$entity]['duplicate'] = array('access CiviCRM', 'edit HRJobs');
    $permissions[$entity]['delete'] = array('access CiviCRM', 'delete HRJobs');
  }
  $permissions['CiviHRJob'] = $permissions['h_r_job'];
}

/**
 * @return array list fields keyed by stable name; each field has:
 *   - id: int
 *   - name: string
 *   - column_name: string
 *   - field: string, eg "custom_123"
 */
function hrjob_getSummaryFields($fresh = FALSE) {
  static $cache = NULL;
  if ($cache === NULL || $fresh) {
    $sql =
      "SELECT ccf.id, ccf.name, ccf.column_name, concat('custom_', ccf.id) as field
      FROM civicrm_custom_group ccg
      INNER JOIN civicrm_custom_field ccf ON ccf.custom_group_id = ccg.id
      WHERE ccg.name = 'HRJob_Summary'
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $cache = array();
    while ($dao->fetch()) {
      $cache[$dao->name] = $dao->toArray();
    }
  }
  return $cache;
}

/**
 * Helper function to load data into DB between iterations of the unit-test
 */
function _hrjob_phpunit_populateDB() {
  $import = new CRM_Utils_Migrate_Import();
  $import->run(
    CRM_Extension_System::singleton()->getMapper()->keyToBasePath('org.civicrm.hrjob')
      . '/xml/option_group_install.xml'
  );
  $import->run(
    CRM_Extension_System::singleton()->getMapper()->keyToBasePath('org.civicrm.hrjob')
      . '/xml/job_summary_install.xml'
  );

  //create option value for option group region
  $result = civicrm_api3('OptionGroup', 'get', array(
    'name' => "hrjob_region",
  ));
  $regionVal = array(
    'Asia' => ts('Asia'),
    'Europe' => ts('Europe'),
  );

  foreach ($regionVal as $name => $label) {
    $regionParam = array(
      'option_group_id' => $result['id'],
      'label' => $label,
      'name' => $name,
      'value' => $name,
      'is_active' => 1,
    );
    civicrm_api3('OptionValue', 'create', $regionParam);
  }
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function hrjob_civicrm_caseTypes(&$caseTypes) {
  _hrjob_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function hrjob_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _hrjob_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function hrjob_civicrm_pageRun( &$page ) {
  if ($page instanceof CRM_Contact_Page_View_Summary || $page instanceof CRM_Contact_Page_Inline_CustomData) {
    $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
    $gid = array_search('HRJob_Summary', $groups);
    CRM_Core_Resources::singleton()->addSetting(array('grID' => $gid,));
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrjob', 'js/jobsummary.js');
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrjob', 'js/readable-range.js');
  }
}

/**
 * Implementation of hook_civicrm_buildForm
 *
 * @params string $formName - the name of the form
 *         object $form - reference to the form object
 * @return void
 */
function hrjob_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Export_Form_Select') {
    $_POST['unchange_export_selected_column'] = TRUE;
    if (!empty($form->_submitValues) && $form->_submitValues['exportOption'] == CRM_Export_Form_Select::EXPORT_SELECTED) {
      $_POST['unchange_export_selected_column'] = FALSE;
    }
  }
}

function hrjob_civicrm_export( $exportTempTable, $headerRows, $sqlColumns, $exportMode ) {
  if ($exportMode == CRM_Export_Form_Select::EXPORT_ALL && !empty($_POST['unchange_export_selected_column'])) {
    //drop column from table -- HR-379
    $col = array('do_not_trade', 'do_not_email');
    if ($_POST['unchange_export_selected_column']) {
      $sql = "ALTER TABLE ".$exportTempTable." ";
      $sql .= "DROP COLUMN do_not_email ";
      $sql .= ",DROP COLUMN do_not_trade ";
      CRM_Core_DAO::singleValueQuery($sql);

      $i = 0;
      foreach($sqlColumns as $key => $val){
        if (in_array($key, $col)){
          //unset column from sqlColumn and headerRow
          unset($sqlColumns[$key]);
          unset($headerRows[$i]);
        }
        $i++;
      }
      CRM_Export_BAO_Export::writeCSVFromTable($exportTempTable, $headerRows, $sqlColumns, $exportMode);

      // delete the export temp table
      $sql = "DROP TABLE IF EXISTS {$exportTempTable}";
      CRM_Core_DAO::executeQuery($sql);
      CRM_Utils_System::civiExit();
    }
  }
}
