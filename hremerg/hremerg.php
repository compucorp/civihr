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
  $profileId = civicrm_api3('UFGroup', 'getsingle', array('return' => "id",  'name' => "new_individual"));
  $summaryId = civicrm_api3('UFGroup', 'getsingle', array('return' => "id",  'name' => "summary_overlay"));
  $location = civicrm_api3('LocationType', 'getsingle', array('return' => "id", 'name' => "Main"));
  $phoneTypes = CRM_Core_OptionGroup::values('phone_type');
  $phone =  array(
    array_search('Phone',$phoneTypes) => 'Phone No',
    array_search('Mobile',$phoneTypes) => 'Mobile No',
  );

  //add postal code suffix ,country and 2 phone nos in summary overlay profile
  $postal_weight = civicrm_api3('UFField', 'getsingle', array('return' => "weight", 'uf_group_id' => $summaryId['id'], 'field_name' => "postal_code"));
  $postalParams = array(
    'uf_group_id' => $summaryId['id'],
    'is_active' => '1',
    'label' => 'Postal Code Suffix',
    'field_type' => 'Contact',
    'weight' => ++$postal_weight['weight'],
    'field_name' => 'postal_code_suffix',
  );
  civicrm_api3('UFField', 'create', $postalParams);

  foreach ( $phone as $name=>$label) {
    $params = array(
      'uf_group_id' => $summaryId['id'],
      'is_active' => '1',
      'label' => $label,
      'field_type' => 'Contact',
      'location_type_id' => $location['id'],
      'field_name' => 'phone',
      'phone_type_id' => $name,
    );
    civicrm_api3('UFField', 'create', $params);
  }

  $state_weight = civicrm_api3('UFField', 'getsingle', array('return' => "weight", 'uf_group_id' => $summaryId['id'], 'field_name' => "state_province"));
  $country = array(
    'uf_group_id' => $summaryId['id'],
    'is_active' => '1',
    'label' => 'Country',
    'field_type' => 'Contact',
    'weight' => $state_weight['weight']+1,
    'field_name' => 'country',
  );
  civicrm_api3('UFField', 'create', $country);

  //add fields in new individual profile
  $i = 4;
  foreach ( $phone as $name=>$label) {
    $params = array(
      'uf_group_id' => $profileId['id'],
      'is_active' => '1',
      'label' => $label,
      'field_type' => 'Contact',
      'weight' => $i,
      'location_type_id' => $location['id'],
      'field_name' => 'phone',
      'phone_type_id' => $name,
    );
    $i++;
    civicrm_api3('UFField', 'create', $params);
  }
  $fields = array(
    'street_address' => 'Street Address',
    'city' => 'City',
    'postal_code' => 'Postal Code',
    'postal_code_suffix' => 'Postal Code Suffix',
    'country' => 'Country',
    'state_province' => 'State',
  );
  foreach ( $fields as $name=>$label) {
    $params = array(
      'uf_group_id' => $profileId['id'],
      'is_active' => '1',
      'label' => $label,
      'field_type' => 'Contact',
      'weight' => $i,
      'field_name' => $name,
    );
    $i++;
    civicrm_api3('UFField', 'create', $params);
  }

  return _hremerg_civix_civicrm_install();
}


/**
 * Implementation of hook_civicrm_uninstall
 */
