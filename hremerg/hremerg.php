<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'hremerg.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hremerg_civicrm_config(&$config) {
  _hremerg_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hremerg_civicrm_xmlMenu(&$files) {
  _hremerg_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hremerg_civicrm_install() {
  //hack to translate Relationship to Emergency Contact
  $overrides = array(
    'enabled' => array(
      'wildcardMatch' => array(
        'Relationships' => 'Emergency Contacts',
        'Relationship' => 'Emergency Contact'
      ),
    ),
  );

  $config = CRM_Core_Config::singleton();
  $domain = new CRM_Core_DAO_Domain();
  $domain->find(TRUE);

  if ($domain->locales && $config->localeCustomStrings) {
    // for multilingual
    $addReplacements = $config->localeCustomStrings;
    $addReplacements[$config->lcMessages] = $overrides;
    $stringOverride = serialize($addReplacements);
  }
  else {
    // for single language
    $stringOverride = serialize(array($config->lcMessages => $overrides));
  }

  $params = array('locale_custom_strings' => $stringOverride);
  $id = CRM_Core_Config::domainID();

  $wordReplacementSettings = CRM_Core_BAO_Domain::edit($params, $id);

  if ($wordReplacementSettings) {
    // Reset navigation
    CRM_Core_BAO_Navigation::resetNavigation();
    // Clear js string cache
    CRM_Core_Resources::singleton()->flushStrings();
  }


  return _hremerg_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hremerg_civicrm_uninstall() {
  return _hremerg_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hremerg_civicrm_enable() {
  return _hremerg_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hremerg_civicrm_disable() {
  return _hremerg_civix_civicrm_disable();
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
function hremerg_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hremerg_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hremerg_civicrm_managed(&$entities) {
  return _hremerg_civix_civicrm_managed($entities);
}
