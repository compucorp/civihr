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
 * This class generates form components for applying Vacancy
 *
 */
class CRM_HRRecruitment_Form_Application extends CRM_Core_Form {

/**
   * Function to set variables up before form is built
   *
   * @return void
   * @access public
   */
  function preProcess() {
    $this->_id = CRM_Utils_Request::retrieve('id', 'Integer', $this);
    $this->_contactID = CRM_Utils_Request::retrieve('cid', 'Integer', $this);
    if (!isset($this->_contactID)) {
      $this->_contactID = 0;
    }

    if (!$this->_id) {
      CRM_Core_Error::fatal(ts('There is no related Vacancy to apply'));
    }

    $ufJoinParams = array(
      'module' => 'Vacancy',
      'entity_id' => $this->_id,
      'module_data' => 'application_profile',
    );
    $ufJoin = new CRM_Core_DAO_UFJoin();
    $ufJoin->copyValues($ufJoinParams);
    $ufJoin->find(TRUE);
    $this->_profileID = $ufJoin->uf_group_id;
  }

  function setDefaultValues() {

    $defaults = array();
    $profileFields = CRM_Core_BAO_UFGroup::getFields($this->_profileID);
    $contactID = $this->_contactID;
    $entityCaseID = NULL;

    $cases = CRM_Case_BAO_Case::retrieveCaseIdsByContactId($contactID, FALSE, 'Application');
    $entityCaseID = end($cases);

    if ($contactID) {
      $options = array();
      $fields = array();
      foreach ($profileFields as $name => $field) {
        if (substr($name, 0, 7) == 'custom_') {
          $id = substr($name, 7);
          if ($customFieldID = CRM_Core_BAO_CustomField::getKeyID($name)) {
            if ($entityCaseID) {
              CRM_Core_BAO_CustomField::setProfileDefaults($customFieldID, $name, $defaults,
                $entityCaseID, CRM_Profile_Form::MODE_REGISTER
              );
            }
            if (!isset($defaults[$name])) {
              CRM_Core_BAO_CustomField::setProfileDefaults($customFieldID, $name, $defaults,
                $contactID, CRM_Profile_Form::MODE_REGISTER
              );
            }
            $htmlType = $field['html_type'];
            if ($htmlType == 'File') {
              $this->assign('customname',$name);
              $entityId = $entityCaseID;
              $url = CRM_Core_BAO_CustomField::getFileURL($entityId, $customFieldID);
              if ($url) {
                $customFiles[$field['name']]['displayURL'] = ts("Attached File") . ": {$url['file_url']}";
                $deleteExtra = ts("Are you sure you want to delete attached file?");
                $fileId      = $url['file_id'];
                $session = CRM_Core_Session::singleton();
                $session->pushUserContext(CRM_Utils_System::url('civicrm/vacancy/apply', "reset=1&id={$this->_id}&cid={$contactID}"));

                $deleteURL   = CRM_Utils_System::url('civicrm/file',
                               "reset=1&id={$fileId}&eid=$entityId&fid={$customFieldID}&action=delete"
                );
                $text = ts("Delete Attached File");
                $customFiles[$field['name']]['deleteURL'] = "<a href=\"{$deleteURL}\" onclick = \"if (confirm( ' $deleteExtra ' )) this.href+='&amp;confirmed=1'; else return false;\">$text</a>";
                $this->assign('customFiles',$customFiles);
              }
            }
          }
        }
        else {
          $fields[$name] = 1;
        }
      }
      CRM_Core_BAO_UFGroup::setProfileDefaults($contactID, $fields, $defaults);
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
    $VacancyResult = civicrm_api3('HRVacancy', 'get', array('id' => $this->_id));
    $this->_creatorID = $VacancyResult['values'][$this->_id]['created_id'];
    $position = $VacancyResult['values'][$this->_id]['position'];
    CRM_Utils_System::setTitle(ts('Apply for %1', array(1 => $position)));

    $applicationProfileFields = CRM_Core_BAO_UFGroup::getFields($this->_profileID, FALSE, NULL,
        NULL, NULL,
        FALSE, NULL,
        TRUE, NULL,
        CRM_Core_Permission::CREATE
    );

    $this->assign('fields', $applicationProfileFields);
    foreach ($applicationProfileFields as $name => $field) {
      CRM_Core_BAO_UFGroup::buildProfile($this, $field, CRM_Profile_Form::MODE_CREATE, NULL, FALSE, FALSE, NULL);
    }

    $this->addButtons(array(
        array(
          'type' => 'upload',
          'name' => ts('Apply'),
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
      )
    );
  }

  /**
   * Function to process the form
   *
   * @access public
   * @return void
   */
  public function postProcess() {
    $params = $this->controller->exportValues($this->_name);

    //Check the contact provided in Application form is existing or new
    $profileContactType = CRM_Core_BAO_UFGroup::getContactType($this->_profileID);
    $dedupeParams = CRM_Dedupe_Finder::formatParams($params, $profileContactType);
    $dedupeParams['check_permission'] = FALSE;
    $ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, $profileContactType);
    $applicantID = $this->_contactID;

    if(count($ids) && !$applicantID) {
      $applicantID = CRM_Utils_Array::value(0, $ids);
    }

    $applicantID = CRM_Contact_BAO_Contact::createProfileContact(
      $params, CRM_Core_DAO::$_nullArray,
      $applicantID, NULL,
      $this->_profileID
    );

    if ($applicantID) {
      $params['start_date'] = date("YmdHis");
      $dao = new CRM_HRRecruitment_DAO_HRVacancyStage();
      $dao->vacancy_id = $this->_id;
      $dao->find();
      while($dao->fetch()) {
        $params['case_status_id'] = $dao->case_status_id;
        break;
      }

      //Create case of type Application against creator applicant and assignee as Vacancy creator
      $caseTypes = array_flip(CRM_Case_PseudoConstant::caseType('name', TRUE, 'AND filter = 1'));
      $cases = CRM_Case_BAO_Case::retrieveCaseIdsByContactId($applicantID, FALSE, 'Application');
      foreach ($cases as $case) {
        $oldAppl = CRM_HRRecruitment_BAO_HRVacancy::getVacancyIDByCase($case);
        if($oldAppl == $this->_id) {
          $params['id'] = $case;
          break;
        }
      }

      $params['case_type_id'] = $caseTypes['Application'];
      $caseObj = CRM_Case_BAO_Case::create($params);
      if (empty($params['id'])) {
        $contactParams = array(
          'case_id' => $caseObj->id,
          'contact_id' => $applicantID,
        );
        if (is_callable('CRM_Case_BAO_Case::addCaseToContact')) {
            CRM_Case_BAO_Case::addCaseToContact($contactParams);
        } else {
            CRM_Case_BAO_CaseContact::create($contactParams);
        }

        $xmlProcessor = new CRM_Case_XMLProcessor_Process();
        $xmlProcessorParams = array(
          'clientID' => $applicantID,
          'creatorID' => $this->_creatorID,
          'standardTimeline' => 1,
          'activityTypeName' => 'Open Case',
          'caseID' => $caseObj->id,
          'activity_date_time' => $params['start_date'],
        );
        $xmlProcessor->run('Application', $xmlProcessorParams);
      }

      //process Custom data
      CRM_Core_BAO_CustomValueTable::postProcess($params, 'civicrm_case', $caseObj->id, 'Case');

      //Process case to vacancy one-to-one mapping in custom table 'application_case'
      $cgID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'application_case', 'id', 'name');
      $result = civicrm_api3('CustomField', 'get', array('custom_group_id' => $cgID, 'name' => 'vacancy_id'));
      civicrm_api3('custom_value' , 'create', array("custom_{$result['id']}" => $this->_id, 'entity_id' => $caseObj->id));
    }
    if ($this->controller->getButtonName('submit') == "_qf_Application_upload") {
      CRM_Core_Session::setStatus(ts("Application has been successfully submitted."));
      CRM_Core_Session::singleton()->pushUserContext(CRM_Utils_System::url('civicrm/vacancy/publiclisting', 'reset=1'));
    }
  }
}