function hremerg_civicrm_uninstall() {
  $summaryId = civicrm_api3('UFGroup', 'getsingle', array('return' => "id",  'name' => "summary_overlay"));
  $location = civicrm_api3('LocationType', 'getsingle', array('return' => "id", 'name' => "Main"));
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_uf_field WHERE uf_group_id = {$summaryId['id']} and field_name = 'phone' and location_type_id = {$location['id']}");
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_uf_field WHERE uf_group_id = {$summaryId['id']} and (field_name = 'postal_code_suffix' or field_name = 'country')");

  //delete uffields
  $profileId = civicrm_api3('UFGroup', 'getsingle', array('return' => "id",  'name' => "new_individual"));
  CRM_Core_DAO::executeQuery("DELETE FROM  civicrm_uf_field WHERE uf_group_id = {$profileId['id']} AND field_name IN ('phone','street_address','city','postal_code','postal_code_suffix','state_province','country')");

  //delete customgroup
  $customGroup = civicrm_api3('CustomGroup', 'getsingle', array('return' => "id",'name' => "Emergency_Contact",));
  civicrm_api3('CustomGroup', 'delete', array('id' => $customGroup['id']));
  //delete optiongroup
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_option_group WHERE name = 'priority_20130514082429'");
  return _hremerg_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hremerg_civicrm_enable() {
  _hremerg_setActiveFields(1);
  return _hremerg_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hremerg_civicrm_disable() {
  _hremerg_setActiveFields(0);
  return _hremerg_civix_civicrm_disable();
}

function _hremerg_setActiveFields($setActive) {
  //disable/enable uffields
  $profileId = civicrm_api3('UFGroup', 'getsingle', array('return' => "id",  'name' => "new_individual"));
  CRM_Core_DAO::executeQuery("UPDATE civicrm_uf_field SET is_active = {$setActive} WHERE uf_group_id = {$profileId['id']} AND field_name IN ('phone','street_address','city','postal_code','postal_code_suffix','state_province','country')");

  $summaryId = civicrm_api3('UFGroup', 'getsingle', array('return' => "id",  'name' => "summary_overlay"));
  $location = civicrm_api3('LocationType', 'getsingle', array('return' => "id", 'name' => 'Main'));
  CRM_Core_DAO::executeQuery("UPDATE civicrm_uf_field SET is_active = {$setActive} where uf_group_id = {$summaryId['id']} AND (field_name = 'postal_code_suffix' OR field_name = 'country')");
  CRM_Core_DAO::executeQuery("UPDATE civicrm_uf_field SET is_active = {$setActive}  where uf_group_id = {$summaryId['id']} AND field_name = 'phone' and location_type_id = {$location['id']}");

  //disable/enable customgroup and customvalue
  $sql = "UPDATE civicrm_custom_field JOIN civicrm_custom_group on civicrm_custom_group.id = civicrm_custom_field.custom_group_id SET civicrm_custom_field.is_active = {$setActive} WHERE civicrm_custom_group.name = 'Emergency_Contact'";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name = 'Emergency_Contact'");

  //disable/enable optionGroup and optionValue
  $query = "UPDATE civicrm_option_value JOIN civicrm_option_group on civicrm_option_group.id = civicrm_option_value.option_group_id SET civicrm_option_value.is_active = {$setActive} WHERE civicrm_option_group.name = 'priority_20130514082429'";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$setActive} WHERE name = 'priority_20130514082429'");
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

/**
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function hremerg_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contact_Form_Relationship' && empty($form->_caseId)) {
    if ($form->elementExists('relationship_type_id') && $form->_contactType == 'Individual') {
      $relationshipType = civicrm_api3('relationship_type', 'get', array('name_a_b' => 'Emergency Contact'));
      $select = $form->getElement('relationship_type_id');
      $select->freeze();
      $select->setLabel('');
      $form->getElement('related_contact_id')->setLabel('');
      if ($form->getAction() & CRM_Core_Action::ADD && !empty($relationshipType['id'])) {
        $form->setDefaults(array('relationship_type_id' => $relationshipType['id'] . '_a_b'));
      }
    }
  }
}

function hremerg_civicrm_alterContent( &$content, $context, $tplName, &$object ) {
  $smarty = CRM_Core_Smarty::singleton();
  if (!empty($smarty->_tpl_vars['form']['formName']) &&
    ($formname = $smarty->_tpl_vars['form']['formName']) &&
    $tplName == 'CRM/Contact/Page/View/Relationship.tpl') {
    $content .="<script type=\"text/javascript\">
      CRM.$(function($) {
        var context = $('form#{$formname}');
        $('.crm-relationship-form-block-description', context).remove();
        $('.crm-relationship-form-block-note',context).remove();
        $('.crm-relationship-form-block-is_permission_a_b',context).remove();
        $('.crm-relationship-form-block-is_permission_b_a',context).remove();
        $('.crm-relationship-form-block-is_active',context).hide();
        $('.crm-relationship-form-block-start_date',context).remove();
      });
    </script>";
  }
}

/**
 * Implementation of hook_civicrm_pageRun
 */
function hremerg_civicrm_pageRun( &$page ) {
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.hremerg', 'css/hremerg.css');
  }
}
