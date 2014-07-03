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
 * This class generates form components for Public Holidays
 *
 */
class CRM_HRAbsence_Form_PublicHolidays extends CRM_Core_Form {

  function setDefaultValues() {
    $defaults = array();

    if ($this->_id) {
      $params = array('id' => $this->_id);
      CRM_Activity_BAO_Activity::retrieve($params, $defaults);
      if (!CRM_Utils_Array::value('activity_date_time', $defaults)) {
        list($defaults['activity_date_time'], $defaults['activity_date_time_time']) = CRM_Utils_Date::setDateDefaults(NULL, 'activityDateTime');
      }
        list($defaults['activity_date_time'],
          $defaults['activity_date_time_time']
          ) = CRM_Utils_Date::setDateDefaults($defaults['activity_date_time'], 'activityDateTime');
        $status = CRM_Core_PseudoConstant::activityStatus();
        $defaults['status_id'] = ($defaults['status_id'] == array_search('Scheduled', $status)) ? 1 : 0;
    }
    else {
      $defaults['status_id'] = 1;
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
      $this->_subject = CRM_Core_DAO::getFieldValue('CRM_Activity_DAO_Activity', $this->_id, 'subject');
      CRM_Utils_System::setTitle($this->_subject . ' - ' . ts( 'Public Holiday'));
    }
    if ($this->_action & CRM_Core_Action::DELETE) {
      return;
    }
    $this->add('text', 'subject', ts('Title'), CRM_Core_DAO::getAttribute('CRM_Activity_DAO_Activity', 'subject'), TRUE);
    $this->addDateTime('activity_date_time', ts('Date'), TRUE, array('formatType' => 'activityDateTime'));
    $this->add('checkbox', 'status_id', ts('Enabled?'));
  }

  /**
   * Function to process the form
   *
   * @access public
   * @return void
   */
  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    if ($this->_action & CRM_Core_Action::DELETE) {
      $params['id'] = $this->_id;
      CRM_Activity_BAO_Activity::deleteActivity($params);
      CRM_Core_Session::setStatus(ts('Selected Public Holiday has been deleted.'), 'Success', 'success');
    }
    else {
      $params = $ids = array( );
      // store the submitted values in an array
      $params = $this->exportValues();
      $activity_type_id = civicrm_api3('OptionValue', 'getvalue', array('name' => 'Public Holiday', 'return'=> 'value',) );
      $params['activity_type_id'] = $activity_type_id;
      $params['source_contact_id'] = $session->get('userID');
      $status = CRM_Core_PseudoConstant::activityStatus();
      $params['status_id'] =  !empty($params['status_id']) ? array_search('Scheduled', $status) : array_search('Cancelled', $status);
      $params['activity_date_time'] = CRM_Utils_Date::processDate($params['activity_date_time'], $params['activity_date_time_time']);

      if ($this->_action & CRM_Core_Action::UPDATE) {
        $params['id'] = $this->_id;
      }

      $publicHoliday = CRM_Activity_BAO_Activity::create($params);

      if ($this->_action & CRM_Core_Action::UPDATE) {
        CRM_Core_Session::setStatus(ts('The Public holiday \'%1\' has been updated.', array( 1 => $publicHoliday->subject)), 'Success', 'success');
      }
      else {
        CRM_Core_Session::setStatus(ts('The Public Holiday \'%1\' has been added.', array( 1 => $publicHoliday->subject)), 'Success', 'success');
      }

      $url = CRM_Utils_System::url('civicrm/absence/holidays', 'reset=1&action=browse');
      $session = CRM_Core_Session::singleton();
      $session->replaceUserContext($url);
    }
  }
}
