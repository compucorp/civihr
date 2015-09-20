<?php

require_once 'contactsummary.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function contactsummary_civicrm_config(&$config) {
  _contactsummary_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function contactsummary_civicrm_xmlMenu(&$files) {
  _contactsummary_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function contactsummary_civicrm_install() {
  _contactsummary_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function contactsummary_civicrm_uninstall() {
  _contactsummary_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function contactsummary_civicrm_enable() {
  _contactsummary_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function contactsummary_civicrm_disable() {
  _contactsummary_civix_civicrm_disable();
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
function contactsummary_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _contactsummary_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function contactsummary_civicrm_managed(&$entities) {
  _contactsummary_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function contactsummary_civicrm_caseTypes(&$caseTypes) {
  _contactsummary_civix_civicrm_caseTypes($caseTypes);
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
function contactsummary_civicrm_angularModules(&$angularModules) {
_contactsummary_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function contactsummary_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _contactsummary_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function contactsummary_civicrm_preProcess($formName, &$form) {

}

*/

/**
 * Implementation of hook_civicrm_pageRun
 */
function contactsummary_civicrm_pageRun($page) {
    if ($page instanceof CRM_Contact_Page_View_Summary) {

        CRM_Core_Region::instance('page-footer')->add(array(
            'type' => 'markup',
            'markup' => '<script data-main="'
                .CRM_Core_Resources::singleton()->getUrl('org.civicrm.contactsummary', CRM_Core_Config::singleton()->debug ? 'js/contactsummary-main' : 'dist/contactsummary-main', FALSE).
                '" src="'
                .CRM_Core_Resources::singleton()->getUrl('org.civicrm.contactsummary', 'js/vendor/require.js', TRUE).
                '"></script>',
            'weight' => 1003
        ));

        CRM_Core_Resources::singleton()
            ->addStyleFile('org.civicrm.contactsummary', 'css/contactsummary.css');
    }
}

/**
 * Implementation of hook_civicrm_tabs
 */

function contactsummary_civicrm_tabs(&$tabs) {
    $tabs[] = Array(
        'id'        => 'contactsummary',
        'url'       => CRM_Utils_System::url('civicrm/contact-summary'),
        'title'     => ts('Contact Summary'),
        'weight'    => 1
    );
}
