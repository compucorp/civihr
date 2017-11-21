<?php

require_once 'appraisals.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function appraisals_civicrm_config(&$config) {
  _appraisals_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function appraisals_civicrm_xmlMenu(&$files) {
  _appraisals_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function appraisals_civicrm_install() {
  _appraisals_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function appraisals_civicrm_uninstall() {
  _appraisals_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function appraisals_civicrm_enable() {
  _appraisals_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function appraisals_civicrm_disable() {
  _appraisals_civix_civicrm_disable();
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
function appraisals_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _appraisals_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function appraisals_civicrm_managed(&$entities) {
  _appraisals_civix_civicrm_managed($entities);
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
function appraisals_civicrm_caseTypes(&$caseTypes) {
  _appraisals_civix_civicrm_caseTypes($caseTypes);
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
function appraisals_civicrm_angularModules(&$angularModules) {
_appraisals_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function appraisals_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _appraisals_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_entityTypes
 */
function appraisals_civicrm_entityTypes(&$entityTypes) {
    $entityTypes[] = array(
        'name' => 'AppraisalCycle',
        'class' => 'CRM_Appraisals_DAO_AppraisalCycle',
        'table' => 'civicrm_appraisal_cycle',
    );
    $entityTypes[] = array(
        'name' => 'Appraisal',
        'class' => 'CRM_Appraisals_DAO_Appraisal',
        'table' => 'civicrm_appraisal',
    );
    $entityTypes[] = array(
        'name' => 'AppraisalCriteria',
        'class' => 'CRM_Appraisals_DAO_AppraisalCriteria',
        'table' => 'civicrm_appraisal_criteria',
    );
}

/**
 * Implementation of hook_civicrm_tabs
 * this tab should appear after absences tab directly
 * and since absences tab weight is
 * set to 10 we chose this to be 20
 * to give some room for other extensions to place
 * their tabs between these two.
 */

function appraisals_civicrm_tabs(&$tabs) {
    CRM_Appraisals_Page_Appraisals::registerScripts();

    $tabs[] = Array(
        'id'        => 'appraisals',
        'url'       => CRM_Utils_System::url('civicrm/contact/view/appraisals'),
        'title'     => ts('Appraisals'),
        'weight'    => 20,
    );
}

/**
 * Implementation of hook_civicrm_pageRun
 */
function appraisals_civicrm_pageRun($page) {
    if ($page instanceof CRM_Contact_Page_View_Summary ||
        $page instanceof CRM_Appraisals_Page_Dashboard) {

        CRM_Core_Resources::singleton()->addVars('appraisals', array(
            'baseURL' => CRM_Extension_System::singleton()->getMapper()->keyToUrl('uk.co.compucorp.civicrm.appraisals')
        ));

        CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.appraisals', 'css/civiappraisals.css');

        if ($page instanceof CRM_Appraisals_Page_Dashboard) {
            CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.shoreditch', 'base/js/tab.js', 1009);

            // Temporary, necessary to use the mocked API data
            CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.reqangular', 'dist/reqangular.mocks.min.js', 1010);

            CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.appraisals', 'js/dist/appraisals.min.js', 1010);
        }
    }
}

/**
 * Implementation of hook_civicrm_buildForm
 *
 * @params string $formName - the name of the form
 *         object $form - reference to the form object
 * @return void
 */
function appraisals_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Export_Form_Select') {
    $_POST['unchange_export_selected_column'] = TRUE;
    if (!empty($form->_submitValues) && $form->_submitValues['exportOption'] == CRM_Export_Form_Select::EXPORT_SELECTED) {
      $_POST['unchange_export_selected_column'] = FALSE;
    }
  }
}

function appraisals_civicrm_export( $exportTempTable, $headerRows, $sqlColumns, $exportMode ) {
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
