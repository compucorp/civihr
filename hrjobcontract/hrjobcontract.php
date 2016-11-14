<?php

require_once 'hrjobcontract.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function hrjobcontract_civicrm_config(&$config) {
  _hrjobcontract_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function hrjobcontract_civicrm_xmlMenu(&$files) {
  _hrjobcontract_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function hrjobcontract_civicrm_install() {
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


  /* on civicrm 4.7.7 this activity type (Contact Deleted by Merge) is not created
 * as a part of civicrm installation but it should be, since it's used in
 * contact merge code in core civicrm files. So here we just insure that it will
 * be created .
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

  return _hrjobcontract_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function hrjobcontract_civicrm_uninstall() {
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

  $jobContractMenu = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'job_contracts', 'id', 'name');
  if (!empty($jobContractMenu)) {
    CRM_Core_BAO_Navigation::processDelete($jobContractMenu);
  }
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_navigation WHERE name IN ('import_export_job_contracts', 'import_job_contracts', 'hoursType', 'pay_scale','hours_location', 'hrjc_contact_type', 'hrjc_location', 'hrjc_pay_cycle', 'hrjc_benefit_name', 'hrjc_benefit_type', 'hrjc_deduction_name', 'hrjc_deduction_type', 'hrjc_health_provider', 'hrjc_life_provider', 'hrjc_pension_type', 'hrjc_revision_change_reason', 'hrjc_contract_end_reason')");
  CRM_Core_BAO_Navigation::resetNavigation();

  //delete custom groups and field
  $customGroup = civicrm_api3('CustomGroup', 'get', array('name' => "HRJobContract_Summary",));
  $customGroupData = CRM_Utils_Array::first($customGroup['values']);
  if (!empty($customGroupData['id'])) {
    civicrm_api3('CustomGroup', 'delete', array('id' => $customGroupData['id']));
  }
  $customGroup = civicrm_api3('CustomGroup', 'get', array('name' => "HRJobContract_Summary",));
  $customGroupData = CRM_Utils_Array::first($customGroup['values']);
  if (!empty($customGroupData['id'])) {
    civicrm_api3('CustomGroup', 'delete', array('id' => $customGroupData['id']));
  }

  //delete all option group and values
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_option_group WHERE name IN ('job_contracts', 'hoursType', 'pay_scale','hours_location', 'hrjc_contact_type', 'hrjc_location', 'hrjc_pay_cycle', 'hrjc_benefit_name', 'hrjc_benefit_type', 'hrjc_deduction_name', 'hrjc_deduction_type', 'hrjc_health_provider', 'hrjc_life_provider', 'hrjc_pension_type', 'hrjc_revision_change_reason', 'hrjc_contract_end_reason', 'hrjc_contract_type', 'hrjc_level_type', 'hrjc_department', 'hrjc_hours_type', 'hrjc_pay_grade', 'hrjc_health_provider', 'hrjc_life_provider', 'hrjc_location', 'hrjc_pension_type', 'hrjc_region', 'hrjc_pay_scale')");

  //delete job contract files to entities relations
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_entity_file WHERE entity_table LIKE 'civicrm_hrjobcontract_%'");

  // delete 'length_of_service' Custom Group
  $customGroup = civicrm_api3('CustomGroup', 'getsingle', array('return' => "id",'name' => "Contact_Length_Of_Service",));
  civicrm_api3('CustomGroup', 'delete', array('id' => $customGroup['id']));

  // delete scheduled job entry containing length of service upgrader
  $dao = new CRM_Core_DAO_Job();
  $dao->api_entity = 'HRJobContract';
  $dao->api_action = 'updatelengthofservice';
  $dao->find(TRUE);
  if ($dao->id)
  {
    $dao->delete();
  }

  return _hrjobcontract_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function hrjobcontract_civicrm_enable() {
  _hrjobcontract_setActiveFields(1);
  return _hrjobcontract_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function hrjobcontract_civicrm_disable() {
  _hrjobcontract_setActiveFields(0);
  return _hrjobcontract_civix_civicrm_disable();
}

function _hrjobcontract_setActiveFields($setActive) {
  $sql = "UPDATE civicrm_navigation SET is_active= {$setActive} WHERE name IN ('import_export_job_contracts', 'import_job_contracts', 'hoursType', 'pay_scale','hours_location', 'hrjc_contact_type', 'hrjc_location', 'hrjc_pay_cycle', 'hrjc_benefit_name', 'hrjc_benefit_type', 'hrjc_deduction_name', 'hrjc_deduction_type', 'hrjc_health_provider', 'hrjc_life_provider', 'hrjc_pension_type', 'hrjc_revision_change_reason', 'hrjc_contract_end_reason')";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_BAO_Navigation::resetNavigation();

  //disable/enable customgroup and customvalue
  $sql = "UPDATE civicrm_custom_field JOIN civicrm_custom_group ON civicrm_custom_group.id = civicrm_custom_field.custom_group_id SET civicrm_custom_field.is_active = {$setActive} WHERE civicrm_custom_group.name = 'HRJobContract_Summary'";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name IN ('HRJobContract_Summary', 'Contact_Length_Of_Service')");

  //disable/enable optionGroup and optionValue
  $query = "UPDATE civicrm_option_value JOIN civicrm_option_group ON civicrm_option_group.id = civicrm_option_value.option_group_id SET civicrm_option_value.is_active = {$setActive} WHERE civicrm_option_group.name IN ('job_contracts', 'hoursType', 'pay_scale','hours_location', 'hrjc_contact_type', 'hrjc_location', 'hrjc_pay_cycle', 'hrjc_benefit_name', 'hrjc_benefit_type', 'hrjc_deduction_name', 'hrjc_deduction_type', 'hrjc_health_provider', 'hrjc_life_provider', 'hrjc_pension_type', 'hrjc_revision_change_reason', 'hrjc_contract_end_reason', 'hrjc_contract_type', 'hrjc_level_type', 'hrjc_department', 'hrjc_hours_type', 'hrjc_pay_grade', 'hrjc_health_provider', 'hrjc_life_provider', 'hrjc_location', 'hrjc_pension_type', 'hrjc_region', 'hrjc_pay_scale')";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$setActive} WHERE name IN ('job_contracts', 'hoursType', 'pay_scale','hours_location', 'hrjc_contact_type', 'hrjc_location', 'hrjc_pay_cycle', 'hrjc_benefit_name', 'hrjc_benefit_type', 'hrjc_deduction_name', 'hrjc_deduction_type', 'hrjc_health_provider', 'hrjc_life_provider', 'hrjc_pension_type', 'hrjc_revision_change_reason', 'hrjc_contract_end_reason', 'hrjc_contract_type', 'hrjc_level_type', 'hrjc_department', 'hrjc_hours_type', 'hrjc_pay_grade', 'hrjc_health_provider', 'hrjc_life_provider', 'hrjc_location', 'hrjc_pension_type', 'hrjc_region', 'hrjc_pay_scale')");

  // disable/enable update length of service scheduled job
    $dao = new CRM_Core_DAO_Job();
    $dao->api_entity = 'HRJobContract';
    $dao->api_action = 'updatelengthofservice';
    $dao->find(TRUE);
    if ($dao->id)
    {
      $dao->is_active = $setActive;
      $dao->save();
    }
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function hrjobcontract_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrjobcontract_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function hrjobcontract_civicrm_managed(&$entities) {
  _hrjobcontract_civix_civicrm_managed($entities);
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
function hrjobcontract_civicrm_caseTypes(&$caseTypes) {
  _hrjobcontract_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function hrjobcontract_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  $settingsDir = __DIR__ . DIRECTORY_SEPARATOR . 'settings';
  if (is_dir($settingsDir) && !in_array($settingsDir, $metaDataFolders)) {
    $metaDataFolders[] = $settingsDir;
  }
  $metaDataFolders = array_unique($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_pageRun
 */
function hrjobcontract_civicrm_pageRun($page) {
  if ($page instanceof CRM_Contact_Page_View_Summary || $page instanceof CRM_Contact_Page_Inline_CustomData) {
    $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
    $gid = array_search('HRJobContract_Summary', $groups);

    CRM_Core_Resources::singleton()->addSetting(array('grID' => $gid));
  }
}

/**
 * Implementation of hook_civicrm_buildForm
 *
 * @params string $formName - the name of the form
 *         object $form - reference to the form object
 * @return void
 */
function hrjobcontract_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Export_Form_Select') {
    $_POST['unchange_export_selected_column'] = TRUE;
    if (!empty($form->_submitValues) && $form->_submitValues['exportOption'] == CRM_Export_Form_Select::EXPORT_SELECTED) {
      $_POST['unchange_export_selected_column'] = FALSE;
    }
  }
}


