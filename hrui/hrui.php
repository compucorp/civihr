<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.0                                                 |
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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'hrui.civix.php';

function hrui_civicrm_pageRun($page) {
  if ($page instanceof CRM_Contact_Page_DashBoard) {
    CRM_Utils_System::setTitle(ts('CiviHR Home'));
  }
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrui', 'js/hrui.js');
  }
}

function hrui_civicrm_buildForm($formName, &$form) {
  if ($form instanceof CRM_Contact_Form_Contact) {
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrui', 'js/hrui.js');
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
  // make sure only relevant components are enabled 
  $params = array(
    'version' => 3,
    'domain_id' => CRM_Core_Config::domainID(),
    'enable_components' => array('CiviReport','CiviCase'),
  );
  $result = civicrm_api('setting', 'create', $params);
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
      'version' => 3,
      'name' => "Household",
      'id' => $contactTypeId,
      'is_active' => FALSE,
    );
    $resultContactType = civicrm_api('contact_type', 'create', $paramsContactType);
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
          'version' => 3,
          'id' => $reportID,
        );
        $resultContactType = civicrm_api('report_instance', 'delete', $paramsReport);
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
  $options = hrui_getViewOptionsSetting();

  // unset activity & tag tab options
  foreach ($tabsToUnset as $key) {
    unset($options[$key]);
  }
  $options = array_keys($options);

  // set modified options in the DB
  hrui_setViewOptionsSetting($options);

  $relationshipTypes = CRM_Core_PseudoConstant::relationshipType();
  $disableRelationships = array(
    'Child of',
    'Spouse of',
    'Sibling of',
    'Employee of',
    'Volunteer for',
    'Head of Household for',
    'Household Member of',
    'Supervised by'
  );

  foreach ($relationshipTypes as $id => $value) {
    if (in_array($value['label_a_b'], $disableRelationships)) {
      CRM_Contact_BAO_RelationshipType::setIsActive($id, FALSE);
    }
  }

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

  return _hrui_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrui_civicrm_uninstall() {
  // get a list of all tab options
  $options = CRM_Core_OptionGroup::values('contact_view_options', TRUE, FALSE);
  $tabsToSet = array($options['Activities'], $options['Tags']);

  // get tab options from DB
  $options = hrui_getViewOptionsSetting();

  // set activity & tag tab options
  foreach ($tabsToSet as $key) {
    $options[$key] = 1;
  }
  $options = array_keys($options);

  // set modified options in the DB
  hrui_setViewOptionsSetting($options);

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

  return _hrui_civix_civicrm_uninstall();
}

/**
 * get tab options from DB using setting-get api
 */
function hrui_getViewOptionsSetting() {
  $domainID = CRM_Core_Config::domainID();
  $params = array(
    'version' => 3,
    'domain_id' => $domainID,
    'return' => 'contact_view_options',
  );
  $result = civicrm_api('setting', 'get', $params);
  if (CRM_Utils_Array::value('is_error', $result, FALSE)) {
    CRM_Core_Error::debug_var('setting-get result for contact_view_options', $result);
    throw new CRM_Core_Exception('Failed to retrieve settings for contact_view_options');
  }
  return array_flip($result['values'][$domainID]['contact_view_options']);
}

/**
 * set modified options in the DB using setting-create api
 */
function hrui_setViewOptionsSetting($options = array()) {
  $domainID = CRM_Core_Config::domainID();
  $params = array(
    'version' => 3,
    'domain_id' => $domainID,
    'contact_view_options' => $options,
  );
  $result = civicrm_api('setting', 'create', $params);
  if (CRM_Utils_Array::value('is_error', $result, FALSE)) {
    CRM_Core_Error::debug_var('setting-create result for contact_view_options', $result);
    throw new CRM_Core_Exception('Failed to create settings for contact_view_options');
  }
  return TRUE;
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
 * Implementation of hook_civicrm_tabs
 */
function hrui_civicrm_tabs(&$tabs, $contactID) {
  $count = count($tabs);
  for ($i = 0; $i < $count; $i++) {
    if ($tabs[$i]['id'] != 'log') {
      $tab[$i] = $tabs[$i]['title'];
    }
    else {
      $changeLogTabID = $i;
    }
  }

  //sort alphabetically
  asort($tab);
  $weight = 0;
  //assign the weights based on alphabetic order
  foreach ($tab as $key => $value) {
    $weight += 10;
    $tabs[$key]['weight'] = $weight;
  }

  //Move change log to the end
  $tabs[$changeLogTabID]['weight'] = $weight + 10;
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

function hrui_civicrm_navigationMenu( &$params ) {
    //  Get the maximum key of $params
    $multipleCustomData = CRM_Core_BAO_CustomGroup::getMultipleFieldGroup();

    $maxKey = ( max( array_keys($params) ) );
    $i =1;
    foreach ($multipleCustomData as $key => $value) {
      $i++;
      $i = $maxKey + $i;
      $multiValuedData[$i] = array (
      'attributes' => array (
        'label'      => $value,
        'name'       => $value,
        'url'        => 'civicrm/import/custom?reset=1&id='.$key,
        'permission' => 'access HRJobs',
        'operator'   => null,
        'separator'  => null,
        'parentID'   => $maxKey+1,
        'navID'      => $i,
        'active'     => 1
      ),
      'child' => null
    );
    }

    $params[15]['child'][$maxKey+1] = array (
      'attributes' => array (
        'label'      => 'Import Multi-value Custom Data' ,
        'name'       => 'multiValueCustomDataImport',
        'url'        => 'civicrm/import/custom',
        'permission' => 'access HRJobs',
        'operator'   => null,
        'separator'  => null,
        'parentID'   => 15,
        'navID'      => $maxKey+1,
        'active'     => 1
      ),
      'child' => $multiValuedData,
    );
   
}
