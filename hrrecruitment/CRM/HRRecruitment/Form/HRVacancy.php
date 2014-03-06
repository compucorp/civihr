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

  function setDefaultValues() {
    $defaults = array();
    if ($tempId = (int) CRM_Utils_Request::retrieve('template_id', 'Integer', $this)) {
      $result = civicrm_api3('HRVacancy', 'get', array('id' => $tempId));
      $defaults = $result['values'][$tempId];

      //format vacancy start/end date
      list($defaults['start_date'], $defaults['start_date_time']) = CRM_Utils_Date::setDateDefaults($defaults['start_date'], 'activityDateTime');
      list($defaults['end_date'], $defaults['end_date_time']) = CRM_Utils_Date::setDateDefaults($defaults['end_date'], 'activityDateTime');
      return $defaults;
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
    $attributes = CRM_Core_DAO::getAttribute('CRM_HRRecruitment_DAO_HRVacancy');

    $result = civicrm_api3('HRVacancy', 'get', array('is_template' => 1, 'return' => 'position'));
    $templates  = array();
    foreach ($result['values'] as $id => $vacancy) {
      $templates[$id] = $vacancy['position'];
    }
    $this->add('select', 'template_id', ts('From Template'), array('' => ts('- select -')) + $templates, FALSE, array('class' => 'crm-select2 huge'));
    $this->add('text', 'position', ts('Job Position'), $attributes['position'], TRUE);
    $this->add('select', 'location', ts('Location'), array('' => ts('- select -')), FALSE, array('class' => 'crm-select2 huge'));
    $this->add('text', 'salary', ts('Salary'), $attributes['salary']);
    $this->addWysiwyg('description', ts('Description'), array('rows' => 2, 'cols' => 40));
    $this->addWysiwyg('benefits', ts('Benefits'), array('rows' => 2, 'cols' => 40));
    $this->addWysiwyg('requirements', ts('Requirements'),  array('rows' => 2, 'cols' => 40));
    $this->addDateTime('start_date', ts('Start Date'), FALSE, array('formatType' => 'activityDateTime'));
    $this->addDateTime('end_date', ts('End Date'), FALSE, array('formatType' => 'activityDateTime'));
    $this->addSelect('status_id', array(), TRUE);

    $allowCoreTypes = array_merge(array('Individual'), CRM_Contact_BAO_ContactType::subTypes('Individual'));
    $entities = array(
      array(
        'entity_name' => 'contact_1',
        'entity_type' => 'IndividualModel',
      ),
    );
    $allowSubTypes = array();
    $this->addProfileSelector('application_profile', '', $allowCoreTypes, $allowSubTypes, $entities);
    $this->addProfileSelector('evaluation_profile', '', $allowCoreTypes, $allowSubTypes, $entities);

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
  }

  /**
   * Function to process the form
   *
   * @access public
   * @return void
   */
  public function postProcess() {
  }
}
