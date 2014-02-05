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
 public $_customValueCount;
 public $_activityTypeID;
 public $_loginUserID;
 public $_targetContactID;
 public $_managerContactID;

  function buildQuickForm() {
    $activityTypes = CRM_HRAbsence_BAO_HRAbsenceType::getActivityTypes();
    $this->assign('absenceType', $activityTypes[$this->_activityTypeID]);
    $paramsHRJob = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id' => $this->_targetContactID,
      'is_primary' => 1,
    );
    $resultHRJob = civicrm_api('HRJob', 'get', $paramsHRJob);
    if (!empty($resultHRJob['values'])) {
      $this->assign('emp_position', $resultHRJob['values'][0]['position']);
    }
    $this->assign('emp_name', CRM_Contact_BAO_Contact::displayName($this->_targetContactID));

   if ($this->_action & CRM_Core_Action::VIEW) {
      $paramsAbsences = array(
        'version' => 3,
        'sequential' => 1,
        'source_record_id' => $this->_activityId,
        'option_sort'=>"activity_date_time ASC",
      );
      $resultAbsences = civicrm_api('Activity', 'get', $paramsAbsences);
      $countDays =0; 
      $absenceDateDuration = array();
      foreach ($resultAbsences['values'] as $key => $val) {
        $convertedDate = date("M d, Y", strtotime($val['activity_date_time']));
        if ($val['duration'] == "480") {
          $converteddays = "Full Day";
          $countDays=$countDays+1;
        } else {
          $converteddays = "Half Day";
          $countDays=$countDays+0.5;
        }
        $absenceDateDuration[$convertedDate]=$converteddays;
      }
      $keys = array_keys($absenceDateDuration);
      $count = count($keys) - 1;
      $this->assign('fromDate', date("M j, Y", strtotime($keys[0])));
      $this->assign('toDate', date("M j, Y", strtotime($keys[$count])));
      $this->assign('absenceDateDuration', $absenceDateDuration);
      $this->_fromDate = date("M j, Y", strtotime($keys[0]));
      $this->_toDate = date("M j, Y", strtotime($keys[$count]));
      $this->assign('totalDays',$countDays);
   }

   if ($this->_action && (CRM_Core_Action::ADD || CRM_Core_Action::UPDATE)) {
     $this->assign('customDataSubType', $this->_activityTypeID);
     if ($this->_customValueCount) {
       CRM_Custom_Form_CustomData::buildQuickForm($this);
     }

     $this->assign('loginUserID', $this->_loginUserID);
     if (!empty($resultHRJob['values'])) {
       $this->_managerContactID = $resultHRJob['values'][0]['manager_contact_id'];
     }
     $this->add('hidden', 'date_values', '', array('id' => 'date_values'));
   }
   $this->addDate('start_date', ts('Start Date'), FALSE, array('formatType' => 'activityDate'));
   $this->addDate('end_date', ts('End Date / Time'), FALSE, array('formatType' => 'activityDate'));
   if ($this->_action && ($this->_action == CRM_Core_Action::ADD || $this->_action == CRM_Core_Action::UPDATE) ) {
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
            'name' => ts('Cancel Absence Request'),
            ),
        )
      );
    }
   else {
     $now = time(); 
     $fromDate = date("Y-m-d", strtotime($keys[0]));
     $from_date = strtotime($fromDate);
     $datediff = $from_date - $now ;
     $dayDiff = floor($datediff/(60*60*24));
     if ($dayDiff>0) {
       $this->addButtons(
         array(
           array(
             'type' => 'cancel',
             'name' => ts('Cancel Absence Request'),
           ),
         )
       );
     }
   }
 }

  function preProcess() {
    CRM_Utils_System::setTitle( ts('Absence Request: View') );
    $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);
    $session = CRM_Core_Session::singleton();
    $this->_loginUserID = $session->get('userID');

    if(($this->_action == 4 || $this->_action == 2)) {
      $this->_activityId = CRM_Utils_Request::retrieve('aid', 'String', $this);

      $this->assign('upActivityId', $this->_activityId);
      $paramsAct = array(
        'version' => 3,
        'sequential' => 1,
        'id' => $this->_activityId,
        'return.target_contact_id' => 1,
        'return.assignee_contact_id' => 1,
        'return.source_contact_id' => 1,
      );
      $resultAct = civicrm_api('Activity', 'get', $paramsAct);
      $this->_activityTypeID = $resultAct['values'][0]['activity_type_id'];
      $this->_targetContactID = $resultAct['values'][0]['target_contact_id'][0];
      $this->_loginUserID = $resultAct['values'][0]['source_contact_id'][0];
      $groupTree = CRM_Core_BAO_CustomGroup::getTree('Activity', $this, $this->_activityId, 0, $this->_activityTypeID);
      CRM_Core_BAO_CustomGroup::buildCustomDataView($this, $groupTree);     
    }
    elseif ( $this->_action == 1) {
      $this->_activityTypeID = CRM_Utils_Request::retrieve('atype', 'Positive', $this);

      if ($this->_activityTypeID) {
        //only custom data has preprocess hence directly call it
        $this->assign('activityType', $this->_activityTypeID);
        CRM_Custom_Form_CustomData::preProcess(
          $this, NULL, $this->_activityTypeID,
          1, 'Activity' , NULL, TRUE
        );
        $this->assign('customValueCount', $this->_customValueCount);
      }

      if (CRM_Utils_Request::retrieve('cid', 'Positive', $this)) {
        $this->_targetContactID = CRM_Utils_Request::retrieve('cid', 'Positive', $this);
      }
      else {
        //if there is no cid passed then consider target contact as logged in user
        //who will applying leave for himself
        $this->_targetContactID = $this->_loginUserID;
      }
    }
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.hrabsence', 'css/hrabsence.css');
    parent::preProcess();
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    $submitValues = $this->_submitValues;
    $absentDateDurations = array();

    if (!empty($submitValues['date_values'])) {
      foreach(explode('|', $submitValues['date_values']) as $key => $dateString) {
        if ($dateString) {
          $values = explode(':', $dateString);
          $date = CRM_Utils_Date::processDate($values[0]);
          $absentDateDurations[$date] = (int)$values[1];
        }
      }
    }

    if ($this->_action && (CRM_Core_Action::ADD || CRM_Core_Action::UPDATE)) {
      $activityParam = array(
        'version' => 3,
        'sequential' => 1,
        'source_contact_id' => $this->_loginUserID,
        'target_contact_id' => $this->_targetContactID,
        'assignee_contact_id' => $this->_managerContactID,
        'activity_type_id' => $this->_activityTypeID,
      );

      if ($this->_action & (CRM_Core_Action::ADD)) {
        //we want to keep the activity status in Scheduled for new absence
        $activityParam['status_id'] = CRM_Core_OptionGroup::values('activity_status', FALSE, NULL, NULL, 'AND v.name = "Scheduled"');
      }
      $result = civicrm_api('Activity', 'create', $activityParam);

      //save the custom data
      if (!empty($submitValues['hidden_custom'])) {
        $customFields = CRM_Utils_Array::crmArrayMerge(
          CRM_Core_BAO_CustomField::getFields('Activity', FALSE, FALSE, $this->_activityTypeID),
          CRM_Core_BAO_CustomField::getFields('Activity', FALSE, FALSE, NULL, NULL, TRUE)
        );
        $customValues = CRM_Core_BAO_CustomField::postProcess($submitValues, $customFields, $result['id'], 'Activity');
        CRM_Core_BAO_CustomValueTable::store($customValues, 'civicrm_activity', $result['id']);
      }

      $activityLeavesParam = array(
        'version' => 3,
        'sequential' => 1,
        'source_record_id' => $result['id'],
        'activity_type_id' => CRM_Core_OptionGroup::getValue('activity_type', 'Absence', 'name'),
      );

      if ($this->_action & (CRM_Core_Action::ADD)) {
        $activityLeavesParam['status_id'] = $activityParam['status_id'];
      }

      foreach ($absentDateDurations as $date => $duration) {
        $activityLeavesParam['activity_date_time'] = $date;
        $activityLeavesParam['duration'] = $duration;
        civicrm_api('Activity', 'create', $activityLeavesParam);
      }

      CRM_Core_Session::setStatus(ts('Your absences have been applied.'), ts('Saved'), 'success');

      $buttonName = $this->controller->getButtonName();
      if ($buttonName == $this->getButtonName('submit')) {
        return CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/absences/set', "reset=1&action=view&aid={$result['id']}"));
      }
    }
  }

}