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
 * This class generates form components for Hours Location
 *
 */
class CRM_Hrjobcontract_Form_HoursLocation extends CRM_Core_Form {

  function setDefaultValues() {
    $defaults = array();

    if ($this->_id) {
      $defaults = CRM_Hrjobcontract_BAO_HoursLocation::getDefaultValues($this->_id);
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

    $this->add('text', 'location', ts('Location'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HoursLocation', 'location'), TRUE);
    $this->add('text', 'standard_hours', ts('Standard Hours'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HoursLocation', 'standard_hours'), TRUE);
    $this->add('select', 'periodicity', ts('Periodicity'), array('' => ts('- select -')) + CRM_Hrjobcontract_SelectValues::commonUnit(), TRUE);

    $this->add('checkbox', 'is_active', ts('Enabled?'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HoursLocation', 'is_active'));

    $this->addFormRule(array('CRM_Hrjobcontract_Form_HoursLocation', 'formRule'), $this);
  }

  static function formRule($fields, $files, $self) {
    $errors = array();
    if (!array_key_exists('location', $fields)) {
      $errors['location'] = ts("Please enter Location value");
    }
    if (!array_key_exists('standard_hours', $fields)) {
      $errors['standard_hours'] = ts("Please enter Standard Hours value");
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
      CRM_Hrjobcontract_BAO_HoursLocation::del($this->_id);
      CRM_Core_Session::setStatus(ts('Selected hours location has been deleted.'), 'Success', 'success');
    }
    else {
      $params = $ids = array( );
      // store the submitted values in an array
      $params = $this->exportValues();

      foreach (array('location', 'standard_hours', 'periodicity') as $key => $index) {
        if(!array_key_exists($index, $params)) {
          $params[$index] = 0;
        }
      }

      if ($this->_action & CRM_Core_Action::UPDATE) {
        $params['id'] = $this->_id;
      }

      $hoursLocation = CRM_Hrjobcontract_BAO_HoursLocation::create($params);

      if ($this->_action & CRM_Core_Action::UPDATE) {
        CRM_Core_Session::setStatus(ts('The Hours Location for \'%1\' has been updated.', array( 1 => $hoursLocation->location)), 'Success', 'success');
      }
      else {
        CRM_Core_Session::setStatus(ts('The Hours Location for \'%1\' has been added.', array( 1 => $hoursLocation->location)), 'Success', 'success');
      }

      $url = CRM_Utils_System::url('civicrm/hours_location', 'reset=1&action=browse');
      $session = CRM_Core_Session::singleton();
      $session->replaceUserContext($url);
    }
  }
}
