<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'hrui.civix.php';

function hrui_civicrm_pageRun($page) {
  if ($page instanceof CRM_Contact_Page_DashBoard) {
    CRM_Utils_System::setTitle(ts('CiviHR Home'));
  }
}

/**
 * Implementation of hook_civicrm_config
 */
function hrui_civicrm_config(&$config) {
  global $civicrm_setting;
  $civicrm_setting['CiviCRM Preferences']['communityMessagesUrl'] = FALSE;
  _hrui_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrui_civicrm_xmlMenu(&$files) {
  _hrui_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrui_civicrm_install() {
  $domainID = CRM_Core_Config::domainID();
  $options  = CRM_Core_OptionGroup::values('contact_view_options', TRUE, FALSE);
  $tabsToUnset = array($options['Activities'], $options['Tags']);

  // get tab options from DB
  $params = array(
    'version'   => 3,
    'domain_id' => $domainID,
    'return'    => 'contact_view_options',
  );
  $result   = civicrm_api('setting', 'get', $params);
  $options  = array_flip($result['values'][$domainID]['contact_view_options']);
  
  // unset activity & tag tab options
  foreach ($tabsToUnset as $key) {
    unset($options[$key]);
  }
  $options = array_keys($options);
  
  // set modified options in the DB
  $params = array(
    'version'   => 3,
    'domain_id' => $domainID,
    'contact_view_options' => $options,
  );
  $result = civicrm_api('setting', 'create', $params);

  return _hrui_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrui_civicrm_uninstall() {
  $domainID = CRM_Core_Config::domainID();
  $options  = CRM_Core_OptionGroup::values('contact_view_options', TRUE, FALSE);
  $tabsToSet = array($options['Activities'], $options['Tags']);
  
  // get tab options from DB
  $params = array(
    'version'   => 3,
    'domain_id' => $domainID,
    'return'    => 'contact_view_options',
  );
  $result   = civicrm_api('setting', 'get', $params);
  $options  = array_flip($result['values'][$domainID]['contact_view_options']);
  
  // set activity & tag tab options
  foreach ($tabsToSet as $key) {
    $options[$key] = 1;
  }
  $options = array_keys($options);
  
  // set modified options in the DB
  $params = array(
    'version'   => 3,
    'domain_id' => $domainID,
    'contact_view_options' => $options,
  );
  $result = civicrm_api('setting', 'create', $params);

  return _hrui_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrui_civicrm_enable() {
  return _hrui_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrui_civicrm_disable() {
  return _hrui_civix_civicrm_disable();
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
function hrui_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrui_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrui_civicrm_managed(&$entities) {
  return _hrui_civix_civicrm_managed($entities);
}
