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
    $this->_statusId = CRM_Utils_Request::retrieve('cStatus', 'Positive');

    $commentActivity = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Comment');
    $this->assign('commentActivity', $commentActivity);
    $activityStatus = CRM_Core_PseudoConstant::activityStatus();
    $activityStatsId = array_search('Completed', $activityStatus);
    $this->assign('activityStatsId', $activityStatsId);

    $emailActivity = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Email');
    $this->assign('emailActivity', $emailActivity);

    if ($this->_caseID = CRM_Utils_Request::retrieve('case_id', 'Positive')) {
      $this->assign('caseID', $this->_caseID);
      $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
      $gid = array_search('application_case', $groups);
      $cgID = array('custom_group_id'=>$gid);
      CRM_Core_BAO_CustomField::retrieve($cgID, $cfID);
      $params = array(
        "entityID" => $this->_caseID,
        "custom_{$cfID['id']}" => 1,
      );
      $result = CRM_Core_BAO_CustomValueTable::getValues($params);
      $this->_vacancyID = $vacancyID = $result["custom_{$cfID['id']}"];
      $ufJoinParams = array(
        'module' => 'Vacancy',
        'entity_id' => $vacancyID,
        'module_data' => 'application_profile',
      );
      $ufJoin = new CRM_Core_DAO_UFJoin();
      $ufJoin->copyValues($ufJoinParams);
      $ufJoin->find(TRUE);
      $this->_profileID = $ufJoin->uf_group_id;
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
    if (isset($this->_defStatus)) {
      $this->_defaults['stages'] = $this->_defStatus;
    }
    return $this->_defaults;
  }

  public function buildQuickForm() {
    //Add Change case status
    $this->_statuses = CRM_HRRecruitment_BAO_HRVacancyStage::caseStage($this->_vacancyID);
    $this->_defStatus = null;
    $weight = $this->_statuses[$this->_statusId];
    $statusChangeID = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Change Case Status');
    foreach ($this->_statuses as $csID => $csV) {
      $url = CRM_Utils_System::url('civicrm/case/activity',
        "action=add&reset=1&cid={$this->_contactID}&caseid={$this->_caseID}&atype={$statusChangeID}&cStatus={$csID}",
        FALSE, NULL, FALSE
      );
      $stageOption[$url] = $csV['title'];
      if( $csV['weight'] > $weight['weight'] && !isset($this->_defStatus) ) {
        $this->_defStatus = $url;
      }
    }
    $this->add('select', 'stages', '',
      array('' => ts(" - Change status - ")) + $stageOption,
      FALSE, array(
        'class' => 'crm-select2 crm-action-menu',
      )
    );

    //Add new activity - attach letter
    $activityLetterId = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Attach Letter');
    $attachLetter = CRM_Utils_System::url('civicrm/case/activity',
        "action=add&reset=1&cid={$this->_contactID}&caseid={$this->_caseID}&atype={$activityLetterId}",
        FALSE, NULL, FALSE
      );
    $newActivity[$attachLetter] = ts("Attach Letter");

    $this->add('select', 'new_activity', '',
      array('' => ts(" - New Activity - ")) + $newActivity,
      FALSE, array(
        'class' => 'crm-select2 crm-action-menu',
      )
    );

    //Evaluation ID
    $evaluationID = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Evaluation');
    $this->assign('evaluationID', $evaluationID);

    //Add application profile
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