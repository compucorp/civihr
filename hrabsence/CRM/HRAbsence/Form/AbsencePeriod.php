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
 * This class generates form components for Absence Period
 *
 */
class CRM_HRAbsence_Form_AbsencePeriod extends CRM_Core_Form {


function setDefaultValues() {
    $defaults = array();

    if ($this->_id) {
      $defaults = CRM_HRAbsence_BAO_HRAbsencePeriod::getDefaultValues($this->_id);
      list($defaults['start_date'], $defaults['start_date_time']) = CRM_Utils_Date::setDateDefaults($defaults['start_date'], 'activityDateTime');
      list($defaults['end_date'], $defaults['end_date_time']) = CRM_Utils_Date::setDateDefaults($defaults['end_date'], 'activityDateTime');
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
      $this->_title = CRM_Core_DAO::getFieldValue('CRM_HRAbsence_DAO_HRAbsencePeriod', $this->_id, 'title');
      CRM_Utils_System::setTitle($this->_title . ' - ' . ts( 'Absence Period'));
    }
    if ($this->_action & CRM_Core_Action::DELETE) {
      return;
    }

    $this->add('text', 'title', ts('Title'), CRM_Core_DAO::getAttribute('CRM_HRAbsence_DAO_HRAbsencePeriod', 'title'), TRUE);

    $this->addDateTime('start_date', ts('Start Date'), TRUE, array('formatType' => 'activityDateTime'));
    $this->addDateTime('end_date', ts('End Date'), TRUE, array('formatType' => 'activityDateTime'));

    $this->addFormRule(array('CRM_HRAbsence_Form_AbsencePeriod', 'formRule'), $this);
  }

  static function formRule($fields, $files, $self) {
    $errors = array();

    if (CRM_Core_DAO::getFieldValue('CRM_HRAbsence_DAO_HRAbsencePeriod', $fields['title'], 'id', 'title')) {
      $errors['title'] = ts('Title already exists in Database.');
    }

    $start = CRM_Utils_Date::processDate($fields['start_date']);
    $end = CRM_Utils_Date::processDate($fields['end_date']);

    if (($end < $start) && ($end != 0)) {
      $errors['end_date'] = ts('End date should be after Start date.');
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
      CRM_HRAbsence_BAO_HRAbsencePeriod::del($this->_id);
      CRM_Core_Session::setStatus(ts('Selected absence period has been deleted.'), 'Success', 'success');
    }
    else {
      $params = $ids = array( );
      // store the submitted values in an array
      $params = $this->exportValues();

      if ($this->_action & CRM_Core_Action::UPDATE) {
        $params['id'] = $this->_id;
      }

      //format params
      $params['name'] = CRM_Utils_String::munge($params['title']);
      $params['start_date'] = CRM_Utils_Date::processDate($params['start_date'], $params['start_date_time']);
      $params['end_date'] = CRM_Utils_Date::processDate($params['end_date'], $params['end_date_time']);

      $absencePeriod = CRM_HRAbsence_BAO_HRAbsencePeriod::create($params);

      if ($this->_action & CRM_Core_Action::UPDATE) {
        CRM_Core_Session::setStatus(ts('The absence period \'%1\' has been updated.', array( 1 => $absencePeriod->title)), 'Success', 'success');
      }
      else {
        CRM_Core_Session::setStatus(ts('The absence period \'%1\' has been added.', array( 1 => $absencePeriod->title)), 'Success', 'success');
      }

      $url = CRM_Utils_System::url('civicrm/absence/period', 'reset=1&action=browse');
      $session = CRM_Core_Session::singleton();
      $session->replaceUserContext($url);
    }
  }
}
