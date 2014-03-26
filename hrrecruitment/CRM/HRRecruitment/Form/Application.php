<?php

/*
  +--------------------------------------------------------------------+
  | CiviHR version 1.3                                                 |
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
    $applicantID = NULL;

    if(count($ids)) {
        $applicantID = CRM_Utils_Array::value(0, $ids);
    }
    $applicantID = CRM_Contact_BAO_Contact::createProfileContact(
        $params, CRM_Core_DAO::$_nullArray,
        $applicantID, NULL,
        $this->_profileID
      );

    $params['start_date'] = date("Ymd");
    $dao = new CRM_HRRecruitment_DAO_HRVacancyStage();
    $dao->vacancy_id = $this->_id;
    $dao->find();
    while($dao->fetch()) {
      $params['case_status_id'] = $dao->case_status_id;
      break;
    }

    //Create case of type Application against creator applicant and assignee as Vacancy creator
    $caseTypes = array_flip(CRM_Case_PseudoConstant::caseType('name'));
    $params['case_type_id'] = $caseTypes['Application'];
    $caseObj = CRM_Case_BAO_Case::create($params);

    $contactParams = array(
        'case_id' => $caseObj->id,
        'contact_id' => $applicantID,
      );
    CRM_Case_BAO_Case::addCaseToContact($contactParams);

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

    //process Custom data
    CRM_Core_BAO_CustomValueTable::postprocess(&$params,CRM_Core_DAO::$_nullArray, 'civicrm_case', $caseObj->id, 'Case');

    //Process case to vacancy one-to-one mapping in custom table 'application_case'
    $cgID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'application_case', 'id', 'name');
    $result = civicrm_api3('CustomField', 'get', array('custom_group_id' => $cgID, 'name' => 'vacancy_id'));
    civicrm_api3('custom_value' , 'create', array("custom_{$result['id']}" => $this->_id, 'entity_id' => $caseObj->id));
  }
}
