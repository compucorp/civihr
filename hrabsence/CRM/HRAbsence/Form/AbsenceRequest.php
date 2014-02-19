<?php
/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.2                                                 |
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
 * This file is for civiHR Absence
 */
class CRM_HRAbsence_Form_AbsenceRequest extends CRM_Core_Form {
  public $_customValueCount;
  public $_activityId;
  public $_activityTypeID;
  public $_loginUserID;
  public $_targetContactID;
  public $_managerContactID;
  public $count;
  public $_actStatusId;
  protected $_aid;

  /**
   * Function to set variables up before form is built
   *
   * @return void
   * @access public
   */
  function preProcess() {
    $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);
    $this->_aid = CRM_Utils_Request::retrieve('aid', 'Int', $this);
    $session = CRM_Core_Session::singleton();
    $this->_loginUserID = $session->get('userID');
    if (CRM_Utils_Request::retrieve('cid', 'Positive', $this)) {
      $this->assign('contactId', CRM_Utils_Request::retrieve('cid', 'Positive', $this));
    }
    $activityTypes = CRM_Core_PseudoConstant::activityType();
    $paramsHoliday = array(
      'sequential' => 1,
      'activity_type_id' => array_search('Public Holiday', $activityTypes),
    );
    $resultHoliday = civicrm_api3('Activity', 'get', $paramsHoliday);
    $publicHolidays = array();
    foreach ($resultHoliday['values'] as $key => $val) {
      $pubDate = date("M j, Y", strtotime($val['activity_date_time']));
      $publicHolidays[$pubDate] = $val['subject'];
    }
    $publicHolidays = json_encode($publicHolidays);
    $this->assign('publicHolidays', $publicHolidays);

