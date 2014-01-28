<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
 * Files required
 */

/**
 * This file is for civievent search
 */
class CRM_HRAbsence_Form_EmployeeAbsenceRequestPage extends CRM_Core_Form {

  function buildQuickForm() {
    $this->addDate('start_date', ts('Start Date'), FALSE, array('formatType' => 'activityDate'));
    $this->addDate('end_date', ts('End Date / Time'), FALSE, array('formatType' => 'activityDate'));
    if ($this->_action && (CRM_Core_Action::UPDATE || CRM_Core_Action::ADD) ) {
      $this->addButtons(
        array(
          array(
            'type' => 'submit',
            'name' => ts('Save'),
            'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
            'isDefault' => TRUE,
          ),
          array(
            'type' => 'cancel',
            'name' => ts('Cancel'),
            ),
        )
      );
    }
    else {
      $this->addButtons(
        array(
          array(
            'type' => 'cancel',
            'name' => ts('Cancel'),
            ),
          )
        );
    }
  }

  function preProcess() {
    $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);
    if ( CRM_Utils_Request::retrieve('cid', 'String', $this) ) {
      $this->_loginUserID = CRM_Utils_Request::retrieve('cid', 'String', $this);
      $this->assign('loginuserid', $this->_loginUserID);
    } else {
      $session = CRM_Core_Session::singleton();
      $this->_loginUserID = $session->get('userID');
      $this->assign('loginuserid', $this->_loginUserID);
    }
    $paramsHRJob = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id' => $this->_loginUserID,
      'is_primary' => 1,
    );
    $resultHRJob = civicrm_api('HRJob', 'get', $paramsHRJob);
    $this->assign('emp_position', $resultHRJob['values'][0]['position']);
    $paramsContact = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id' => $this->_loginUserID,
    );
    $resultContact = civicrm_api('Contact', 'get', $paramsContact);
    $this->assign('emp_name', $resultContact['values'][0]['display_name']);

    if( ($this->_action == 4 || $this->_action == 2) && CRM_Utils_Request::retrieve('activityid', 'String', $this) ) {
      $this->_activityId = CRM_Utils_Request::retrieve('activityid', 'String', $this);
      $this->assign('upActivityId', $this->_activityId);
      $paramsAct = array(
        'version' => 3,
        'sequential' => 1,
        'id' => $this->_activityId,
      );
      $resultAct = civicrm_api('Activity', 'get', $paramsAct);
      
      $paramsAbsenceType = array(
        'version' => 3,
        'sequential' => 1,
        'value' => $resultAct['values'][0]['activity_type_id'],
      );
      $resultAbsenceType = civicrm_api('OptionValue', 'get', $paramsAbsenceType);
      $this->assign('selectedAbsenceType', $resultAbsenceType['values'][0]['name']);
      $paramsAbsences = array(
        'version' => 3,
        'sequential' => 1,
        'source_record_id' => $resultAct['id'],
        'option_sort'=>"activity_date_time ASC",
      );
      $resultAbsences = civicrm_api('Activity', 'get', $paramsAbsences);
      
      $absenceDateDuration = array();
      foreach ($resultAbsences['values'] as $key => $val) {
        $convertedDate = date("M d, Y", strtotime($val['activity_date_time']));
        if ($val['duration'] == "480") {
          $converteddays = "Full Day";
        } else {
          $converteddays = "Half Day";
        }
        $absenceDateDuration[$convertedDate]=$converteddays;
      }
      $keys = array_keys($absenceDateDuration);
      $count = count($keys) - 1;
      $this->assign('fromDate', date("m/d/Y", strtotime($keys[0])));
      $this->assign('toDate', date("m/d/Y", strtotime($keys[$count])));
      $this->assign('absenceDateDuration', $absenceDateDuration); 
      $this->_fromDate = date("m/d/Y", strtotime($keys[0]));
      $this->_toDate = date("m/d/Y", strtotime($keys[$count]));  
    }
    elseif ( $this->_action == 1 && CRM_Utils_Request::retrieve('absencetype', 'String', $this) ) {
      $selAbsenceType = CRM_Utils_Request::retrieve('absencetype', 'String', $this);
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'value' => $selAbsenceType,
      );
      $result = civicrm_api('OptionValue', 'get', $params);
      $this->assign('absenceTypes', $result['values'][0]['name']);
    }
    parent::preProcess();
  }

  public function postProcess() {
    parent::postProcess();
  }

}