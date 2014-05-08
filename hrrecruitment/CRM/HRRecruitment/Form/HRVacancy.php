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
 * This class generates form components for Add/Edit Vacancy
 *
 */
class CRM_HRRecruitment_Form_HRVacancy extends CRM_Core_Form {

/**
   * Function to set variables up before form is built
   *
   * @return void
   * @access public
   */
  function preProcess() {
    $this->_isTemplate = (boolean) CRM_Utils_Request::retrieve('template', 'Integer', $this);
    $this->_id = CRM_Utils_Request::retrieve('id', 'Integer', $this);
    if ($this->_isTemplate) {
      CRM_Utils_System::setTitle(ts('New Vacancy Template'));
    }
    if ($this->_id) {
      if ($this->_isTemplate = CRM_Core_DAO::getFieldValue('CRM_HRRecruitment_DAO_HRVacancy', $this->_id, 'is_template')) {
        CRM_Utils_System::setTitle(ts('Edit Vacancy Template'));
      }
      else {
        CRM_Utils_System::setTitle(ts('Edit Vacancy'));
      }
    }
    $this->assign('isTemplate', $this->_isTemplate);

    $session = CRM_Core_Session::singleton();
    if ($this->_id) {
      $permission = CRM_HRRecruitment_BAO_HRVacancyPermission::checkVacancyPermission($this->_id,array("administer Vacancy","administer CiviCRM"));
    }
    else {
      $permission = CRM_Core_Permission::checkAnyPerm(array("administer Vacancy","administer CiviCRM"));
    }
    if (!$permission) {
      $session->pushUserContext(CRM_Utils_System::url('civicrm'));
      CRM_Core_Error::statusBounce(ts('You do not have the necessary permission to perform this action.'));
    }
  }

 /**
   * This function sets the default values for the form. For add/edit mode
   * the default values are retrieved from the database
   *
   * @access public
   *
   * @return void
   */
  function setDefaultValues() {
    $defaults = array();

    if ($this->_id) {
      $params['id'] = $this->_id;
    }
    else {
      $defaults['template_id'] = $params['id'] = CRM_Utils_Request::retrieve('template_id', 'Integer', $this);
    }

    if (!empty($params['id'])) {
      CRM_HRRecruitment_BAO_HRVacancy::retrieve($params, $defaults);
      //format vacancy start/end date
      list($defaults['start_date'], $defaults['start_date_time']) = CRM_Utils_Date::setDateDefaults($defaults['start_date'], 'activityDateTime');
      list($defaults['end_date'], $defaults['end_date_time']) = CRM_Utils_Date::setDateDefaults($defaults['end_date'], 'activityDateTime');

      //show that only number of permission row(s) which have defaults if any
      if (!empty($defaults['permission']) && count($defaults['permission'])) {
        $this->assign('showPermissionRow', count($defaults['permission']));
      }

      return $defaults;
    }

    foreach (array('application_profile', 'evaluation_profile') as $profileName) {
      if ($ufGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', $profileName, 'id', 'name')) {
        $defaults[$profileName] = $ufGroupID;
      }
    }
    $defaults['status_id'] = CRM_Core_OptionGroup::getDefaultValue('vacancy_status');

    return $defaults;
  }