/**
 * Implementation of hook_civicrm_tabs
 * this tab should appear after contact summary tab directly
 * and since contact summary tab weight is
 * -200 we chose this to be -190
 * to give some room for other extensions to place
 * their tabs between these two.
 */
function hrjobcontract_civicrm_tabs(&$tabs, $contactId) {
  $tabs[] = Array(
    'id'        => 'hrjobcontract',
    'url'       => CRM_Utils_System::url('civicrm/contact/view/hrjobcontract', array('cid' => $contactId)),
    'title'     => ts('Job Contract'),
    'weight'    => -190
  );
}

/**
 * Implementation of hook_civicrm_entityTypes
 */
function hrjobcontract_civicrm_entityTypes(&$entityTypes) {
  $entityTypes[] = array(
    'name' => 'HRJobContract',
    'class' => 'CRM_Hrjobcontract_DAO_HRJobContract',
    'table' => 'civicrm_hrjobcontract',
  );
  $entityTypes[] = array(
    'name' => 'HRJobContractRevision',
    'class' => 'CRM_Hrjobcontract_DAO_HRJobContractRevision',
    'table' => 'civicrm_hrjobcontract_revision',
  );
  $entityTypes[] = array(
    'name' => 'HRJobDetails',
    'class' => 'CRM_Hrjobcontract_DAO_HRJobDetails',
    'table' => 'civicrm_hrjobcontract_details',
  );
  $entityTypes[] = array(
    'name' => 'HRJobPay',
    'class' => 'CRM_Hrjobcontract_DAO_HRJobPay',
    'table' => 'civicrm_hrjobcontract_pay',
  );
  $entityTypes[] = array(
    'name' => 'HRJobHealth',
    'class' => 'CRM_Hrjobcontract_DAO_HRJobHealth',
    'table' => 'civicrm_hrjobcontract_health',
  );
  $entityTypes[] = array(
    'name' => 'HRJobHour',
    'class' => 'CRM_Hrjobcontract_DAO_HRJobHour',
    'table' => 'civicrm_hrjobcontract_hour',
  );
  $entityTypes[] = array(
    'name' => 'HRJobLeave',
    'class' => 'CRM_Hrjobcontract_DAO_HRJobLeave',
    'table' => 'civicrm_hrjobcontract_leave',
  );
  $entityTypes[] = array(
    'name' => 'HRJobPension',
    'class' => 'CRM_Hrjobcontract_DAO_HRJobPension',
    'table' => 'civicrm_hrjobcontract_pension',
  );
  $entityTypes[] = array(
    'name' => 'HRJobRole',
    'class' => 'CRM_Hrjobcontract_DAO_HRJobRole',
    'table' => 'civicrm_hrjobcontract_role',
  );
  $entityTypes[] = array(
    'name' => 'HRHoursLocation',
    'class' => 'CRM_Hrjobcontract_DAO_HoursLocation',
    'table' => 'civicrm_hrhours_location',
  );
  $entityTypes[] = array(
    'name' => 'HRPayScale',
    'class' => 'CRM_Hrjobcontract_DAO_PayScale',
    'table' => 'civicrm_hrpay_scale',
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
function hrjobcontract_civicrm_alterAPIPermissions_($entity, $action, &$params, &$permissions) {
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
  $permissions['CiviHRJobContract'] = $permissions['h_r_job'];
}

/**
 * @return array list fields keyed by stable name; each field has:
 *   - id: int
 *   - name: string
 *   - column_name: string
 *   - field: string, eg "custom_123"
 */
function hrjobcontract_getSummaryFields($fresh = FALSE) {
  static $cache = NULL;
  if ($cache === NULL || $fresh) {
    $sql =
      "SELECT ccf.id, ccf.name, ccf.column_name, concat('custom_', ccf.id) as field
      FROM civicrm_custom_group ccg
      INNER JOIN civicrm_custom_field ccf ON ccf.custom_group_id = ccg.id
      WHERE ccg.name = 'HRJobContract_Summary'
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
 * Implementation of hook_civicrm_queryObjects
 */
function hrjobcontract_civicrm_queryObjects(&$queryObjects, $type) {
  if ($type == 'Contact') {
    $queryObjects[] = new CRM_Hrjobcontract_BAO_Query();
  }
}

/**
 * Implementation of hook_civicrm_permission
 *
 * @param array $permissions
 * @return void
 */
function hrjobcontract_civicrm_permission(&$permissions) {
  $prefix = 'CiviHRJobContract' . ': ';
  $permissions += array(
    'access HRJobs' => $prefix . ts('access HRJobs'),
    'edit HRJobs' => $prefix . ts('edit HRJobs'),
    'delete HRJobs' => $prefix . ts('delete HRJobs'),
    'access own HRJobs' => $prefix . ts('access own HRJobs'),
  );
}

/**
 * Helper function to load data into DB between iterations of the unit-test
 */
function _hrjobcontract_phpunit_populateDB() {
  $import = new CRM_Utils_Migrate_Import();
  $import->run(
    CRM_Extension_System::singleton()->getMapper()->keyToBasePath('org.civicrm.hrjobcontract')
      . '/xml/option_group_install.xml'
  );

  //create option value for option group region
  $result = civicrm_api3('OptionGroup', 'get', array(
    'name' => "hrjc_region",
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

function hrjobcontract_civicrm_export( $exportTempTable, $headerRows, $sqlColumns, $exportMode ) {
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
    }
  }

  // Here we call custom writeCSVFromTable method instead
  // of CRM_Export_BAO_Export::writeCSVFromTable. It allows us to convert
  // Job Contract entity values to proper export format.
  CRM_Hrjobcontract_Export_Converter::writeCSVFromTable($exportTempTable, $headerRows, $sqlColumns, $exportMode);
  $sql = "DROP TABLE IF EXISTS {$exportTempTable}";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Utils_System::civiExit();
}

function hrjobcontract_civicrm_merge($type, &$data, $mainId = NULL, $otherId = NULL, $tables = NULL) {
  switch ($type) {
    case 'cidRefs':
        $data['civicrm_hrjobcontract'] = array('contact_id');
      break;
  }
}

/**
 * Implementation of hook_civicrm_searchColumns
 *
 * @return void
 */
function hrjobcontract_civicrm_searchColumns( $objectName, &$headers, &$rows, &$selector ) {
  $options = getWorkLocationOptions();

  // replace location options values with label
  foreach($rows as &$row){
    if(!empty($row['hrjobcontract_details_location']) && isset($options[$row['hrjobcontract_details_location']])){
      $row['hrjobcontract_details_location'] = $options[$row['hrjobcontract_details_location']];
    } else {
      $row['hrjobcontract_details_location'] = '';
    }
  }
}

/**
 * Get all options for group hrjc_location
 *
 * @return array
 */
function getWorkLocationOptions(){
  // get option group ID
  $optionGroup = civicrm_api3('OptionGroup', 'get', array(
    'sequential' => 1,
    'return' => "id",
    'name' => "hrjc_location",
  ));
  $optionGroupId = $optionGroup['values'][0]['id'];

  // fetch options for hrjc_location
  return CRM_Core_BAO_OptionValue::getOptionValuesAssocArray($optionGroupId);
}

/**
 * Get value => label array of Contract Type Options
 *
 * @return array
 */
function getContractTypeOptions() {
  // contract type options:
  $contractTypeOptions = array();
  CRM_Core_OptionGroup::getAssoc('hrjc_contract_type', $contractTypeOptions, true);
  $valueLabelMap = array();
  foreach ($contractTypeOptions as $contractType) {
    $valueLabelMap[$contractType['value']] = $contractType['label'];
  }

  return $valueLabelMap;
}

/**
 * Implementation of hook_civicrm_pre hook.
 *
 * @param string $op
 * @param string $objectName
 * @param int $objectId
 * @param object $objectRef
 */
function hrjobcontract_civicrm_pre($op, $objectName, $objectId, &$objectRef) {
  if ($objectName === 'Individual' && $op === 'delete') {
    CRM_Hrjobcontract_BAO_HRJobContract::deleteAllContractsPermanently($objectId);
  }
}
