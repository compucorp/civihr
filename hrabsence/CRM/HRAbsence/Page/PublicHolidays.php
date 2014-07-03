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
 * Page for displaying list of public holidays
 */
class CRM_HRAbsence_Page_PublicHolidays extends CRM_Core_Page_Basic {
  /**
   * The action links that we need to display for the browse screen
   *
   * @var array
   * @static
   */
  static $_links = null;

  public $useLivePageJS = TRUE;

  /**
   * Get BAO Name
   *
   * @return string Classname of BAO.
   */
  function getBAOName() {
    return 'CRM_Activity_BAO_Activity';
  }

  /**
   * Get action Links
   *
   * @return array (reference) of action links
   */
  function &links() {
    if (!(self::$_links)) {
      self::$_links = array(
        CRM_Core_Action::UPDATE  => array(
          'name'  => ts('Edit'),
          'url'   => 'civicrm/absence/holidays',
          'qs'    => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Public Holiday'),
        ),
        CRM_Core_Action::DELETE  => array(
          'name'  => ts('Delete'),
          'url'   => 'civicrm/absence/holidays',
          'qs'    => 'action=delete&id=%%id%%',
          'title' => ts('Delete Public Holiday'),
        ),
      );
    }
    return self::$_links;
  }

  /**
   * Run the page.
   *
   * This method is called after the page is created. It checks for the
   * type of action and executes that action.
   * Finally it calls the parent's run method.
   *
   * @return void
   * @access public
   *
   */
  function run() {
    // get the requested action
    $action = CRM_Utils_Request::retrieve('action', 'String', $this, false, 'browse'); // default to 'browse'

    // assign vars to templates
    $this->assign('action', $action);
    $id = CRM_Utils_Request::retrieve('id', 'Positive', $this, false, 0);

    // what action to take ?
    if ($action & (CRM_Core_Action::UPDATE | CRM_Core_Action::ADD)) {
      $this->edit($action, $id);
    }

    // parent run
    return parent::run();
  }

  /**
   * Browse all Public Holidays
   *
   *
   * @return void
   * @access public
   * @static
   */
  function browse() {
    $publicHoliday = array();
    $dao = new CRM_Activity_DAO_Activity();
    $dao->activity_type_id = civicrm_api3('OptionValue', 'getvalue', array('name' => 'Public Holiday', 'return'=> 'value'));
    $dao->find();
    while ($dao->fetch()) {
      $publicHoliday[$dao->id] = array();
      $publicHoliday[$dao->id]['id'] = $dao->id;
      $publicHoliday[$dao->id]['subject'] = $dao->subject;
      if (array_search('Scheduled',  CRM_Core_PseudoConstant::activityStatus()) == $dao->status_id) {
        $publicHoliday[$dao->id]['status'] = 1;
      }
      else {
        $publicHoliday[$dao->id]['status'] = 0;
      }
      $publicHoliday[$dao->id]['date'] = $dao->activity_date_time;

      // form all action links
      $action = array_sum(array_keys($this->links()));
      $publicHoliday[$dao->id]['action'] = CRM_Core_Action::formLink(self::links(), $action,
        array('id' => $dao->id)
      );
    }

    $this->assign('rows', $publicHoliday);
  }

  /**
   * Get name of edit form
   *
   * @return string Classname of edit form.
   */
  function editForm() {
    return 'CRM_HRAbsence_Form_PublicHolidays';
  }

  /**
   * Get edit form name
   *
   * @return string name of this page.
   */
  function editName() {
    return 'Public Holidays';
  }

  /**
   * Get user context.
   *
   * @return string user context.
   */
  function userContext($mode = null) {
    return 'civicrm/absence/holidays';
  }
}
