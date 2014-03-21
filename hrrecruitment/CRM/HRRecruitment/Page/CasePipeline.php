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

  function preProcess() {
    // process url params
    if ($this->_vid = CRM_Utils_Request::retrieve('vid', 'Positive', $this)) {
      $this->assign('vid', $this->_vid);
    }
    else {
      CRM_Core_Error::fatal(ts('There is no vacancy information provided'));
    }
  }

  function run() {
    $this->preProcess();

    $this->view();

    return parent::run();
  }

  function view() {
    // Add js for tabs
    CRM_Core_Resources::singleton()
      ->addScriptFile('civicrm', 'packages/jquery/plugins/jstree/jquery.jstree.js', 0, 'html-header', FALSE)
      ->addStyleFile('civicrm', 'packages/jquery/plugins/jstree/themes/default/style.css', 0, 'html-header')
      ->addScriptFile('civicrm', 'templates/CRM/common/TabHeader.js');

    //Change page title to designate against which position you are viewing this page
    $position = CRM_Core_DAO::getFieldValue('CRM_HRRecruitment_DAO_HRVacancy', $this->_vid, 'position');
    CRM_Utils_System::setTitle(ts('%1 : %2', array(1 => $this->_title, 2 => $position)));

    $vacancyStages = CRM_HRRecruitment_BAO_HRVacancyStage::caseStage($this->_vid);
    $allTabs = array();
    $current = TRUE;
    foreach ($vacancyStages as $key => $vacancyStage) {
      $allTabs[$key] = array(
        'title' => $vacancyStage['title'],
        'link' => NULL,
        'weight' => $vacancyStage['weight'],
        'count' => $vacancyStage['count'],
        'active' => TRUE,
        'valid' => $vacancyStage['valid'],
      );
      if ($current) {
        $allTabs[$key]['current'] = $current;
        $current = FALSE;
      }
    }

    $this->assign('tabHeader', $allTabs);
  }
}

