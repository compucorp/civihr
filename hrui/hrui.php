<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.4                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2014                                |
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
  CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.hrui', 'css/hrui.css');

  if ($page instanceof CRM_Contact_Page_DashBoard) {
    CRM_Utils_System::setTitle(ts('CiviHR Home'));
  }
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    CRM_Core_Resources::singleton()
      ->addStyleFile('org.civicrm.hrui', 'css/contact.css')
      ->addScriptFile('org.civicrm.hrui', 'js/contact.js')
      ->addScriptFile('org.civicrm.hrui', 'js/hrui.js')
      ->addSetting(array('pageName' => 'viewSummary'));
    //set government field value for individual page
    $contactType = CRM_Contact_BAO_Contact::getContactType(CRM_Utils_Request::retrieve('cid', 'Integer'));
    if ($contactType == 'Individual') {
      $hideGId = civicrm_api3('CustomField', 'getvalue', array('custom_group_id' => 'Identify', 'name' => 'is_government', 'return' => 'id'));
      CRM_Core_Resources::singleton()
        ->addSetting(array(
          'cid' => CRM_Utils_Request::retrieve('cid', 'Integer'),
          'hideGId' => $hideGId));
    }
  }
}

function hrui_civicrm_buildForm($formName, &$form) {
  CRM_Core_Resources::singleton()
    ->addStyleFile('org.civicrm.hrui', 'css/hrui.css')
    ->addScriptFile('org.civicrm.hrui', 'js/hrui.js');
  if ($form instanceof CRM_Contact_Form_Contact) {
    CRM_Core_Resources::singleton()
      ->addSetting(array('formName' => 'contactForm'));
    //HR-358 - Set default values
    //set default value to phone location and type
    $locationId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_LocationType', 'Main', 'id', 'name');
    // PCHR-1146 : Commenting line ahead to fix the issue, but figuring why it was done at first place coul be useful.
    //$result = civicrm_api3('LocationType', 'create', array('id'=>$locationId, 'is_default'=> 1, 'is_active'=>1));
    if (($form->elementExists('phone[2][phone_type_id]')) && ($form->elementExists('phone[2][phone_type_id]'))) {
      $phoneType = $form->getElement('phone[2][phone_type_id]');
      $phoneValue = CRM_Core_OptionGroup::values('phone_type');
      $phoneKey = CRM_Utils_Array::key('Mobile', $phoneValue);
      $phoneType->setSelected($phoneKey);
      $phoneLocation = $form->getElement('phone[2][location_type_id]');
      $phoneLocation->setSelected($locationId);
    }
  }

  $ogID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'type_20130502144049', 'id', 'name');
  //HR-355 -- Add Government ID
  if ($formName == 'CRM_Contact_Form_Contact' && $ogID && $form->_contactType == 'Individual') {
    //add government fields
    $contactID = CRM_Utils_Request::retrieve('cid', 'Integer', $form);
    $templatePath = CRM_Extension_System::singleton()->getMapper()->keyToBasePath('org.civicrm.hrui'). '/templates';
    $form->add('text', 'GovernmentId', ts('Government ID'));
    $form->addElement('select', "govTypeOptions", '', CRM_Core_BAO_OptionValue::getOptionValuesAssocArray($ogID));
    CRM_Core_Region::instance('page-body')
      ->add(array('template' => "{$templatePath}/CRM/HRUI/Form/contactField.tpl"));

    $action = CRM_Utils_Request::retrieve('action', 'String', $form);
    $govVal = CRM_HRIdent_Page_HRIdent::retreiveContactFieldValue($contactID);
    //set default to government type option
    $default = array();
    $default['govTypeOptions'] = CRM_Core_BAO_CustomField::getOptionGroupDefault($ogID, 'select');
    if ($action == CRM_Core_Action::UPDATE && !empty($govVal)) {
      //set key for updating specific record of contact id in custom value table
      $default['govTypeOptions'] = CRM_Utils_Array::value('type', $govVal);
      $default['GovernmentId'] = CRM_Utils_Array::value('typeNumber',$govVal);
    }
    $form->setDefaults($default);
  }
}

/**
 * Implementation of hook_civicrm_postProcess
 */
