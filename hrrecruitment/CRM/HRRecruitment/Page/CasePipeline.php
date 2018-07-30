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
 * Main page for Case pipeline View.
 *
 */
class CRM_HRRecruitment_Page_CasePipeline extends CRM_Core_Page {

  /**
   * the id of the vacancy
   *
   * @int
   * @access protected
   */
  protected $_vid;

  /**
   * @var int
   */
  protected $_statusId;

  function preProcess() {
    // process url params
    if ($this->_vid = CRM_Utils_Request::retrieve('vid', 'Positive')) {
      $this->assign('vid', $this->_vid);
    }
    else {
      CRM_Core_Error::fatal(ts('There is no vacancy information provided'));
    }
    $this->_statusId = CRM_Utils_Request::retrieve('status_id', 'Positive');
    $administerper = CRM_HRRecruitment_BAO_HRVacancyPermission::checkVacancyPermission($this->_vid,array("administer Vacancy","administer CiviCRM","manage Applicants"));
    $evaluateper = CRM_HRRecruitment_BAO_HRVacancyPermission::checkVacancyPermission($this->_vid,array("administer Vacancy","administer CiviCRM","evaluate Applicants"));
    $viewper = CRM_HRRecruitment_BAO_HRVacancyPermission::checkVacancyPermission($this->_vid,array("view Applicants"));

    $this->assign('administerper',$administerper);
    $this->assign('evaluateper',$evaluateper);
    if (!($administerper || $evaluateper || $viewper)) {
      CRM_Core_Session::singleton()->pushUserContext(CRM_Utils_System::url('civicrm'));
      CRM_Core_Error::statusBounce(ts('You do not have the necessary permission to perform this action.'));
      return;
    }
  }

  function run() {
    $this->preProcess();

    if (!$this->_statusId) {
      $this->topTabs();
    }
    else {
      $this->viewStage();
    }

    return parent::run();
  }

  /**
   * View the header with a tab per stage
   */
  function topTabs() {
    CRM_Core_Resources::singleton()
      ->addScriptFile('civicrm', 'templates/CRM/common/TabHeader.js')
      ->addStyleFile('org.civicrm.hrrecruitment', 'css/casePipeline.css')
      ->addScriptFile('org.civicrm.hrrecruitment', 'templates/CRM/HRRecruitment/Page/CasePipeline.js');

    //Change page title to designate against which position you are viewing this page
    $position = CRM_Core_DAO::getFieldValue('CRM_HRRecruitment_DAO_HRVacancy', $this->_vid, 'position');
    $link = CRM_Utils_System::url('civicrm/vacancy/add', array('reset' => 1, 'id' => $this->_vid));
    $title = "<a href={$link}>{$position}</a>";
    CRM_Utils_System::setTitle(ts('%1: %2', array(1 => $this->_title, 2 => $title)));

    $vacancyStages = CRM_HRRecruitment_BAO_HRVacancyStage::caseStage($this->_vid);
    foreach ($vacancyStages as $key => &$stage) {
      $stage['active'] = $stage['valid'] = TRUE;
      $stage['link'] = CRM_Utils_System::url('civicrm/case/pipeline', array('reset' => 1, 'status_id' => $key, 'vid' => $this->_vid));
      $stage['extra'] = "data-status_id='$key'";
      $stage['class'] = "livePage crm-count-{$stage['count']}";

      if (CRM_Utils_Request::retrieve('default_status_id', 'Positive') == $key) {
      CRM_Core_Resources::singleton()
        ->addSetting(array('tabSettings' => array(
          'active' => $key),
        ));
      }
    }

    $this->assign('tabHeader', $vacancyStages);
  }

  /**
   * View a particular stage in a tab
   */
  function viewStage() {
    // Data needed for contact list
    $contacts = CRM_HRRecruitment_BAO_HRVacancyStage::getCasesAtStage($this->_vid, $this->_statusId);
    $this->assign('contacts', $contacts);
    $this->ajaxResponse['tabCount'] = count($contacts);

    // Data needed for activity buttons/menu
    $xmlProcessor = new CRM_Case_XMLProcessor_Process();
    $activities = $xmlProcessor->get('Application', 'ActivityTypes', TRUE);

    // Special activities we don't want in the action menu
    foreach (array('Open Case', 'Comment', 'Email', 'Evaluation', 'Change Case Status', 'Link Cases', 'Assign Case Role') as $remove) {
      $type = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', $remove);
      if ($type) {
        // Assign to the tpl as camelCase
        $this->assign(str_replace(' ', '', lcfirst($remove . 'Activity')), $type);
        // And remove from the list
        unset($activities[$type]);
      }
    }
    $this->assign('activities', $activities);

    // Data needed for status menu
    $this->assign('statusId', $this->_statusId);
    $caseStatus = array();
    foreach(CRM_HRRecruitment_BAO_HRVacancyStage::caseStage($this->_vid) as $id => $stage) {
      $caseStatus[$stage['weight']] = $stage + array('id' => $id);
    }
    ksort($caseStatus);
    $this->assign('caseStatus', $caseStatus);
  }

  /**
   * Returns approprate template file if we are viewing the main page or a tab
   * @return string
   */
  function getTemplateFileName() {
    return $this->_statusId ? "CRM/HRRecruitment/Page/HRVacancyStage.tpl" : "CRM/common/TabHeader.tpl";
  }
}
