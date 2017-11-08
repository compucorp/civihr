<?php

require_once 'hrcore.civix.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference as Reference;
use CRM_HRCore_Service_DrupalUserService as DrupalUserService;
use CRM_HRCore_Service_DrupalRoleService as DrupalRoleService;
use CRM_HRCore_SearchTask_ContactFormSearchTaskAdder as ContactFormSearchTaskAdder;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function hrcore_civicrm_config(&$config) {
  _hrcore_hrui_civicrm_config($config);
  _hrcore_civix_civicrm_config($config);
  $smarty = CRM_Core_Smarty::singleton();
  array_push($smarty->plugins_dir, __DIR__ . '/CRM/Smarty/plugins');
}

/**
 * Implements hook_civicrm_searchTasks().
 *
 * @param string $objectName
 * @param array $tasks
 */
function hrcore_civicrm_searchTasks($objectName, &$tasks) {
  $taskAdders = [
    ContactFormSearchTaskAdder::class
  ];

  foreach ($taskAdders as $adder) {
    if ($adder::shouldAdd($objectName)) {
      $adder::add($tasks);
    }
  }
}

function hrcore_civicrm_summaryActions( &$actions, $contactID ) {
  $otherActions = CRM_Utils_Array::value('otherActions', $actions, []);
  $userAdd = CRM_Utils_Array::value('user-add', $otherActions, []);

  if (empty($userAdd)) {
    return;
  }

  // replace default action with link to custom form
  $userAdd['title'] = ts('Create User Account');
  $userAdd['description'] = ts('Create User Account');
  $link = '/civicrm/user/create-account?cid=%d';
  $userAdd['href'] = sprintf($link, $contactID);
  $actions['otherActions']['user-add'] = $userAdd;
}

/**
 * Implements hook_civicrm_container().
 *
 * @param ContainerBuilder $container
 */
