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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_HRJob_Form_EditHourOption extends CRM_Core_Form {
  public $_optionValue = array();
  public $_id = array();

  /**
   * This function sets the default values for the form. Note that in edit/view mode
   * the default values are retrieved from the database
   *
   * @access public
   *
   * @return None
   */
  public function setDefaultValues() {
    $hourValue = CRM_Utils_Request::retrieve('value', 'Integer', $this);
    $defaults = array(
      'hour_type_select' => $hourValue ? $hourValue : NULL,
      'hour_value' => $hourValue ? $hourValue : NULL,
    );
    return $defaults;
  }

  function buildQuickForm() {
    $this->addButtons(array(
        array(
          'type' => 'next',
          'name' => ts('Save'),
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
      )
    );
    $optionGroupId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'hrjob_hours_type', 'id', 'name');
    $optionGroupIds = CRM_Core_BAO_OptionValue::getOptionValuesArray($optionGroupId);
    foreach($optionGroupIds as $key => $value) {
      $this->_optionValue[$value['value']] = $value['label'];
      $this->_id[$key] = $value['value'];
    }
    $this->assign('optionGroupIds', $optionGroupIds);
    $this->addElement('select', 'hour_type_select', ts('Select Hour Type'), array('' => ts('- select -')) + $this->_optionValue);
    $this->add('text', 'hour_value', ts('Value'));
    $this->addFormRule(array('CRM_HRJob_Form_EditHourOption', 'formRule'), $this);
  }

 static function formRule($fields, $files, $self) {
   $errors = array();
   if ($fields['hour_value'] == '') {
     $errors['hour_value'] = ts('Value is required.');
   }
   if ($fields['hour_type_select'] == '') {
     $errors['hour_type_select'] = ts('Hour Type is required.');
   }
   if(CRM_Utils_Array::value($fields['hour_value'], $self->_optionValue)){
     $errors['hour_value'] = ts('Value already exist in database.');
   }
   return $errors;
 }

  function postProcess() {
    $session = CRM_Core_Session::singleton();
    $params = $this->exportValues();
    $result = civicrm_api3('OptionValue', 'create', array(
      'value' => $params['hour_value'],
      'id' => CRM_Utils_Array::key($params['hour_type_select'], $this->_id),
    ));
    $session->pushUserContext(CRM_Utils_System::url('civicrm/hour/editoption', "&value={$params['hour_value']}"));
  }
}
