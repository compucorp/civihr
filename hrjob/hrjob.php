<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.2                                                 |
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
    'label' => ts('Jobs'),
    'name' => 'jobs',
    'url'  => 'civicrm/job/import',
    'permission' => 'access HRJobs',
    'is_active'  => 1,
    'parent_id'  => $importJobNavigation->id,
    'weight'     => 1,
  );
  CRM_Core_BAO_Navigation::add($importJobMenuTree);
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
  $importJobId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'jobImport', 'id', 'name');
  CRM_Core_BAO_Navigation::processDelete($importJobId);
  CRM_Core_BAO_Navigation::resetNavigation();

  return _hrjob_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrjob_civicrm_enable() {
  CRM_Core_BAO_Navigation::processUpdate(array('name' => 'jobImport'), array('is_active' => 1));
  CRM_Core_BAO_Navigation::resetNavigation();

  return _hrjob_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrjob_civicrm_disable() {
  CRM_Core_BAO_Navigation::processUpdate(array('name' => 'jobImport'), array('is_active' => 0));
  CRM_Core_BAO_Navigation::resetNavigation();

  return _hrjob_civix_civicrm_disable();
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
  if($entity == 'h_r_job_leave' &&
    ( $cid == $params['contact_id'] || $params['api.has_parent'] == 1 ) &&
    $action == 'get') {
    $permissions['h_r_job_leave']['get'] = array('access own HRJobs');
  } else {
    $permissions['h_r_job_leave']['get'] = array('access HRJobs');
  }

  if ($entity == 'h_r_job' && $cid == $params['contact_id'] && $action == 'get') {
    $permissions['h_r_job']['get'] = array('access own HRJobs');
   } else {
    $permissions['h_r_job']['get'] = array('access CiviCRM', 'access HRJobs');
  }
  $permissions['h_r_job']['create'] = array('access CiviCRM', 'edit HRJobs');
  $permissions['h_r_job']['update'] = array('access CiviCRM', 'edit HRJobs');
  $permissions['h_r_job']['duplicate'] = array('access CiviCRM', 'edit HRJobs');
  $permissions['h_r_job']['delete'] = array('access CiviCRM', 'delete HRJobs');
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