function hrcore_civicrm_container($container) {
  $container->register('drupal_role_service', DrupalRoleService::class);
  $container->setDefinition(
    'drupal_user_service',
    new Definition(DrupalUserService::class, [new Reference('drupal_role_service')])
  );
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function hrcore_civicrm_xmlMenu(&$files) {
  _hrcore_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function hrcore_civicrm_install() {
  _hrcore_hrui_civicrm_install();
  _hrcore_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function hrcore_civicrm_uninstall() {
  _hrcore_hrui_civicrm_uninstall();
  _hrcore_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function hrcore_civicrm_enable() {
  _hrcore_hrui_civicrm_enable();
  _hrcore_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function hrcore_civicrm_disable() {
  _hrcore_hrui_civicrm_disable();
  _hrcore_civix_civicrm_disable();
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
function hrcore_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrcore_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function hrcore_civicrm_managed(&$entities) {
  _hrcore_civix_civicrm_managed($entities);
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
function hrcore_civicrm_caseTypes(&$caseTypes) {
  _hrcore_civix_civicrm_caseTypes($caseTypes);
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
function hrcore_civicrm_angularModules(&$angularModules) {
  _hrcore_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function hrcore_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _hrcore_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_apiWrappers
 */
function hrcore_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if ($apiRequest['action'] === 'get') {
    $wrappers[] = new CRM_HRCore_APIWrapper_DefaultLimitRemover();
  }
}

/**
 * Implementation of hook_civicrm_pre hook.
 *
 * @param string $op
 *   Operation being done
 * @param string $objectName
 *   Name of the object on which the operation is being done
 * @param int $objectId
 *   ID of the record the object instantiates
 * @param array $params
 *   Parameter array being used to call the operation
 */
function hrcore_civicrm_pre($op, $objectName, $objectId, &$params) {
  $listeners = [
    new CRM_HRCore_Hook_Pre_PrimaryAddressSetter(),
  ];

  foreach ($listeners as $currentListener) {
    $currentListener->handle($op, $objectName, $objectId, $params);
  }
}

/**
 * Implements hrcore_civicrm_pageRun.
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_pageRun/
 */
function hrcore_civicrm_pageRun($page) {
  _hrcore_hrui_civicrm_pageRun($page);
  _hrcore_add_js_session_vars();
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu/
 */
function hrcore_civicrm_navigationMenu(&$params) {
  _hrcore_hrui_civicrm_navigationMenu($params);
  _hrcore_renameMenuLabel($params, 'Contacts', 'Staff');
  _hrcore_renameMenuLabel($params, 'Administer', 'Configure');
  _hrcore_civix_insert_navigation_menu($params, '', [
    'name' => ts('ssp'),
    'label' => ts('Self Service Portal'),
    'url' => 'dashboard',
  ]);
}

/**
 * Implements hook_civicrm_permission().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_permission/
 */
function hrcore_civicrm_permission(&$permissions) {
  $permissions += [
    'access CiviCRM developer menu and tools' => ts('Access CiviCRM developer menu and tools')
  ];
}

/**
 * Renames a menu with the given new label
 *
 * @param array $params
 * @param string $menuName
 * @param string $newLabel
 */
function _hrcore_renameMenuLabel(&$params, $menuName, $newLabel) {
  $menuItemID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', $menuName, 'id', 'name');
  $params[$menuItemID]['attributes']['label'] = $newLabel;
}

/**
 * This function adds the session variable to CRM.vars object.
 */
function _hrcore_add_js_session_vars() {
  CRM_Core_Resources::singleton()->addVars('session', [
    'contact_id' => CRM_Core_Session::getLoggedInContactID()
  ]);
}



/**
 * Implements hrcore_civicrm_buildForm.
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_buildForm/
 */
function hrcore_civicrm_buildForm($formName, &$form) {
  _hrcore_hrui_civicrm_buildForm($formName, $form);
}

/**
 * Implements hrcore_civicrm_postProcess.
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_postProcess/
 */
function hrcore_civicrm_postProcess($formName, &$form) {
  _hrcore_hrui_civicrm_postProcess($formName, $form);
}

/**
 * Implements hrcore_civicrm_tabset.
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_tabset/
 */
function hrcore_civicrm_tabset($tabsetName, &$tabs, $contactID) {
  _hrcore_hrui_civicrm_tabset($tabsetName, $tabs, $contactID);
}

/**
 * Implements hrcore_civicrm_alterMenu.
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_alterMenu/
 */
function hrcore_civicrm_alterMenu(&$items) {
  _hrcore_hrui_civicrm_alterMenu($items);
}

/**
 * Implements hrcore_civicrm_alterContent.
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_alterContent/
 */
function hrcore_civicrm_alterContent(&$content, $context, $tplName, &$object) {
  _hrcore_hrui_civicrm_alterContent($content, $context, $tplName, $object);
}

/**
 * Implements hrcore_civicrm_summary.
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_summary/
 */
function hrcore_civicrm_summary($contactId, &$content, &$contentPlacement) {
  _hrcore_hrui_civicrm_summary($contactId, $content, $contentPlacement);
}

//////////////////////
//                  //
//       HRUI       //
//                  //
//////////////////////

function _hrcore_hrui_civicrm_pageRun($page) {
  if (isset($_GET['snippet']) && $_GET['snippet'] == 'json') {
    return;
  }

  if ($page instanceof CRM_Contact_Page_DashBoard) {
    CRM_Utils_System::setTitle(ts('CiviHR Home'));
  }

  if ($page instanceof CRM_Contact_Page_View_Summary) {
    CRM_Core_Resources::singleton()->addSetting(array('pageName' => 'viewSummary'));

    //set government field value for individual page
    $contactType = CRM_Contact_BAO_Contact::getContactType(CRM_Utils_Request::retrieve('cid', 'Integer'));

    $isEnabled = __hrui_is_extension_enabled('org.civicrm.hrident');

    if ($isEnabled && $contactType == 'Individual') {
      $hideGId = civicrm_api3('CustomField', 'getvalue', array('custom_group_id' => 'Identify', 'name' => 'is_government', 'return' => 'id'));
      CRM_Core_Resources::singleton()
        ->addSetting(array(
          'cid' => CRM_Utils_Request::retrieve('cid', 'Integer'),
          'hideGId' => $hideGId)
        );
    }
  }

  if (CRM_Core_Config::singleton()->debug) {
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrcore', 'js/src/civihr-popup/attrchange.js');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrcore', 'js/src/civihr-popup/civihr-popup.js');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrcore', 'js/src/hrcore.js');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrcore', 'js/src/contact.js');
  } else {
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrcore', 'js/dist/hrcore.min.js');
  }

  CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrcore', 'css/hrcore.css');
}

function _hrcore_hrui_civicrm_buildForm($formName, &$form) {
  CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrcore', 'css/hrcore.css');

  if (CRM_Core_Config::singleton()->debug) {
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrcore', 'js/src/hrcore.js');
  } else {
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrcore', 'js/dist/hrcore.min.js');
  }

  if ($form instanceof CRM_Contact_Form_Contact) {
    CRM_Core_Resources::singleton()
      ->addSetting(array('formName' => 'contactForm'));

    $phoneIndex = 2;
    if (__hrui_phone_is_empty($phoneIndex, $form)) {
      __hrui_set_phone_type_as_mobile($phoneIndex, $form);
      __hrui_set_phone_location_to_the_default_location($phoneIndex, $form);
    }
  }

  if ($formName == 'CRM_Admin_Form_Extensions') {
    $extensionKey= CRM_Utils_Request::retrieve('key', 'String', $this);
    if ($extensionKey == 'uk.co.compucorp.civicrm.hrsampledata') {
      $title = ts("Be Careful");
      $message = ts("Installing/Uninstalling this extension will remove all existing data, so make sure to create a backup first !");

      CRM_Core_Session::setStatus($message, $title, 'no-popup crm-error', ['expires' => 0]);
    }
  }

  if ($formName === 'CRM_Admin_Form_Options' && $form->elementExists('value')) {
    $form->removeElement('value');
  }

  if ($form instanceof CRM_Admin_Form_Setting_Localization) {
    $form->removeElement('makeMultilingual');
  }
}

function _hrcore_hrui_civicrm_postProcess($formName, &$form) {
  $isEnabled = __hrui_is_extension_enabled('org.civicrm.hrident');

  if ($formName == 'CRM_Contact_Form_Contact'
    && $isEnabled
    && !empty($form->_submitValues['GovernmentId'])
    && $form->_contactType == 'Individual'
  ) {
    $govFieldId = CRM_HRIdent_Page_HRIdent::retreiveContactFieldId('Identify');
    $govFieldIds = CRM_HRIdent_Page_HRIdent::retreiveContactFieldValue($form->_contactId);
    if (!empty($govFieldId)) {
      if (empty($govFieldIds)) {
        $govFieldIds['id'] = NULL;
      }
      $customParams = array(
        "custom_{$govFieldId['Type']}{$govFieldIds['id']}" => $form->_submitValues['govTypeOptions'],
        "custom_{$govFieldId['Number']}{$govFieldIds['id']}" => $form->_submitValues['GovernmentId'],
        "custom_{$govFieldId['is_government']}{$govFieldIds['id']}" => 1,
        "entity_id" => $form->_contactId,
      );
      civicrm_api3('CustomValue', 'create', $customParams);
    }
  }
}

function _hrcore_hrui_civicrm_config(&$config) {
  global $civicrm_setting;
  $civicrm_setting['CiviCRM Preferences']['communityMessagesUrl'] = FALSE;
}

function _hrcore_hrui_civicrm_install() {
  //delete default tag of civicrm
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_tag WHERE name IN ('Non-profit', 'Company', 'Government Entity', 'Major Donor', 'Volunteer')");

  // make sure only relevant components are enabled
  $params = array(
    'domain_id' => CRM_Core_Config::domainID(),
    'enable_components' => array('CiviReport','CiviCase'),
  );
  $result = civicrm_api3('setting', 'create', $params);
  if (CRM_Utils_Array::value('is_error', $result, FALSE)) {
    CRM_Core_Error::debug_var('setting-create result for enable_components', $result);
    throw new CRM_Core_Exception('Failed to create settings for enable_components');
  }

  // Disable Household contact type
  $contactTypeId = CRM_Core_DAO::getFieldValue(
    'CRM_Contact_DAO_ContactType',
    'Household',
    'id',
    'name'
  );
  if ($contactTypeId) {
    $paramsContactType = array(
      'name' => "Household",
      'id' => $contactTypeId,
      'is_active' => FALSE,
    );
    $resultContactType = civicrm_api3('contact_type', 'create', $paramsContactType);
    if (CRM_Utils_Array::value('is_error', $resultContactType, FALSE)) {
      CRM_Core_Error::debug_var('contact_type-create result for is_active', $resultContactType);
      throw new CRM_Core_Exception('Failed to disable contact type');
    }
  }


  // Delete unnecessary reports
  $reports = array("Constituent Summary", "Constituent Detail", "Current Employers");
  if (!empty($reports)) {
    foreach ($reports as $reportTitle) {
      $reportID = CRM_Core_DAO::getFieldValue(
        'CRM_Report_DAO_ReportInstance',
        $reportTitle,
        'id',
        'title'
      );
      if ($reportID) {
        $paramsReport = array(
          'id' => $reportID,
        );
        $resultContactType = civicrm_api3('report_instance', 'delete', $paramsReport);
        if (CRM_Utils_Array::value('is_error', $resultContactType, FALSE)) {
          CRM_Core_Error::debug_var('contact_type-create result for is_active', $resultContactType);
          throw new CRM_Core_Exception('Failed to disable contact type');
        }
      }
    }
  }

  // Reset Navigation
  CRM_Core_BAO_Navigation::resetNavigation();

  // get a list of all tab options
  $options = CRM_Core_OptionGroup::values('contact_view_options', TRUE, FALSE);
  $tabsToUnset = array($options['Activities'], $options['Tags']);

  // get tab options from DB
  $options = __hrui_getViewOptionsSetting();

  // unset activity & tag tab options
  foreach ($tabsToUnset as $key) {
    unset($options[$key]);
  }
  $options = array_keys($options);

  // set modified options in the DB
  __hrui_setViewOptionsSetting($options);
  __hrui_setActiveFields(FALSE);

  //hide communication preferences block
  $groupID = CRM_Core_DAO::getFieldValue(
    'CRM_Core_DAO_OptionGroup',
    'contact_edit_options',
    'id',
    'name'
  );

  $params = array(
    'option_group_id' => $groupID,
    'name' => 'CommunicationPreferences',
  );

  CRM_Core_BAO_OptionValue::retrieve($params, $defaults);
  $defaults['is_active'] = 0;
  CRM_Core_BAO_OptionValue::create($defaults);

  // Change the blog URL
  civicrm_api3('setting', 'create', array(
    'blogUrl' => 'https://civicrm.org/taxonomy/term/198/feed',
  ));
  __hrui_wordReplacement(FALSE);
}

function _hrcore_hrui_civicrm_uninstall() {
  // get a list of all tab options
  $options = CRM_Core_OptionGroup::values('contact_view_options', TRUE, FALSE);
  $tabsToSet = array($options['Activities'], $options['Tags']);

  // get tab options from DB
  $options = __hrui_getViewOptionsSetting();

  // set activity & tag tab options
  foreach ($tabsToSet as $key) {
    $options[$key] = 1;
  }
  $options = array_keys($options);

  // set modified options in the DB
  __hrui_setViewOptionsSetting($options);
  __hrui_setActiveFields(TRUE);
  // show communication preferences block
  $groupID = CRM_Core_DAO::getFieldValue(
    'CRM_Core_DAO_OptionGroup',
    'contact_edit_options',
    'id',
    'name'
  );

  $params = array(
    'option_group_id' => $groupID,
    'name' => 'CommunicationPreferences',
  );

  CRM_Core_BAO_OptionValue::retrieve($params, $defaults);
  $defaults['is_active'] = 1;
  CRM_Core_BAO_OptionValue::create($defaults);
  __hrui_wordReplacement(TRUE);

  // Remove 'Import Custom Fields' Navigation item and reset the menu
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_navigation WHERE name = 'import_custom_fields'");
  CRM_Core_BAO_Navigation::resetNavigation();
}

function _hrcore_hrui_civicrm_enable() {
  __hrui_setActiveFields(FALSE);
  __hrui_wordReplacement(FALSE);
  __hrui_menuSetActive(1);
}

function _hrcore_hrui_civicrm_disable() {
  __hrui_setActiveFields(TRUE);
  __hrui_wordReplacement(TRUE);
  __hrui_menuSetActive(0);
}

function _hrcore_hrui_civicrm_tabset($tabsetName, &$tabs, $contactID) {
  $tabsToRemove = array();

  if (__hrui_check_extension('uk.co.compucorp.civicrm.tasksassignments')) {
    $tabsToRemove[] = 'case';
  }

  __hrui_alter_tabs($tabs, $tabsToRemove);
}

function _hrcore_hrui_civicrm_alterMenu(&$items) {
  $items['civicrm/api']['access_arguments'] =[['access CiviCRM', 'access CiviCRM developer menu and tools'], "and"];
  $items['civicrm/styleguide']['access_arguments'] =[['access CiviCRM', 'access CiviCRM developer menu and tools'], "and"];
}

function _hrcore_hrui_civicrm_navigationMenu(&$params) {
  __hrui_customImportMenuItems($params);
  __hrui_coreMenuChanges($params);
  __hrui_createHelpMenu($params);
  // __hrui_createDeveloperMenu($params);
  __hrui_setDynamicMenuIcons($params);
}

function _hrcore_hrui_civicrm_alterContent(&$content, $context, $tplName, &$object) {
  $smarty = CRM_Core_Smarty::singleton();

  // fetch data to the new summary page UI
  if($context == 'page' && $tplName == "CRM/Contact/Page/View/Summary.tpl" ) {
    $content .= __hrui_updateContactSummaryUI();
  }

  if ($context == "form" && $tplName == "CRM/Contact/Import/Form/MapField.tpl" ) {
    $columnToHide = array(
      'formal_title',
      'job_title',
      'legal_identifier',     //Legal Identifier
      'addressee',            //Addressee
      'addressee_custom',     //Addressee Custom
      'do_not_email',         //Do Not Email
      'do_not_mail',          //Do Not Mail
      'do_not_phone',         //Do Not Phone
      'do_not_sms',           //Do Not Sms
      'do_not_trade',         //Do Not Trade
      'email_greeting',       //Email Greeting
      'email_greeting_custom',//Email Greeting Custom
      'geo_code_1',           //Latitude
      'master_id',            //Master Address Belongs To
      'is_opt_out',           //No Bulk Emails (User Opt Out)
      'openid',               //OpenID
      'postal_greeting',      //Postal Greeting
      'postal_greeting_custom',//Postal Greeting Custom
      'preferred_communication_method',//Preferred Communication Method
      'preferred_language',    //Preferred Language
      'preferred_mail_format',//Preferred Mail Format
      'signature_html',       //Signature Html
      'signature_text',       //Signature Text
      'user_unique_id'        //Unique ID (OpenID)
    );
    $relations = CRM_Contact_BAO_Relationship::getContactRelationshipType(NULL, NULL, NULL, 'Individual', FALSE, 'name', TRUE );
    $relationsToHide = array( 'Benefits Specialist', 'Benefits Specialist is','Case Coordinator','Case Coordinator is','Health Services Coordinator','Health Services Coordinator is','Homeless Services Coordinator','Homeless Services Coordinator is','Senior Services Coordinator','Senior Services Coordinator is', 'Partner of' );
    $hideRelations = array_intersect($relations, $relationsToHide);
    $str = '';
    foreach($columnToHide as $columnToHide) {
      $str .= "$('select[name^=\"mapper\"] option[value={$columnToHide}]').remove();";
    }
    foreach($hideRelations as $columnToHide => $columnName) {
      $str .= "$('select[name^=\"mapper\"] option[value={$columnToHide}]').remove();";
    }

    $content .="<script type=\"text/javascript\">
      CRM.$(function($) {
        {$str};
        $('select[name^=\"mapper\"]').on('change', function() {
          {$str};
        });
      });
    </script>";
  }

  if ($context == "form" && $tplName == "CRM/Contact/Form/Contact.tpl" ) {
    $content .="<script type=\"text/javascript\">
      CRM.$(function($) {
        if(!$('#internal_identifier_display').val()){
          $('#first_name').keyup(function() {
            var value = $( this ).val();
            $('#nick_name').val(value);
            });
        }
      });
    </script>";
  }

  if($context == 'page' && ($tplName == "CRM/Case/Page/DashBoard.tpl" || $tplName == "CRM/Dashlet/Page/CaseDashboard.tpl")) {
    if($tplName == "CRM/Case/Page/DashBoard.tpl") {
       $id = '.page-civicrm-case';
    }
    else {
      $id = '#case_dashboard_dashlet';
    }
    $content .="<script type=\"text/javascript\">
      CRM.$(function($) {
        $('{$id} table.report tr th strong').each(function () {
          var app = $(this).text();
          if (app == 'Application') {
            $(this).parent('th').parent('tr').remove();
          }
        });
      });
    </script>";
  }

 if ($tplName == 'CRM/Profile/Form/Edit.tpl' && $smarty->_tpl_vars['context'] == 'dialog' && $smarty->_tpl_vars['ufGroupName'] == 'new_individual') {
   $content .="<script type=\"text/javascript\">
     CRM.$(function($) {
       $('.ui-dialog').css({'top':'10%'});
     });
   </script>";
  }

  if ($tplName === 'CRM/Admin/Form/Setting/Localization.tpl') {
    // hide multiple language description
    $content = str_replace('<h3>Multiple Languages Support</h3>', '', $content);
    $content .= sprintf(
      '<script>%s</script>',
      'CRM.$(".crm-localization-form-block-description").hide();'
    );
  }
}

function _hrcore_hrui_civicrm_summary($contactId, &$content, &$contentPlacement) {
  $uf = _get_uf_match_contact($contactId);
  if (empty($uf) || empty($uf['uf_id'])) {
    return NULL;
  }
  $user = user_load($uf['uf_id']);
  $content['userid'] = $uf['uf_id'];
  $content['username'] = !empty($user->name) ? $user->name : '';
  $contentPlacement = NULL;
}



/**
 * Sets the location type of the phone with the given index to the default
 * location type.
 *
 * @param int $phoneIndex
 *  The index of phone in the contact form
 * @param CRM_Core_Form $form
 *  The Contact Form instance
 */
function __hrui_set_phone_location_to_the_default_location($phoneIndex, $form) {
  $locationId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_LocationType', 1, 'id', 'is_default');

  if($locationId) {
    $form->setDefaults([
      "phone[{$phoneIndex}][location_type_id]" => $locationId
    ]);
  }
}

/**
 * Sets the phone type of the phone with the given index as 'Mobile'.
 *
 * @param $phoneIndex
 *  The index of phone in the contact form
 * @param CRM_Core_Form $form
 *  The Contact Form instance
 */
function __hrui_set_phone_type_as_mobile($phoneIndex, $form) {
  __hrui_set_phone_type($phoneIndex, $form, 'Mobile');
}

/**
 * Sets the phone type of the phone with the given index to the type given by
 * $type.
 *
 * @param int $phoneIndex
 *   The index of phone in the contact form
 * @param CRM_Core_Form $form
 *   The Contact Form instance
 * @param string $type
 *   The new phone type. Valid values are those from the phone_type option list
 */
function __hrui_set_phone_type($phoneIndex, $form, $type) {
  $elementName = "phone[{$phoneIndex}][phone_type_id]";

  if(!$form->elementExists($elementName)) {
    return;
  }

  $phoneType  = $form->getElement($elementName);
  $phoneValue = CRM_Core_OptionGroup::values('phone_type');
  $phoneKey   = CRM_Utils_Array::key($type, $phoneValue);
  if($phoneKey) {
    $phoneType->setSelected($phoneKey);
  }
}

/**
 * Returns if the contact form has a phone with the given index and it's empty
 *
 * @param int $phoneIndex
 *  The index of phone in the contact form
 * @param CRM_Core_Form $form
 *  The Contact Form instance
 *
 * @return bool
 */
function __hrui_phone_is_empty($phoneIndex, $form) {
  return $form->elementExists("phone[{$phoneIndex}][phone]") &&
         empty($form->getElementValue("phone[{$phoneIndex}][phone]"));
}

/**
 * get tab options from DB using setting-get api
 */
function __hrui_getViewOptionsSetting() {
  $domainID = CRM_Core_Config::domainID();
  $params = array(
    'domain_id' => $domainID,
    'return' => 'contact_view_options',
  );
  $result = civicrm_api3('setting', 'get', $params);
  if (CRM_Utils_Array::value('is_error', $result, FALSE)) {
    CRM_Core_Error::debug_var('setting-get result for contact_view_options', $result);
    throw new CRM_Core_Exception('Failed to retrieve settings for contact_view_options');
  }
  return array_flip($result['values'][$domainID]['contact_view_options']);
}

/**
 * set modified options in the DB using setting-create api
 */
function __hrui_setViewOptionsSetting($options = array()) {
  $domainID = CRM_Core_Config::domainID();
  $params = array(
    'domain_id' => $domainID,
    'contact_view_options' => $options,
  );
  $result = civicrm_api3('setting', 'create', $params);
  if (CRM_Utils_Array::value('is_error', $result, FALSE)) {
    CRM_Core_Error::debug_var('setting-create result for contact_view_options', $result);
    throw new CRM_Core_Exception('Failed to create settings for contact_view_options');
  }
  return TRUE;
}


function __hrui_wordReplacement($isActive) {
  if( $isActive) {
    CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'CiviCRM News' WHERE name = 'blog' ");
    CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'Case Dashboard Dashlet' WHERE name = 'casedashboard' ");
  }
  else {
    CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'CiviHR News' WHERE name = 'blog' ");
    CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'Assignments Dashlet' WHERE name = 'casedashboard' ");
  }
}

function __hrui_setActiveFields($setActive) {
  $setActive = $setActive ? 1 : 0;
  //disable/enable optionGroup and optionValue
  $query = "UPDATE civicrm_option_value JOIN civicrm_option_group ON civicrm_option_group.id = civicrm_option_value.option_group_id SET civicrm_option_value.is_active = {$setActive} WHERE civicrm_option_group.name IN ('custom_most_important_issue', 'custom_marital_status')";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$setActive} WHERE name IN ('custom_most_important_issue', 'custom_marital_status')");

  //disable/enable customgroup and customvalue
  $sql = "UPDATE civicrm_custom_field JOIN civicrm_custom_group ON civicrm_custom_group.id = civicrm_custom_field.custom_group_id SET civicrm_custom_field.is_active = {$setActive} WHERE civicrm_custom_group.name = 'constituent_information'";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name = 'constituent_information'");
  CRM_Core_DAO::executeQuery("UPDATE civicrm_relationship_type SET is_active = {$setActive} WHERE name_a_b IN ( 'Employee of', 'Head of Household for', 'Household Member of' )");
}

/**
 * Check if the extension with the given key is enabled
 *
 * @param string $extensionKey
 * @return boolean
 */
function __hrui_check_extension($extensionKey)  {
  return (boolean) CRM_Core_DAO::getFieldValue(
    'CRM_Core_DAO_Extension',
    $extensionKey,
    'is_active',
    'full_name'
  );
}

/**
 * 1) we alter the weights for these tabs here
 * since these tabs are not created by hook_civicrm_tab
 * and the only way to alter their weights is here
 * by taking advantage of &$tabs variable.
 * 2) we set assignments tab to 30 since it should appear
 * after appraisals tab directly which have the weight of 20.
 * 3) the weight increased by 10 between every tab
 * to give a large space for other tabs to be inserted
 * between any two without altering other tabs weights.
 * 4) we remove a tab if present in the $tabsToRemove list
 *
 * @param array $tabs
 * @param array $tabsToRemove
 */
function __hrui_alter_tabs(&$tabs, $tabsToRemove) {
  foreach ($tabs as $i => $tab) {
    if (in_array($tab['id'], $tabsToRemove)) {
      unset($tabs[$i]);
      continue;
    }

    switch($tab['title'])  {
      case 'Assignments':
        $tabs[$i]['weight'] = 30;
        break;
      case 'Emergency Contacts':
        $tabs[$i]['weight'] = 80;
        break;
      case 'Relationships':
        $tabs[$i]['weight'] = 90;
        $tabs[$i]['title'] = 'Managers';
        break;
      case 'Bank Details':
        $tabs[$i]['weight'] = 100;
        break;
      case 'Career History':
        $tabs[$i]['weight'] = 110;
        break;
      case 'Medical & Disability':
        $tabs[$i]['weight'] = 120;
        break;
      case 'Qualifications':
        $tabs[$i]['weight'] = 130;
        break;
      case 'Notes':
        $tabs[$i]['weight'] = 140;
        break;
      case 'Groups':
        $tabs[$i]['weight'] = 150;
        break;
      case 'Change Log':
        $tabs[$i]['weight'] = 160;
        break;
    }
  }
}


/**
 * Builds the custom HTML markup for the contact header section
 *
 * @param  Array $data contains details about the contact, the current contract, the departments and managers
 * @return string
 */
function __hrui_contactSummaryHeaderHtml($data) {
  $html = '';

  if (!empty($data['contact']['phone'])) {
    $html .= "<span class='crm-contact-detail'><strong>Phone:</strong> " . $data['contact']['phone'] . "</span>";
  }

  if (!empty($data['contact']['email'])) {
    $html .= "<span class='crm-contact-detail'><strong>Email:</strong> " . $data['contact']['email'] . "</span>";
  }

  $html .= "<br />";

  if (isset($data['current_contract'])) {
    $position = $location =  '';

    if (!empty($data['current_contract']->position)) {
      $position = "<strong>Position:</strong> " . $data['current_contract']->position;
    }

    if (!empty($data['current_contract']->location)) {
      $location .= "<strong>Normal place of work:</strong> " . $data['current_contract']->location;
    }

    $html .= "<span class='crm-contact-detail crm-contact-detail-position'>{$position}</span>";
    $html .= "<span class='crm-contact-detail crm-contact-detail-location'>{$location}</span>";

    if (!empty($data['departments'])) {
      $html .= "<span class='crm-contact-detail crm-contact-detail-departments'><strong>Department:</strong> " . $data['departments'] . "</span>";
    } else {
      $html .= "<span class='crm-contact-detail crm-contact-detail-departments'></span>";
    }

    if (!empty($data['managers'])) {
      $html .= "<span class='crm-contact-detail'><strong>Manager:</strong> " . $data['managers'] . "</span>";
    }
  }
  else {
    $html .= "<span class='crm-contact-detail crm-contact-detail-position'></span>";
    $html .= "<span class='crm-contact-detail crm-contact-detail-location'></span>";
    $html .= "<span class='crm-contact-detail crm-contact-detail-departments'></span>";
  }

  return $html;
}

/**
 * Builds the JS script that will alter the DOM of the contact summary DOM
 *
 * @param  Array $data contains details about the contact, the current contract, the departments and managers
 * @return string
 */
function __hrui_contactSummaryDOMScript($data) {
  $script = '';

  $script .= "<script type=\"text/javascript\">";
  $script .= "CRM.$(function($) {";
  $script .= "$('#contactname-block.crm-summary-block').wrap('<div class=\"crm-summary-block-wrap\" />');";

  if (!empty($data['contact']['image_URL'])) {
    $script .= "$('.crm-summary-contactname-block').prepend('<img class=\"crm-summary-contactphoto\" src=" . $data['contact']['image_URL'] . " />');";
  }

  if (empty($data['current_contract'])) {
    $script .= "$('.crm-summary-contactname-block').addClass('crm-summary-contactname-block-without-contract');";
  }

  $script .= "$('.crm-summary-block-wrap').append(\"<div class='crm-contact-detail-wrap' />\");";
  $script .= "$('.crm-contact-detail-wrap').append(\"" . __hrui_contactSummaryHeaderHtml($data) . "\");";

  $script .= "});";
  $script .= "</script>";

  return $script;
}

/**
 * Add new information in the contact header as the contact photo,
 * phone, department. All changes are made via Javascript.
 *
 * @return [String] Updated content markup
 */
function __hrui_updateContactSummaryUI() {
  $content = '';
  $departmentsList = $managersList = null;

  $contact_id = CRM_Utils_Request::retrieve( 'cid', 'Positive');

  /* $currentContractDetails contain current contact data including
   * Current ( Position = $currentContractDetails->position ) and
   * ( Normal Place of work =  $currentContractDetails->location )
  */
  $currentContractDetails = CRM_Hrjobcontract_BAO_HRJobContract::getCurrentContract($contact_id);

  // $departmentsList contain current roles departments list separated by comma
  if ($currentContractDetails)  {
    $departmentsArray = CRM_Hrjobroles_BAO_HrJobRoles::getCurrentDepartmentsList($currentContractDetails->contract_id);
    $departmentsList = implode(', ', $departmentsArray);
  }

  // $managersList contain current line managers list separated by comma
  if ($currentContractDetails)  {
    $managersArray = CRM_HRUI_Helper::getLineManagersList($contact_id);
    $managersList = implode(', ', $managersArray);
  }

  try {
    $contactDetails = civicrm_api3('Contact', 'getsingle', array(
      'sequential' => 1,
      'return' => array("phone", "email", "image_URL"),
      'id' => $contact_id,
    ));

    $content = __hrui_contactSummaryDOMScript(array(
      'contact' => $contactDetails,
      'current_contract' => $currentContractDetails,
      'departments' => $departmentsList,
      'managers' => $managersList,
    ));
  }
  catch (CiviCRM_API3_Exception $e) {
  }

  return $content;
}

/**
 * Generating Custom Fields import child menu items
 *
 */
function __hrui_customImportMenuItems(&$params) {
  $navId = CRM_Core_DAO::singleValueQuery("SELECT max(id) FROM civicrm_navigation");

  $customFieldsNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'import_custom_fields', 'id', 'name');
  $contactNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');

  if ($customFieldsNavId) {
    // Degrade gracefully on 4.4
    if (is_callable(array('CRM_Core_BAO_CustomGroup', 'getMultipleFieldGroup'))) {
      //  Get the maximum key of $params
      $multipleCustomData = CRM_Core_BAO_CustomGroup::getMultipleFieldGroup();

      $multiValuedData = NULL;
      foreach ($multipleCustomData as $key => $value) {
        ++$navId;
        $multiValuedData[$navId] = array (
          'attributes' => array (
            'label'      => $value,
            'name'       => $value,
            'url'        => 'civicrm/import/custom?reset=1&id='.$key,
            'permission' => 'access CiviCRM',
            'operator'   => null,
            'separator'  => null,
            'parentID'   => $customFieldsNavId,
            'navID'      => $navId,
            'active'     => 1
          )
        );
      }
      $params[$contactNavId]['child'][$customFieldsNavId]['child'] = $multiValuedData;
    }
  }
}

/**
 * Changes to some core menu items
 *
 */
function __hrui_coreMenuChanges(&$params) {
  // remove search items
  $searchNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Search...', 'id', 'name');
  $toRemove = [
    'Full-text search',
    'Search builder',
    'Custom searches',
    'Find Cases',
    'Find Activities',
  ];
  foreach($toRemove as $item) {
    if (
      in_array($item, ['Find Cases', 'Find Activities'])
      && !(__hrui_check_extension('uk.co.compucorp.civicrm.tasksassignments'))
    ) {
      continue;
    }
    $itemId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', $item , 'id', 'name');
    unset($params[$searchNavId]['child'][$itemId]);
  }

  // remove contact items
  $searchNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');
  $toRemove = [
    'New Tag',
    'Manage Tags (Categories)',
    'New Activity',
    'Import Activities',
    'Contact Reports',
  ];
  foreach($toRemove as $item) {
    if (
      in_array($item, ['New Activity', 'Import Activities'])
      && !(__hrui_check_extension('uk.co.compucorp.civicrm.tasksassignments'))
    ) {
      continue;
    }
    $itemId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', $item , 'id', 'name');
    unset($params[$searchNavId]['child'][$itemId]);
  }

  // remove main Reports menu
  $reportsNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Reports', 'id', 'name');
  unset($params[$reportsNavId]);

  // Remove Admin items
  $adminNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Administer', 'id', 'name');

  $civiReportNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'CiviReport', 'id', 'name');

  $civiCaseNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'CiviCase', 'id', 'name');
  $redactionRulesNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Redaction Rules', 'id', 'name');
  $supportNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Support', 'id', 'name');

  unset($params[$supportNavId]);
  unset($params[$adminNavId]['child'][$civiReportNavId]);
  unset($params[$adminNavId]['child'][$civiCaseNavId]['child'][$redactionRulesNavId]);
}

/**
 * Enable/Disable Menu items created by hrui extension
 *
 */
function __hrui_menuSetActive($isActive) {
  CRM_Core_DAO::executeQuery("UPDATE civicrm_navigation SET is_active = {$isActive} WHERE name = 'import_custom_fields'");
  CRM_Core_BAO_Navigation::resetNavigation();
}

/**
 * Creates Help Menu in navigation bar
 *
 * @param Array $menu List of available menu items
 */
function __hrui_createHelpMenu(&$menu) {
  _hrcore_civix_insert_navigation_menu($menu, '', [
    'name' => ts('Help'),
    'permission' => 'access CiviCRM',
  ]);

  _hrcore_civix_insert_navigation_menu($menu, 'Help', [
    'name' => ts('User Guide'),
    'url' => 'http://civihr-documentation.readthedocs.io/en/latest/',
    'target' => '_blank',
    'permission' => 'access CiviCRM'
  ]);

  _hrcore_civix_insert_navigation_menu($menu, 'Help', [
    'name' => ts('CiviHR website'),
    'url' => 'https://www.civihr.org/',
    'target' => '_blank',
    'permission' => 'access CiviCRM'
  ]);

  _hrcore_civix_insert_navigation_menu($menu, 'Help', [
    'name' => ts('Get support'),
    'url' => 'https://www.civihr.org/support',
    'target' => '_blank',
    'permission' => 'access CiviCRM'
  ]);
}

/**
 * Creates Developer Menu in navigation bar
 *
 * @param Array $menu List of available menu items
 */
function __hrui_createDeveloperMenu(&$menu) {
  _hrcore_civix_insert_navigation_menu($menu, '', [
    'name' => ts('Developer'),
    'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
    'operator' => 'AND'
  ]);

  _hrcore_civix_insert_navigation_menu($menu, 'Developer', [
    'name' => ts('API Explorer'),
    'url' => 'civicrm/api',
    'target' => '_blank',
    'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
    'operator' => 'AND'
  ]);

  _hrcore_civix_insert_navigation_menu($menu, 'Developer', [
    'name' => ts('Developer Docs'),
    'target' => '_blank',
    'url' => 'https://civihr.atlassian.net/wiki/spaces/CIV/pages',
    'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
    'operator' => 'AND'
  ]);

  _hrcore_civix_insert_navigation_menu($menu, 'Developer', [
    'name' => ts('Style Guide'),
    'target' => '_blank',
    'url' => 'https://www.civihr.org/support',
    'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
    'operator' => 'AND'
  ]);

  // Adds sub menu under Style Guide menu
  foreach (Civi::service('style_guides')->getAll() as $styleGuide) {
    _hrcore_civix_insert_navigation_menu($menu, 'Developer/Style Guide', [
      'label' => $styleGuide['label'],
      'name' => $styleGuide['name'],
      'url' => 'civicrm/styleguide/' . $styleGuide['name'],
      'permission' => 'access CiviCRM,access CiviCRM developer menu and tools',
      'operator' => 'AND'
    ]);
  }
}

/**
 * Adds icons to dynamically defined menu items
 *
 * @param array $menu
 *   List of available menu items
 */
function __hrui_setDynamicMenuIcons(&$menu) {
  $menuToIcons = [
    'Help' => 'fa fa-question-circle',
    'Developer'=> 'fa fa-code',
  ];

  foreach ($menu as $key => $item) {
    $menuName = $item['attributes']['name'];
    if (array_key_exists($menuName, $menuToIcons)) {
      $menu[$key]['attributes']['icon'] = $menuToIcons[$menuName];
    }
  }
}

function __hrui_is_extension_enabled($key) {
  $isEnabled = CRM_Core_DAO::getFieldValue(
    'CRM_Core_DAO_Extension',
    $key,
    'is_active',
    'full_name'
  );

  return !empty($isEnabled) ? true : false;
}

