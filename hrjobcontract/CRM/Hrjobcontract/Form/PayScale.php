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
 * This class generates form components for Pay Scale
 *
 */
class CRM_Hrjobcontract_Form_PayScale extends CRM_Core_Form {

  function setDefaultValues() {
    $defaults = array();

    if ($this->_id) {
      $defaults = CRM_Hrjobcontract_BAO_PayScale::getDefaultValues($this->_id);
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
    
    $currencyFormatsKeys = array_keys(CRM_Hrjobcontract_Page_JobContractTab::getCurrencyFormats());
    $currencies = array_combine($currencyFormatsKeys, $currencyFormatsKeys);
    
    $this->add('text', 'pay_scale', ts('Pay Scale'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_PayScale', 'pay_scale'), TRUE);
    $this->add('text', 'pay_grade', ts('Pay Grade'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_PayScale', 'pay_grade'), TRUE);
    $this->add('select', 'currency', ts('Currency'), array('' => ts('- select -')) + $currencies, TRUE);
    $this->add('text', 'amount', ts('Amount'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_PayScale', 'amount'), TRUE);
    $this->add('text', 'periodicity', ts('Periodicity'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_PayScale', 'periodicity'), TRUE);

    $this->add('checkbox', 'is_active', ts('Enabled?'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_PayScale', 'is_active'));

    $this->addFormRule(array('CRM_Hrjobcontract_Form_PayScale', 'formRule'), $this);
  }

  static function formRule($fields, $files, $self) {
    $errors = array();
    if (!array_key_exists('pay_scale', $fields)) {
      $errors['pay_scale'] = ts("Please enter Pay Scale value");
    }
    if (!array_key_exists('pay_grade', $fields)) {
      $errors['pay_grade'] = ts("Please enter Pay Grade value");
    }
    if (!array_key_exists('currency', $fields)) {
      $errors['currency'] = ts("Please enter Currency value");
    }
    if (!array_key_exists('amount', $fields)) {
      $errors['amount'] = ts("Please enter Amount value");
    }
    if (!array_key_exists('periodicity', $fields)) {
      $errors['periodicity'] = ts("Please enter Periodicity value");
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
      CRM_Hrjobcontract_BAO_PayScale::del($this->_id);
      CRM_Core_Session::setStatus(ts('Selected pay scale has been deleted.'), 'Success', 'success');
    }
    else {
      $params = $ids = array( );
      // store the submitted values in an array
      $params = $this->exportValues();

      foreach (array('pay_scale', 'pay_grade', 'currency', 'amount', 'periodicity') as $key => $index) {
        if(!array_key_exists($index, $params)) {
          $params[$index] = 0;
        }
      }

      if ($this->_action & CRM_Core_Action::UPDATE) {
        $params['id'] = $this->_id;
      }

      $payScale = CRM_Hrjobcontract_BAO_PayScale::create($params);

      if ($this->_action & CRM_Core_Action::UPDATE) {
        CRM_Core_Session::setStatus(ts('The Pay Scale for \'%1\' has been updated.', array( 1 => $payScale->pay_scale)), 'Success', 'success');
      }
      else {
        CRM_Core_Session::setStatus(ts('The Pay Scale for \'%1\' has been added.', array( 1 => $payScale->pay_scale)), 'Success', 'success');
      }

      $url = CRM_Utils_System::url('civicrm/pay_scale', 'reset=1&action=browse');
      $session = CRM_Core_Session::singleton();
      $session->replaceUserContext($url);
    }
  }
}
