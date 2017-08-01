<?php

use CRM_Hrjobcontract_Page_JobContractTab as JobContractTab;
use CRM_Hrjobcontract_SelectValues as SelectValues;
use CRM_Core_Session as Session;

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

  /**
   * @var int
   */
  protected $_id;

  public function setDefaultValues() {
    $defaults = [];

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
    $this->addButtons([
        [
          'type' => 'next',
          'name' => ts('Save'),
          'isDefault' => TRUE,
        ],
        [
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ],
      ]
    );

    if ($this->_action & CRM_Core_Action::DELETE) {
      $this->addButtons([
          [
            'type' => 'next',
            'name' => ts('Delete'),
            'isDefault' => TRUE,
          ],
          [
            'type' => 'cancel',
            'name' => ts('Cancel'),
          ],
        ]
      );
    }

    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    if ($this->_action & CRM_Core_Action::DELETE) {
      return;
    }

    $currencyFormatsKeys = array_keys(JobContractTab::getCurrencyFormats());
    $currencies = array_combine($currencyFormatsKeys, $currencyFormatsKeys);

    $daoClass = CRM_Hrjobcontract_DAO_PayScale::class;

    $payScaleAttr = CRM_Core_DAO::getAttribute($daoClass, 'pay_scale');
    $currencyOptions = ['' => ts('- select -')] + $currencies;
    $amountAttr = CRM_Core_DAO::getAttribute($daoClass, 'amount');
    $frequencyOptions = ['' => ts('- select -')] + SelectValues::commonUnit();
    $isActiveAttr = CRM_Core_DAO::getAttribute($daoClass, 'is_active');

    $this->add('text', 'pay_scale', ts('Label'), $payScaleAttr, TRUE);
    $this->add('select', 'currency', ts('Currency'), $currencyOptions, TRUE);
    $this->add('text', 'amount', ts('Default Amount'), $amountAttr, TRUE);
    $this->add('select', 'pay_frequency', ts('Pay Frequency'), $frequencyOptions, TRUE);
    $this->add('checkbox', 'is_active', ts('Enabled?'), $isActiveAttr);
    $this->addFormRule([self::class, 'formRule']);
  }

  /**
   * @param $fields
   *
   * @return array
   */
  public static function formRule($fields) {
    $errors = [];
    if (!array_key_exists('pay_scale', $fields)) {
      $errors['pay_scale'] = ts("Please enter Pay Scale value");
    }
    if (!array_key_exists('currency', $fields)) {
      $errors['currency'] = ts("Please enter Currency value");
    }
    if (!array_key_exists('amount', $fields)) {
      $errors['amount'] = ts("Please enter Amount value");
    }
    if (!array_key_exists('pay_frequency', $fields)) {
      $errors['pay_frequency'] = ts("Please enter a value fro pay frequency");
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
      $deleteMsg = ts('Selected pay scale has been deleted.');
      Session::setStatus($deleteMsg, 'Success', 'success');
    }
    else {
      $params = $this->getParams();

      if ($this->_action & CRM_Core_Action::UPDATE) {
        $params['id'] = $this->_id;
      }

      $payScale = CRM_Hrjobcontract_BAO_PayScale::create($params);
      $payScaleName = $payScale->pay_scale;

      if ($this->_action & CRM_Core_Action::UPDATE) {
        $msg = ts(
          'The Pay Scale for \'%1\' has been updated.',
          [1 => $payScaleName]
        );
        Session::setStatus($msg, 'Success', 'success');
      }
      else {
        $msg = ts(
          'The Pay Scale for \'%1\' has been added.',
          [1 => $payScaleName]
        );
        Session::setStatus($msg, 'Success', 'success');
      }

      $url = CRM_Utils_System::url('civicrm/pay_scale', 'reset=1&action=browse');
      $session = Session::singleton();
      $session->replaceUserContext($url);
    }
  }

  /**
   * @return array
   */
  private function getParams() {
    $params = $this->exportValues();

    $properties = [
      'pay_scale',
      'currency',
      'amount',
      'pay_frequency',
      'is_active'
    ];

    // set defaults
    foreach ($properties as $property) {
      if (!array_key_exists($property, $params)) {
        $params[$property] = 0;
      }
    }

    return $params;
  }

}
