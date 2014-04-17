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
 * Form for Applicant profile View.
 *
 */
class CRM_HRRecruitment_Form_CaseProfile extends CRM_Case_Form_CaseView {

  /**
   * the id of the case
   *
   * @int
   * @access protected
   */
  protected $_caseID;

  /**
   * the id of the contact
   *
   * @int
   * @access protected
   */
  protected $_contactID;

  /**
   * the id of the application profile
   *
   * @int
   * @access protected
   */
  protected $_profileID;

  /**
   * the fields needed to build this form
   *
   * @var array
   */
  public $_fields;

  public function preProcess() {
    // process url params
    if ($this->_contactID = CRM_Utils_Request::retrieve('cid', 'Positive')) {
      $this->assign('contactID', $this->_contactID);
    }
    if ($this->_caseID = CRM_Utils_Request::retrieve('case_id', 'Positive')) {
      $this->assign('case_id', $this->_caseID);
      $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
      $gid = array_search('application_case', $groups);
      $cgID = array('custom_group_id'=>$gid);
      CRM_Core_BAO_CustomField::retrieve($cgID, $cfID);
      $vacancyID = CRM_HRRecruitment_BAO_HRVacancy::getVacancyIDByCase($this->_caseID);
      foreach (array('application_profile', 'evaluation_profile') as $profileName) {
        $dao = new CRM_Core_DAO_UFJoin;
        $dao->module = 'Vacancy';
        $dao->entity_id = $vacancyID;
        $dao->module_data = $profileName;
        $dao->find(TRUE);
        $profile[$profileName] = $dao->uf_group_id;
      }
      //Evaluation ID
      $evaluationID = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Evaluation');
      $this->assign('evaluationID', $evaluationID);


      $params = array(
        'activity_type_id' => $evaluationID,
      );

      $url = CRM_Utils_System::url('civicrm/case/activity',
        "action=add&reset=1&cid={$this->_contactID}&caseid={$this->_caseID}&atype={$evaluationID}",
        FALSE, NULL, FALSE
      );

      $caseActivity = CRM_Case_BAO_Case::getCaseActivity($this->_caseID, $params, $this->_contactID);
      foreach ($caseActivity as $caseActivity) {
        $evalID = $caseActivity['id'];
        $url = CRM_Utils_System::url('civicrm/case/activity',
          "action=update&reset=1&cid={$this->_contactID}&caseid={$this->_caseID}&atype={$evaluationID}&id={$evalID}",
          FALSE, NULL, FALSE
        );
      }
      $this->assign('evalURL', $url);
      $this->_profileID = $profile['application_profile'];
      $this->_evalProfileID = $profile['evaluation_profile'];
      $this->assign('evalProfileID', $this->_evalProfileID);
    }
  }

  function setDefaultValues() {
    $profileFields = CRM_Core_BAO_UFGroup::getFields($this->_profileID);
    $contactID = $this->_contactID;
    if ($contactID) {
      $options = array();
      $fields = array();

      if (!empty($this->_fields)) {
        foreach ($this->_fields as $name => $dontCare) {
          if (substr($name, 0, 7) == 'custom_') {
            $id = substr($name, 7);
            continue;
          }
          $fields[$name] = 1;
        }
      }
    }
    if (!empty($fields)) {
      CRM_Core_BAO_UFGroup::setProfileDefaults($contactID, $fields, $this->_defaults);
    }
    //set custom field defaults
    if (!empty($this->_fields)) {
      foreach ($this->_fields as $name => $field) {
        if ($customFieldID = CRM_Core_BAO_CustomField::getKeyID($name)) {
          if (!isset($this->_defaults[$name])) {
            CRM_Core_BAO_CustomField::setProfileDefaults($customFieldID, $name, $this->_defaults,
              $contactID, CRM_Profile_Form::MODE_REGISTER
            );
          }
        }
      }
    }
    return $this->_defaults;
  }

  public function buildQuickForm() {
    $profileFields = CRM_Core_BAO_UFGroup::getFields($this->_profileID);
    foreach ($profileFields as $profileFieldKey => $profileFieldVal) {
      CRM_Core_BAO_UFGroup::buildProfile($this, $profileFields[$profileFieldKey], CRM_Profile_Form::MODE_EDIT, $this->_contactID, TRUE);
      $this->_fields[$profileFieldKey] = $profileFields[$profileFieldKey];
      $this->freeze($profileFieldKey);
      if ($profileFields[$profileFieldKey]['field_type'] == 'Case') {
        unset($profileFields[$profileFieldKey]);
        unset($this->_fields[$profileFieldKey]);
      }
    }
    $this->assign('profileFields', $profileFields);

    $controller = new CRM_Core_Controller_Simple(
      'CRM_Case_Form_CaseView',
      'View Case',
      CRM_Core_Action::VIEW,
      FALSE,
      FALSE,
      TRUE
    );
    $controller->setEmbedded(TRUE);
    $controller->set('id', $this->_caseID);
    $controller->set('cid', $this->_contactID);
    $controller->run();
    $this->_caseID = CRM_Utils_Request::retrieve('case_id', 'Positive');
    CRM_Case_Form_CaseView::activityForm($this);
  }
}