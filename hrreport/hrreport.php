<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'hrreport.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrreport_civicrm_config(&$config) {
  _hrreport_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrreport_civicrm_xmlMenu(&$files) {
  _hrreport_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrreport_civicrm_install() {
  $id     = NULL;
  $action = CRM_Core_Action::ADD;
  $params = array(
    array(
      'label'       => 'HRDetail',
      'description' => 'HRDetail Report',
      'value'       => 'civihr/detail',
      'name'        => 'CRM_HRReport_Form_HRDetail',
      'is_active'   => TRUE,
    ),
  );
  $groupParam = array('name' => 'report_template');
  foreach ($params as $param) {
    $optionValue = CRM_Core_OptionValue::addOptionValue($param, $groupParam, $action, $id);
    CRM_Core_Error::debug_var( '$optionValue', $optionValue );
  }
  return _hrreport_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrreport_civicrm_uninstall() {
  $reportOptVals = array('civihr/detail');
  
  foreach ($reportOptVals as $optionVal) {
    CRM_Core_Error::debug_var( '$optionVal', $optionVal );
    $templateInfo = CRM_Core_OptionGroup::getRowValues('report_template', "{$optionVal}", 'value');
    CRM_Core_Error::debug_var( '$templateInfo', $templateInfo );
    if ($optValID = CRM_Utils_Array::value('id', $templateInfo)) {
      CRM_Core_BAO_OptionValue::del($optValID);
    }
  }
  return _hrreport_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrreport_civicrm_enable() {
  return _hrreport_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrreport_civicrm_disable() {
  return _hrreport_civix_civicrm_disable();
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
function hrreport_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrreport_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrreport_civicrm_managed(&$entities) {
  return _hrreport_civix_civicrm_managed($entities);
}