  /**
   * Function to build the form
   *
   * @return void
   * @access public
   */
  function buildQuickForm() {
    $attributes = CRM_Core_DAO::getAttribute('CRM_HRRecruitment_DAO_HRVacancy');

    $this->add('text', 'position', ts('Job Position'), $attributes['position'], TRUE);
    $this->addSelect('location', array('label' => ts('Location'), 'entity' => 'HRJob', 'field' => 'location'));
    $this->add('text', 'salary', ts('Salary'), $attributes['salary']);

    $this->addWysiwyg('description', ts('Description'), array('rows' => 2, 'cols' => 40));
    $this->addWysiwyg('benefits', ts('Benefits'), array('rows' => 2, 'cols' => 40));
    $this->addWysiwyg('requirements', ts('Requirements'), array('rows' => 2, 'cols' => 40));

    $this->addDateTime('start_date', ts('Start Date'), FALSE, array('formatType' => 'activityDateTime'));
    $this->addDateTime('end_date', ts('End Date'), FALSE, array('formatType' => 'activityDateTime'));

    $include = & $this->addElement('advmultiselect', 'stages',
      '', CRM_Core_OptionGroup::values('case_status', FALSE, FALSE, FALSE, " AND grouping = 'Vacancy'"),
      array(
        'size' => 5,
        'style' => 'width:150px',
        'class' => 'advmultiselect',
      )
    );
    $include->setButtonAttributes('add', array('value' => ts('Enable >>')));
    $include->setButtonAttributes('remove', array('value' => ts('<< Disable')));

    $templates = $vacancyPermissions = array();
    if (!$this->_isTemplate) {
      $this->addSelect('status_id', array(), TRUE);
      $result = civicrm_api3('HRVacancy', 'get', array('is_template' => 1, 'return' => 'position'));
      foreach ($result['values'] as $id => $vacancy) {
        $templates[$id] = $vacancy['position'];
      }

      //hide 'From Template' on edit screen
      if (empty($this->_id)) {
        $this->add('select', 'template_id', ts('From Template'), array('' => ts('- select -')) + $templates, FALSE, array('class' => 'crm-select2 huge'));
      }
    }

    $evalEntity[] = array('entity_name' => 'activity_1', 'entity_type' => 'ActivityModel');
    $appEntities = array();
    $appEntities[] = array('entity_name' => 'contact_1', 'entity_type' => 'IndividualModel');
    $appEntities[] = array('entity_name' => 'case_1', 'entity_type' => 'CaseModel');

    $caseTypes = CRM_Case_PseudoConstant::caseType('title', TRUE, 'AND filter = 1');
    $caseTypes = array_keys($caseTypes);
    $this->addProfileSelector('application_profile', '', array('Individual', 'Contact', 'Case'), array('CaseType' => $caseTypes), $appEntities);
    $this->addProfileSelector('evaluation_profile', '', array('Activity'), array(), $evalEntity);

    $permissionClass = new CRM_Core_Permission_Base;
    $permissions = $permissionClass->getAllModulePermissions();
    foreach (array('view Applicants', 'manage Applicants', 'evaluate Applicants', 'administer Vacancy') as $permission) {
      $explodedPerms = explode(':', $permissions[$permission]);
      $vacancyPermissions[$permission] = array_pop($explodedPerms);
    }

    $rowCount = 5;
    for ($rowNumber = 1; $rowNumber <= $rowCount; $rowNumber++) {
      $this->add(
        'select', "permission[{$rowNumber}]",
        '', array('' => ts('- select -')) + $vacancyPermissions,
        FALSE, array('class' => 'crm-select2 huge')
      );
      $this->addEntityRef("permission_contact_id[{$rowNumber}]", NULL, array('api' => array('params' => array('contact_type' => 'Individual'))));
    }
    $this->assign('rowCount', $rowCount);
    $this->assign('showPermissionRow', 1);

    $this->addButtons(
      array(
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

    $session = CRM_Core_Session::singleton();
    if ($this->_isTemplate) {
      $this->_cancelURL = CRM_Utils_System::url('civicrm/vacancy/find',
        'reset=1&template=1'
      );
    }
    else {
      $this->_cancelURL = CRM_Utils_System::url('civicrm/vacancy/find',
        'reset=1'
      );
    }

    $this->addFormRule(array('CRM_HRRecruitment_Form_HRVacancy', 'formRule'));
    $session->replaceUserContext($this->_cancelURL);
  }

  /**
   * global validation rules for the form
   *
   * @param array $fields posted values of the form
   *
   * @return array list of errors to be posted back to the form
   * @static
   * @access public
   */
  static function formRule($fields, $files, $self) {
    $errors = array();
    if (empty($fields['stages'])) {
      $errors['stages'] = ts('Please select at least one Vacancy stage');
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
    $params = $this->exportValues();

    if ($this->_id) {
      $params['id'] = $this->_id;
    }

    $params['is_template'] = $this->_isTemplate;
    CRM_HRRecruitment_BAO_HRVacancy::create($params);

    if ($this->controller->getButtonName('submit') == "_qf_HRVacancy_next") {
      $urlParams = "reset=1";
      if ($this->_isTemplate) {
        $urlParams .= "&template=$this->_isTemplate";
      }
      CRM_Core_Session::singleton()->pushUserContext(CRM_Utils_System::url('civicrm/vacancy/find', $urlParams));
    }
  }
}
