<?php

require_once 'styleguide.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function styleguide_civicrm_config(&$config) {
  _styleguide_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function styleguide_civicrm_xmlMenu(&$files) {
  _styleguide_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function styleguide_civicrm_install() {
  _styleguide_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function styleguide_civicrm_uninstall() {
  _styleguide_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function styleguide_civicrm_enable() {
  _styleguide_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function styleguide_civicrm_disable() {
  _styleguide_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function styleguide_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _styleguide_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function styleguide_civicrm_managed(&$entities) {
  _styleguide_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function styleguide_civicrm_caseTypes(&$caseTypes) {
  _styleguide_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function styleguide_civicrm_angularModules(&$angularModules) {
_styleguide_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function styleguide_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _styleguide_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function styleguide_civicrm_navigationMenu(&$menu) {
  _styleguide_civix_insert_navigation_menu($menu, 'Support/Developer', array(
    'label' => ts('Style Guide', array('domain' => 'org.civicrm.styleguide')),
    'name' => 'developer_styleguide',
    'permission' => 'access CiviCRM',
    'child' => array(),
    'operator' => 'OR',
    'separator' => 0,
  ));
  foreach (Civi::service('style_guides')->getAll() as $styleGuide) {
    _styleguide_civix_insert_navigation_menu($menu, 'Support/Developer/developer_styleguide', array(
      'label' => $styleGuide['label'],
      'name' => 'developer_styleguide_' . $styleGuide['name'],
      'url' => 'civicrm/styleguide/' . $styleGuide['name'],
      'permission' => 'access CiviCRM',
      'operator' => 'OR',
      'separator' => 0,
    ));
  }
}

/**
 * Implements hook_civicrm_container().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_container
 */
function styleguide_civicrm_container(\Symfony\Component\DependencyInjection\ContainerBuilder $container) {
  $container->setDefinition('style_guides', new \Symfony\Component\DependencyInjection\Definition(
    'CRM_StyleGuide_StyleGuides',
    array()
  ));

}