function hrui_civicrm_postProcess( $formName, &$form ) {
  $isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrident', 'is_active', 'full_name');
  if ($formName == 'CRM_Contact_Form_Contact'
    && !empty($form->_submitValues['GovernmentId'])
    && $form->_contactType == 'Individual') {
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

  //Disable Individual sub types
  _hrui_toggleContactSubType(FALSE);

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
  $options = hrui_getViewOptionsSetting();

  // unset activity & tag tab options
  foreach ($tabsToUnset as $key) {
    unset($options[$key]);
  }
  $options = array_keys($options);

  // set modified options in the DB
  hrui_setViewOptionsSetting($options);
  _hrui_setActiveFields(FALSE);

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
  _hrui_wordReplacement(FALSE);
  return _hrui_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrui_civicrm_uninstall() {
  //Enable Individual sub types
  _hrui_toggleContactSubType(TRUE);

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
  _hrui_setActiveFields(TRUE);
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
  _hrui_wordReplacement(TRUE);
  return _hrui_civix_civicrm_uninstall();
}

/**
 * get tab options from DB using setting-get api
 */
function hrui_getViewOptionsSetting() {
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
function hrui_setViewOptionsSetting($options = array()) {
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

/**
 * Implementation of hook_civicrm_enable
 */
function hrui_civicrm_enable() {
  _hrui_setActiveFields(FALSE);
  _hrui_toggleContactSubType(FALSE);
  _hrui_wordReplacement(FALSE);
  return _hrui_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrui_civicrm_disable() {
  _hrui_setActiveFields(TRUE);
  _hrui_toggleContactSubType(TRUE);
  _hrui_wordReplacement(TRUE);
  return _hrui_civix_civicrm_disable();
}


function _hrui_wordReplacement($isActive) {
  if( $isActive) {
    CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'CiviCRM News' WHERE name = 'blog' ");
    CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'Case Dashboard Dashlet' WHERE name = 'casedashboard' ");
  }
  else {
    CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'CiviHR News' WHERE name = 'blog' ");
    CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'Assignments Dashlet' WHERE name = 'casedashboard' ");
  }
}

/**
 * Enable/disable individual contact sub types
 */
function _hrui_toggleContactSubType($isActive) {
  $individualTypeId = civicrm_api3('ContactType', 'getsingle', array('return' => "id",'name' => "Individual"));
  $subContactId = civicrm_api3('ContactType', 'get', array('parent_id' => $individualTypeId['id']));
  foreach ($subContactId['values'] as $key) {
    $paramsSubType = array(
      'name' => $key['name'],
      'id' => $key['id'],
      'is_active' => $isActive,
    );
    civicrm_api3('ContactType', 'create', $paramsSubType);
  }

  $orgTypeId = civicrm_api3('ContactType', 'getsingle', array('return' => "id",'name' => "Organization"));
  $subOrgId = civicrm_api3('ContactType', 'get', array('parent_id' => $orgTypeId['id']));
  foreach ($subOrgId['values'] as $key) {
   if ($key['name'] == 'Team' || $key['name'] == 'Sponsor') {
    $paramsSubType = array(
      'name' => $key['name'],
      'id' => $key['id'],
      'is_active' => $isActive,
    );
    civicrm_api3('ContactType', 'create', $paramsSubType);
   }
  }
  // Reset Navigation
  CRM_Core_BAO_Navigation::resetNavigation();
}

function _hrui_setActiveFields($setActive) {
  $setActive = $setActive ? 1 : 0;
  //disable/enable optionGroup and optionValue
  $query = "UPDATE civicrm_option_value JOIN civicrm_option_group ON civicrm_option_group.id = civicrm_option_value.option_group_id SET civicrm_option_value.is_active = {$setActive} WHERE civicrm_option_group.name IN ('custom_most_important_issue', 'custom_marital_status')";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$setActive} WHERE name IN ('custom_most_important_issue', 'custom_marital_status')");

  //disable/enable customgroup and customvalue
  $sql = "UPDATE civicrm_custom_field JOIN civicrm_custom_group ON civicrm_custom_group.id = civicrm_custom_field.custom_group_id SET civicrm_custom_field.is_active = {$setActive} WHERE civicrm_custom_group.name = 'constituent_information'";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name = 'constituent_information'");
  CRM_Core_DAO::executeQuery("UPDATE civicrm_relationship_type SET is_active = {$setActive} WHERE name_a_b IN ( 'Child of', 'Spouse of', 'Sibling of', 'Employee of', 'Volunteer for', 'Head of Household for', 'Household Member of', 'Supervised by' )");
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
  $newTabs = array();
  /*
   * 1) we alter the weights for these tabs here
   * since these tabs are not created by hook_civicrm_tab
   * and the only way to alter their weights is here
   * by taking advantage of &$tabs variable.
   * 2) we set assignments tab to 30 since it should appear
   * after appraisals tab directly which have the weight of 20.
   * 3) we jump to weight of 60 in identifications tab since 40 & 50
   * are occupied by tasks & assignments extension tabs .
   * 4) the weight increased by 10 between every tab
   * to give a large space for other tabs to be inserted
   * between any two without altering other tabs weights.
   */
  foreach ($tabs as $i => $tab) {
    switch($tab['title'])  {
      case 'Assignments':
        $tabs[$i]['weight'] = 30;
        break;
      case 'Identification':
        $tabs[$i]['weight'] = 60;
        break;
      case 'Immigration':
        $tabs[$i]['weight'] = 70;
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
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrui_civicrm_managed(&$entities) {
  return _hrui_civix_civicrm_managed($entities);
}

function hrui_civicrm_navigationMenu( &$params ) {
  $maxKey = ( max( array_keys($params) ) );
  $jobNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'jobImport', 'id', 'name');
  $contactNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');
  if ($jobNavId) {
    $i = 1;
    // Degrade gracefully on 4.4
    if (is_callable(array('CRM_Core_BAO_CustomGroup', 'getMultipleFieldGroup'))) {
      //  Get the maximum key of $params
      $multipleCustomData = CRM_Core_BAO_CustomGroup::getMultipleFieldGroup();

      $multiValuedData[$maxKey+1] = array(
        'attributes' => array (
         'label' => ts('Jobs'),
         'name' => 'jobs',
         'url'  => 'civicrm/job/import',
         'permission' => 'access HRJobs',
         'operator'   => null,
         'separator'  => null,
         'parentID'   => $jobNavId,
         'navID'      => $maxKey+1,
         'weight'     => 1,
         'active'     => 1
       ));
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
            'parentID'   => $jobNavId,
            'navID'      => $i,
            'active'     => 1
          ),
          'child' => null
        );
      }
      $params[$contactNavId]['child'][$jobNavId]['child'] = $multiValuedData;
    }
  }
}

/**
 * Implementation of hook_civicrm_alterContent
 *
 * @return void
 */
function hrui_civicrm_alterContent( &$content, $context, $tplName, &$object ) {
  $smarty = CRM_Core_Smarty::singleton();

  // fetch data to the new summary page UI
  if($context == 'page' && $tplName == "CRM/Contact/Page/View/Summary.tpl" ) {
    $content .= _hrui_updateContactSummaryUI();
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
}

/**
 * Add new information in the contact header as the contact photo,
 * phone, department. All changes are made via Javascript.
 *
 * @return [String] Updated content markup
 */
function _hrui_updateContactSummaryUI() {
  $content = '';

  $contact_id = CRM_Utils_Request::retrieve( 'cid', 'Positive');
  /* $currentContractDetails contain current contact data including
   * Current ( Position = $currentContractDetails->position ) and
   * ( Normal Place of work =  $currentContractDetails->location )
  */
  $currentContractDetails = CRM_Hrjobcontract_BAO_HRJobContract::getCurrentContract($contact_id);
  // $departmentsList contain current roles departments list separated by comma
  $departmentsList = null;
  if ($currentContractDetails)  {
    $departmentsArray = CRM_Hrjobroles_BAO_HrJobRoles::getDepartmentsList($currentContractDetails->contract_id);
    $departmentsList = implode(', ', $departmentsArray);
  }
  // $managersList contain current line managers list separated by comma
  $managersList = null;
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
  }
  catch (CiviCRM_API3_Exception $e) {
  }

  $content .="<script type=\"text/javascript\">
    CRM.$(function($) {
      $('#contactname-block.crm-summary-block').wrap('<div class=\"crm-summary-block-wrap\" />');
    });
  </script>";

  if (!empty($contactDetails['image_URL'])) {
    $content .= "<script type=\"text/javascript\">
      CRM.$(function($) {
        $('.crm-summary-contactname-block').prepend('<img class=\"crm-summary-contactphoto\" src=" . $contactDetails['image_URL'] . " />');
      });
    </script>";
  }

  if (empty($currentContractDetails)) {
    $content .= "<script type=\"text/javascript\">
      CRM.$(function($) {
        $('.crm-summary-contactname-block').addClass('crm-summary-contactname-block-without-contract');
      });
    </script>";
  }

  $content .="<script type=\"text/javascript\">
    CRM.$(function($) {
      $('.crm-summary-block-wrap').append(\"<div class='crm-contact-detail-wrap' />\");
    });
  </script>";

  $contactDetailHTML = '';

  if (!empty($contactDetails['phone'])) {
    $contactDetailHTML .= "<span class='crm-contact-detail'><strong>Phone:</strong> " . $contactDetails['phone'] . "</span>";
  }

  if (!empty($contactDetails['email'])) {
    $contactDetailHTML .= "<span class='crm-contact-detail'><strong>Email:</strong> " . $contactDetails['email'] . "</span>";
  }

  $contactDetailHTML .= "<br />";

  if (isset($currentContractDetails)) {
    if (!empty($currentContractDetails->position)) {
      $contactDetailHTML .= "<span class='crm-contact-detail'><strong>Position:</strong> " . $currentContractDetails->position . "</span>";
    }

    if (!empty($currentContractDetails->location)) {
      $contactDetailHTML .= "<span class='crm-contact-detail'><strong>Normal place of work:</strong> " . $currentContractDetails->location . "</span>";
    }

    if (!empty($departmentsList)) {
      $contactDetailHTML .= "<span class='crm-contact-detail'><strong>Department:</strong> " . $departmentsList . "</span>";
    }

    if (!empty($managersList)) {
      $contactDetailHTML .= "<span class='crm-contact-detail'><strong>Manager:</strong> " . $managersList . "</span>";
    }
  }

  $content .="<script type=\"text/javascript\">
    CRM.$(function($) {
      $('.crm-contact-detail-wrap').append(\"" . $contactDetailHTML . "\");
    });
  </script>";

  return $content;
}