    if (($this->_action == CRM_Core_Action::VIEW || $this->_action == CRM_Core_Action::UPDATE)) {
      $this->_activityId = CRM_Utils_Request::retrieve('aid', 'String', $this);

      $this->assign('upActivityId', $this->_activityId);
      $paramsAct = array(
        'sequential' => 1,
        'id' => $this->_activityId,
        'return.target_contact_id' => 1,
        'return.assignee_contact_id' => 1,
        'return.source_contact_id' => 1,
        'option.limit' => 31,
      );
      $resultAct = civicrm_api3('Activity', 'get', $paramsAct);
      $this->_activityTypeID = $resultAct['values'][0]['activity_type_id'];
      $this->_targetContactID = $resultAct['values'][0]['target_contact_id'][0];
      $this->_loginUserID = $resultAct['values'][0]['source_contact_id'];
      $this->_actStatusId = $resultAct['values'][0]['status_id'];
      $displayName = CRM_Contact_BAO_Contact::displayName($this->_targetContactID);
      $activityTypes = CRM_HRAbsence_BAO_HRAbsenceType::getActivityTypes();
      $activityType = $activityTypes[$this->_activityTypeID];
      $activity = CRM_HRAbsence_BAO_HRAbsenceType::getActivityStatus();
      $activityStatus = $activity[$this->_actStatusId];
      CRM_Utils_System::setTitle(ts("Absence for  %1 (%2, %3)", array(1 => $displayName, 2 => $activityType, 3 => $activityStatus) ));

      if ($this->_action == CRM_Core_Action::VIEW) {
        $groupTree = CRM_Core_BAO_CustomGroup::getTree('Activity', $this, $this->_activityId, 0, $this->_activityTypeID);
        CRM_Core_BAO_CustomGroup::buildCustomDataView($this, $groupTree);
      }
      else {
        $this->assign('activityType', $this->_activityTypeID);
        CRM_Custom_Form_CustomData::preProcess(
          $this, NULL, $this->_activityTypeID,
          1, 'Activity', $this->_activityId, TRUE
        );
        $this->assign('customValueCount', $this->_customValueCount);
      }
    }
    elseif ($this->_action == CRM_Core_Action::ADD) {
      CRM_Utils_System::setTitle(ts('Absence Request: Add'));
      $this->_activityTypeID = CRM_Utils_Request::retrieve('atype', 'Positive', $this);

      if ($this->_activityTypeID) {
        //only custom data has preprocess hence directly call it
        $this->assign('activityType', $this->_activityTypeID);
        CRM_Custom_Form_CustomData::preProcess(
          $this, NULL, $this->_activityTypeID,
          1, 'Activity', NULL, TRUE
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

  public function setDefaultValues() {
    if ($this->_activityId && $this->_action != CRM_Core_Action::VIEW) {
      return CRM_Custom_Form_CustomData::setDefaultValues($this);
    }
  }

  /**
   * Function to build the form
   *
   * @return void
   * @access public
   */
  function buildQuickForm() {
    if ($this->_action != (CRM_Core_Action::UPDATE) && CRM_Core_Permission::check('edit all contacts')) {
      $this->assign('permEditContact', 1);
    }
    $conId = CRM_Utils_Request::retrieve('cid', 'Positive', $this);
    if (isset($conId) && $conId == 0) {
      $name = "contacts";
      $this->add('text', $name, "contacts");
      $this->add('hidden', $name . '_id');
      $contactDataURL = CRM_Utils_System::url('civicrm/ajax/rest', 'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=contact&contact_type=individual', FALSE, NULL, FALSE);
      $this->assign('contactDataURL', $contactDataURL);
    }

    $activityTypes = CRM_HRAbsence_BAO_HRAbsenceType::getActivityTypes();
    $this->assign('absenceType', $activityTypes[$this->_activityTypeID]);
    $paramsHRJob = array(
      'sequential' => 1,
      'contact_id' => $this->_targetContactID,
      'is_primary' => 1,
    );
    $resultHRJob = civicrm_api3('HRJob', 'get', $paramsHRJob);
    if (!empty($resultHRJob['values'])) {
      $this->assign('emp_position', $resultHRJob['values'][0]['position']);
    }
    $this->assign('emp_name', CRM_Contact_BAO_Contact::displayName($this->_targetContactID));

    if ($this->_action & CRM_Core_Action::VIEW) {
      $paramsAbsences = array(
        'sequential' => 1,
        'source_record_id' => $this->_activityId,
        'option_sort' => "activity_date_time ASC",
        'option.limit' => 31,
      );
      $resultAbsences = civicrm_api3('Activity', 'get', $paramsAbsences);
      $countDays = 0;
      $absenceDateDuration = array();
      foreach ($resultAbsences['values'] as $key => $val) {
        $convertedDate = date("M d, Y (D)", strtotime($val['activity_date_time']));
        if ($val['duration'] == "480") {
          $converteddays = "Full Day";
          $countDays = $countDays + 1;
        }
        elseif ($val['duration'] == "240") {
          $converteddays = "Half Day";
          $countDays = $countDays + 0.5;
        }
        else {
          $converteddays = "Holiday";
        }
        $absenceDateDuration[$convertedDate] = $converteddays;
      }
      $keys = array_keys($absenceDateDuration);
      $count = count($keys) - 1;
      $fromdateVal = explode('(', $keys[0]);
      $todateVal = explode('(', $keys[$count]);
      $this->assign('fromDate', date("M j, Y", strtotime($fromdateVal[0])));
      $this->assign('toDate', date("M j, Y", strtotime($todateVal[0])));
      $this->assign('absenceDateDuration', $absenceDateDuration);
      $this->_fromDate = $fromdateVal[0];
      $this->_toDate = $todateVal[0];
      $this->assign('totalDays', $countDays);
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
    if ($this->_action && ($this->_action == CRM_Core_Action::ADD)) {
      $this->addButtons(
        array(
          array(
            'type' => 'submit',
            'name' => ts('Save'),
            'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
            'isDefault' => TRUE,
          ),
        )
      );
    }
    elseif ($this->_action == (CRM_Core_Action::UPDATE)) {
      $this->add('hidden', 'source_record_id', $this->_aid);
      $params = array(
        'sequential' => 1,
        'source_record_id' => $this->_aid,
        'option_sort' => "activity_date_time ASC",
        'option.limit' => 31,
      );
      $result = civicrm_api3('Activity', 'get', $params);
      $start_date = date_create($result['values'][0]['activity_date_time']);
      $end_date = date_create($result['values'][$result['count'] - 1]['activity_date_time']);
      $this->assign('fromDate', date_format($start_date, 'm/d/Y'));
      $this->assign('toDate', date_format($end_date, 'm/d/Y'));

      global $user;
      $today = time();
      $date1 = new DateTime(date("M j, Y", $today));
      $intervals = $date1->diff($end_date);
      if ((($intervals->days >= 0) && ($intervals->invert == 0)) && (in_array('administrator', array_values($user->roles)) || ((isset($this->_managerContactID)) == (isset($this->_loginUserID)))) && ($this->_actStatusId == 1)) {
        $this->addButtons(
          array(
            array(
              'type' => 'submit',
              'name' => ts('Save'),
              'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
              'isDefault' => TRUE,
            ),
            array(
              'type' => 'submit',
              'name' => ts('Cancel Absence Request'),
              'subName' => 'cancel'
            ),
            array(
              'type' => 'submit',
              'name' => ts('Approve'),
              'subName' => 'approve'
            ),
            array(
              'type' => 'submit',
              'name' => ts('Reject'),
              'subName' => 'reject'
            ),
          )
        );
      }
      else {
        $this->addButtons(
          array(
            array(
              'type' => 'submit',
              'name' => ts('Cancel'),
              'subName' => 'cancelbutton'
            ),
          )
        );
      }
    }
    else {
      global $user;
      $now = time();
      $datetime1 = new DateTime(date("M j, Y", $now));
      $datetime2 = new DateTime($this->_toDate);
      $interval = $datetime1->diff($datetime2);

      if (($interval->days >= 0) && ($interval->invert == 0)) {
        if ((in_array('administrator', array_values($user->roles)) || ((isset($this->_managerContactID)) == (isset($this->_loginUserID)))) && ($this->_actStatusId == 1)) {
          $this->addButtons(
            array(
              array(
                'type' => 'submit',
                'name' => ts('Cancel Absence Request'),
                'subName' => 'cancel'
              ),
            )
          );
        }
        else {
          $this->addButtons(
            array(
              array(
                'type' => 'submit',
                'name' => ts('Cancel'),
                'subName' => 'cancelbutton'
              ),
            )
          );
        }
      }
    }
    if ( $this->_action == CRM_Core_Action::UPDATE || $this->_action == CRM_Core_Action::ADD ) {
      $this->addFormRule(array('CRM_HRAbsence_Form_AbsenceRequest', 'formRule'));
    }
  }

  /**
   * global form rule
   *
   * @param array $fields  the input form values
   * @param array $files   the uploaded files if any
   * @param array $options additional user data
   *
   * @return true if no errors, else array of errors
   * @access public
   * @static
   */
  static function formRule($fields, $files, $self) {
    $errors = array();
    if (isset($fields['start_date_display'])) {
      $dateFrom = $fields['start_date_display'];
    }
    if (isset($fields['start_date_display'])) {
      $dateTo = $fields['end_date_display'];
    }
    if (isset($dateFrom) && isset($dateTo)){
      $days = (strtotime($dateTo)- strtotime($dateFrom))/24/3600;
      $days = $days + 1;
    }
    if (empty($dateFrom)) {
      $errors['start_date'] = ts('From date is required.');
    }
    if (empty($dateTo)) {
      $errors['end_date'] = ts('End date is required.');
    }
    if (strtotime(isset($fields['start_date_display'])) && strtotime(isset($fields['end_date_display'])) && strtotime(isset($fields['start_date_display'])) > strtotime(isset($fields['end_date_display']))) {
      $errors['end_date'] = ts('From date cannot be greater than to date.');
    }
    if (isset($days) && $days > 31) {
      $errors['end_date'] = ts('End date should be within a month.');
    }
    return $errors;
  }

  /**
   * Function to process the form
   *
   * @access public
   *
   * @return void
   */
  public function postProcess() {
    $submitValues = $this->_submitValues;
    if (!empty($submitValues['contacts_id'])) {
      $this->_targetContactID = $submitValues['contacts_id'];
    }
    $absentDateDurations = array();

    if (!empty($submitValues['date_values'])) {
      foreach (explode('|', $submitValues['date_values']) as $key => $dateString) {
        if ($dateString) {
          $values = explode('(', $dateString);
          $date = CRM_Utils_Date::processDate($values[0]);
          $valuesDate = explode(':', $dateString);
          $absentDateDurations[$date] = (int) $valuesDate[1];
        }
      }
    }

    $activityStatus = CRM_HRAbsence_BAO_HRAbsenceType::getActivityStatus('name');
    if ($this->_action & (CRM_Core_Action::ADD)) {
      $activityParam = array(
        'sequential' => 1,
        'source_contact_id' => $this->_loginUserID,
        'target_contact_id' => $this->_targetContactID,
        'assignee_contact_id' => $this->_managerContactID,
        'activity_type_id' => $this->_activityTypeID,
      );

      //we want to keep the activity status in Scheduled for new absence
      $activityParam['status_id'] = CRM_Utils_Array::key('Scheduled', $activityStatus);
      $result = civicrm_api3('Activity', 'create', $activityParam);

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
        'sequential' => 1,
        'source_record_id' => $result['id'],
        'activity_type_id' => CRM_Core_OptionGroup::getValue('activity_type', 'Absence', 'name'),
      );
      $activityLeavesParam['status_id'] = $activityParam['status_id'];
      foreach ($absentDateDurations as $date => $duration) {
        $activityLeavesParam['activity_date_time'] = $date;
        $activityLeavesParam['duration'] = $duration;
        civicrm_api3('Activity', 'create', $activityLeavesParam);
      }

      CRM_Core_Session::setStatus(ts('Your absences have been applied.'), ts('Saved'), 'success');
      $buttonName = $this->controller->getButtonName();
      if ($buttonName == $this->getButtonName('submit')) {
        return CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/absence/set', "reset=1&action=view&aid={$result['id']}"));
      }
    }
    elseif ($this->_action == CRM_Core_Action::UPDATE) {
      if (array_key_exists('_qf_AbsenceRequest_submit_cancel', $submitValues)) {
        $statusId = CRM_Utils_Array::key('Cancelled', $activityStatus);
        $activityParam = array(
          'sequential' => 1,
          'id' => $this->_activityId,
          'activity_type_id' => $this->_activityTypeID,
          'status_id' => $statusId
        );
        $result = civicrm_api3('Activity', 'create', $activityParam);
        CRM_Core_Session::setStatus(ts('Your absences have been Cancelled.'), ts('Cancelled'), 'success');
        return CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/absence/set', "reset=1&action=view&aid={$result['id']}"));
      }
      elseif (array_key_exists('_qf_AbsenceRequest_submit_approve', $submitValues)) {
        $statusId = CRM_Utils_Array::key('Completed', $activityStatus);
        $activityParam = array(
          'sequential' => 1,
          'id' => $this->_activityId,
          'activity_type_id' => $this->_activityTypeID,
          'status_id' => $statusId
        );
        $result = civicrm_api3('Activity', 'create', $activityParam);
        CRM_Core_Session::setStatus(ts('Your absences have been Approved.'), ts('Approved'), 'success');
        return CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/absence/set', "reset=1&action=view&aid={$result['id']}"));
      }
      elseif (array_key_exists('_qf_AbsenceRequest_submit_reject', $submitValues)) {
        $statusId = CRM_Utils_Array::key('Rejected', $activityStatus);
        $activityParam = array(
          'sequential' => 1,
          'id' => $this->_activityId,
          'activity_type_id' => $this->_activityTypeID,
          'status_id' => $statusId
        );
        $result = civicrm_api3('Activity', 'create', $activityParam);
        CRM_Core_Session::setStatus(ts('Your absences have been Rejected.'), ts('Rejected'), 'success');
        return CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/absence/set', "reset=1&action=view&aid={$result['id']}"));
      }
      elseif (array_key_exists('_qf_AbsenceRequest_submit_cancelbutton', $submitValues)) {
        return CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$this->_targetContactID}#hrabsence/list"));
      }
      else {
        $params = array(
          'sequential' => 1,
          'source_record_id' => $submitValues['source_record_id'],
          'option.limit' => 31,
        );
        $result = civicrm_api3('Activity', 'get', $params);
        foreach ($result['values'] as $row_result) {
          $params = array(
            'sequential' => 1,
            'id' => $row_result['id'],
          );
          civicrm_api3('Activity', 'delete', $params);
        }

        foreach ($absentDateDurations as $date => $duration) {
          $params = array(
            'sequential' => 1,
            'activity_type_id' => $this->_activityTypeID,
            'source_record_id' => $submitValues['source_record_id'],
            'activity_date_time' => $date,
            'duration' => $duration,
          );
          $result = civicrm_api3('Activity', 'create', $params);
        }
        $buttonName = $this->controller->getButtonName();
        if ($buttonName == $this->getButtonName('submit')) {
          $this->_aid = $submitValues['source_record_id'];
          return CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/absence/set', "reset=1&action=view&aid={$submitValues['source_record_id']}"));
        }
      }
    }
    elseif ($this->_action & CRM_Core_Action::VIEW) {
      if (CRM_Utils_Request::retrieve('aid', 'Positive', $this)) {
        $activityIDs = CRM_Utils_Request::retrieve('aid', 'Positive', $this);
      }

      if (array_key_exists('_qf_AbsenceRequest_submit_cancel', $submitValues)) {
        $statusId = CRM_Utils_Array::key('Cancelled', $activityStatus);
        $statusMsg = ts('Your absences have been Cancelled');
      }
      elseif (array_key_exists('_qf_AbsenceRequest_submit_cancelbutton', $submitValues)) {
        return CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$this->_targetContactID}#hrabsence/list"));
      }
      $activityParam = array(
        'sequential' => 1,
        'id' => $this->_activityId,
        'activity_type_id' => $this->_activityTypeID,
        'status_id' => $statusId
      );
      civicrm_api3('Activity', 'create', $activityParam);
      CRM_Core_Session::setStatus($statusMsg, 'success');
      return CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/absence/set', "reset=1&action=view&aid={$activityIDs}"));
    }
  }
}