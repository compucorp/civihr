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
 * This class is to build the form for Deleting Particular Vacancy
 */
class CRM_HRRecruitment_Form_Search_Delete extends CRM_Core_Form {

  /**
   * page jobPosition
   *
   * @var string
   * @protected
   */
  protected $_jobPosition;

  /**
   * page id
   *
   * @var int
   * @protected
   */
  protected $_id;

  /**
   * Function to set variables up before form is built
   *
   * @return void
   * @access public
   */
  public function preProcess() {
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this, FALSE, 0, 'REQUEST');
    $this->_isTemplate = (boolean) CRM_Utils_Request::retrieve('template', 'Integer', $this);
    $this->_jobPosition = CRM_Core_DAO::getFieldValue('CRM_HRRecruitment_DAO_HRVacancy', $this->_id, 'position');
    parent::preProcess();
  }

  /**
   * Function to actually build the form
   *
   * @return void
   * @access public
   */
  public function buildQuickForm() {
    $this->_isTemplate = CRM_Core_DAO::getFieldValue('CRM_HRRecruitment_DAO_HRVacancy', $this->_id, 'is_template');
    $this->assign('isTemplate', $this->_isTemplate);

    CRM_Utils_System::setTitle(ts('Delete Vacancy'));
    $btnName = ts('Delete Vacancy');
    if ($this->_isTemplate) {
      CRM_Utils_System::setTitle(ts('Delete Vacancy Template'));
      $btnName = ts('Delete Vacancy Template');
    }
    $buttons = array(
      array(
        'type' => 'next',
        'name' => $btnName,
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    );
    $this->addButtons($buttons);
  }

  /**
   * Process the form when submitted
   *
   * @return void
   * @access public
   */
  public function postProcess() {
    $params = array(
      'sequential' => 1,
      'id' => $this->_id,
    );
    $result = civicrm_api3('HRVacancy', 'delete', $params);
    if (!empty($result)) {
      CRM_Core_Session::setStatus(ts("'%1' has been deleted.", array(1 => $this->_jobPosition)), ts('Vacancy Deleted'), 'success');
      if ($this->_isTemplate) {
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/vacancy/find', 'reset=1&template=1'));
      }
      else {
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/vacancy/find', 'reset=1'));
      }
    }
  }
}

