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
 * This class generates form components for Absence Type
 *
 */
class CRM_HRAbsence_Form_AbsenceType extends CRM_Core_Form {

  function setDefaultValues() {
    $defaults = array();

    if ($this->_id) {
      $defaults = CRM_HRAbsence_BAO_HRAbsenceType::getDefaultValues($this->_id);
    }
    else {
      $defaults['is_active'] = $defaults['allow_debits'] = 1;
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
    if ($this->_id) {
      $this->_title = CRM_Core_DAO::getFieldValue('CRM_HRAbsence_DAO_HRAbsenceType', $this->_id, 'title');
      CRM_Utils_System::setTitle($this->_title . ' - ' . ts( 'Absence Type'));
    }
    if ($this->_action & CRM_Core_Action::DELETE) {
      return;
    }
    $this->add('text', 'title', ts('Title'), CRM_Core_DAO::getAttribute('CRM_HRAbsence_DAO_HRAbsenceType', 'title'), TRUE);

    $this->add('checkbox', 'allow_credits', ts('Allow Credits?'), CRM_Core_DAO::getAttribute('CRM_HRAbsence_DAO_HRAbsenceType', 'allow_credits'));
   $this->add('checkbox', 'allow_debits', ts('Allow Debits?'), CRM_Core_DAO::getAttribute('CRM_HRAbsence_DAO_HRAbsenceType', 'allow_debits'));
    $this->add('checkbox', 'is_active', ts('Enabled?'), CRM_Core_DAO::getAttribute('CRM_HRAbsence_DAO_HRAbsenceType', 'is_active'));

    $this->addFormRule(array('CRM_HRAbsence_Form_AbsenceType', 'formRule'), $this);
  }

  static function formRule($fields, $files, $self) {
    $errors = array();
    if (!array_key_exists('allow_debits', $fields) && !array_key_exists('allow_credits', $fields)) {
      $errors['allow_debits'] = $errors['allow_credits'] = ts("Please choose either 'Allow Debits' and/or 'Allow Credits'");
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
      CRM_HRAbsence_BAO_HRAbsenceType::del($this->_id);
      CRM_Core_Session::setStatus(ts('Selected absence type has been deleted.'), 'Success', 'success');
    }
    else {
      $params = $ids = array( );
      // store the submitted values in an array
      $params = $this->exportValues();

      foreach (array('allow_debits', 'allow_credits', 'is_active') as $key => $index) {
        if(!array_key_exists($index, $params)) {
          $params[$index] = 0;
        }
      }

      if ($this->_action & CRM_Core_Action::UPDATE) {
        $params['id'] = $this->_id;
      }

      $absenceType = CRM_HRAbsence_BAO_HRAbsenceType::create($params);

      if ($this->_action & CRM_Core_Action::UPDATE) {
        CRM_Core_Session::setStatus(ts('The absence type \'%1\' has been updated.', array( 1 => $absenceType->title)), 'Success', 'success');
      }
      else {
        CRM_Core_Session::setStatus(ts('The absence type \'%1\' has been added.', array( 1 => $absenceType->title)), 'Success', 'success');
      }

      $url = CRM_Utils_System::url('civicrm/absence/type', 'reset=1&action=browse');
      $session = CRM_Core_Session::singleton();
      $session->replaceUserContext($url);
    }
  }
}
