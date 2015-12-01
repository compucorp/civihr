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

/**
 * This class generates form components for Appraisal Criteria
 *
 */
class CRM_Appraisals_Form_AppraisalCriteria extends CRM_Core_Form {

  function setDefaultValues() {
    $defaults = array();

    if ($this->_id) {
      $defaults = CRM_Appraisals_BAO_AppraisalCriteria::getDefaultValues($this->_id);
    }
    else {
      $defaults['is_active'] = 1;
    }

    return $defaults;
  }

  /**
   * Function to build the form
   *
   * @return void
   * @access public
   */
  public function buildQuickForm() {
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

    if ($this->_action & CRM_Core_Action::DELETE) {
      $this->addButtons(array(
          array(
            'type' => 'next',
            'name' => ts('Delete'),
            'isDefault' => TRUE,
          ),
          array(
            'type' => 'cancel',
            'name' => ts('Cancel'),
          ),
        )
      );
    }


    $this->_id = CRM_Utils_Request::retrieve('id' , 'Positive', $this);
    if ($this->_action & CRM_Core_Action::DELETE) {
      return;
    }
    
    $this->add('text', 'value', ts('Grade value'), CRM_Core_DAO::getAttribute('CRM_Appraisals_DAO_AppraisalCriteria', 'value'), TRUE);
    $this->add('text', 'label', ts('Grade label'), CRM_Core_DAO::getAttribute('CRM_Appraisals_DAO_AppraisalCriteria', 'label'), TRUE);
    $this->add('checkbox', 'is_active', ts('Enabled?'), CRM_Core_DAO::getAttribute('CRM_Appraisals_DAO_AppraisalCriteria', 'is_active'));

    $this->addFormRule(array('CRM_Appraisals_Form_AppraisalCriteria', 'formRule'), $this);
  }

  static function formRule($fields, $files, $self) {
    $errors = array();
    if (!array_key_exists('value', $fields)) {
      $errors['value'] = ts("Please enter Grade value");
    }
    if (!array_key_exists('label', $fields)) {
      $errors['label'] = ts("Please enter Grade label");
    }
    return $errors;
  }

  /**
   * Function to process the form
   *
   * @access public
   * @return void
   */
  public function postProcess() {
    if ($this->_action & CRM_Core_Action::DELETE) {
        // Check if selected item has highest value of all Appraisal Criteria:
      $result = civicrm_api3('AppraisalCriteria', 'getsingle', array(
        'sequential' => 1,
        'options' => array('sort' => "value DESC", 'limit' => 1),
      ));
      if (empty($result['id']) || (int)$result['id'] !== (int)$this->_id) {
          CRM_Core_Session::setStatus(ts('You can delete only Appraisal Criteria which has highest Value.'), 'Error', 'error');
          return false;
      }
      CRM_Appraisals_BAO_AppraisalCriteria::del($this->_id);
      CRM_Core_Session::setStatus(ts('Selected Appraisal Criteria has been deleted.'), 'Success', 'success');
    }
    else {
      $params = $ids = array( );
      // store the submitted values in an array
      $params = $this->exportValues();

      foreach (array('value', 'label') as $key => $index) {
        if(!array_key_exists($index, $params)) {
          $params[$index] = 0;
        }
      }

      if ($this->_action & CRM_Core_Action::UPDATE) {
        $params['id'] = $this->_id;
      }

      // Check for duplicates:
      $singleParams = array(
        'sequential' => 1,
        'value' => (int)$params['value'],
        'options' => array('limit' => 1),
      );
      if (!empty($params['id'])) {
          $singleParams['id'] = array('!=' => (int)$params['id']);
      }
      $appraisalCriteriaSingle = civicrm_api3('AppraisalCriteria', 'get', $singleParams);
      if (!empty($appraisalCriteriaSingle['values'])) {
        CRM_Core_Session::setStatus(ts('The Appraisal Criteria with Grade value \'%1\' already exists.', array( 1 => (int)$params['value'])), 'Error', 'error');
        return false;
      }

      // Check if Grade is consecutive number:
      $singleParams = array(
        'sequential' => 1,
        'value' => (int)$params['value'] - 1,
        'options' => array('limit' => 1),
      );
      if (!empty($params['id'])) {
        $singleParams['id'] = array('!=' => (int)$params['id']);
      }
      $appraisalCriteriaSingle = civicrm_api3('AppraisalCriteria', 'get', $singleParams);
      if (!isset($appraisalCriteriaSingle['id']) && (int)$params['value'] > 1) {
        CRM_Core_Session::setStatus(ts('The Appraisal Criteria should have consecutive Grade value.'), 'Error', 'error');
        return false;
      }

      $appraisalCriteria = CRM_Appraisals_BAO_AppraisalCriteria::create($params);

      if ($this->_action & CRM_Core_Action::UPDATE) {
        CRM_Core_Session::setStatus(ts('The Appraisal Criteria with Grade value \'%1\' has been updated.', array( 1 => $appraisalCriteria->value)), 'Success', 'success');
      }
      else {
        CRM_Core_Session::setStatus(ts('The Appraisal Criteria with Grade value \'%1\' has been added.', array( 1 => $appraisalCriteria->value)), 'Success', 'success');
      }

      $url = CRM_Utils_System::url('civicrm/appraisal_criteria', 'reset=1&action=browse');
      $session = CRM_Core_Session::singleton();
      $session->replaceUserContext($url);
    }
  }
}
