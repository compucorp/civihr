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
 * Page for displaying list of vacancy
 */
class CRM_HRRecruitment_Page_SearchVacancy extends CRM_Core_Page {
  static $_actionLinks = NULL;
  static $_links = NULL;
  protected $_sortByCharacter;
  protected $_isTemplate = FALSE;
  protected $_force = NULL;

  /**
   * Get action Links
   *
   * @return array (reference) of action links
   */
  function &links() {
    if (!(self::$_actionLinks)) {
      // helper variable for nicer formatting
      $copyExtra = ts('Are you sure you want to make a copy of this Vacancy?');
      $deleteExtra = ts('Are you sure you want to delete this Vacancy?');

      self::$_actionLinks = array(
        CRM_Core_Action::UPDATE => array(
          'name' => ts('Edit'),
          'url' => 'civicrm/vacancy/add',
          'qs' => 'action=update&id=%%id%%',
          'title' => ts('Edit Vacancy'),
        ),
        CRM_Core_Action::DELETE => array(
          'name' => ts('Delete'),
          'url' => CRM_Utils_System::currentPath(),
          'qs' => 'action=delete&id=%%id%%',
          'extra' => 'onclick = "return confirm(\'' . $deleteExtra . '\');"',
          'title' => ts('Delete Vacancy'),
        ),
        CRM_Core_Action::COPY => array(
          'name' => ts('Copy'),
          'url' => CRM_Utils_System::currentPath(),
          'qs' => 'reset=1&action=copy&id=%%id%%',
          'extra' => 'onclick = "return confirm(\'' . $copyExtra . '\');"',
          'title' => ts('Copy Vacancy'),
        ),
      );
    }
    return self::$_actionLinks;
  }

  function run() {
    // get the requested action
    $action = CRM_Utils_Request::retrieve('action', 'String', $this, FALSE, 'browse');
    $searchParams = array();

    foreach(array('position', 'status_id', 'location') as $searchField) {
      $searchValue = $this->get($searchField);
      if (!empty($searchValue)) {
        $searchParams[$searchField] = $searchValue;
      }
    }
    // assign vars to templates
    $this->assign('action', $action);
    $id = CRM_Utils_Request::retrieve('id', 'Positive', $this, FALSE, 0, 'REQUEST');

    // what action to take ?
    if ($action & CRM_Core_Action::DELETE) {
      $session = CRM_Core_Session::singleton();
      $session->pushUserContext(CRM_Utils_System::url(CRM_Utils_System::currentPath(), 'reset=1&action=browse'));
      $controller = new CRM_Core_Controller_Simple('CRM_HRRecruitment_Form_Search_Delete',
        'Delete Vacancy',
        $action
      );
      $controller->set('id', $id);
      $controller->process();
      return $controller->run();
    }
    if ($action & CRM_Core_Action::UPDATE) {
      $session = CRM_Core_Session::singleton();
      $session->pushUserContext(CRM_Utils_System::url('civicrm/vacancy', 'reset=1&action=browse'));
      $controller = new CRM_Core_Controller_Simple('CRM_HRRecruitment_Form_HRVacancy',
        'Edit Vacancy',
        $action
      );
      $controller->set('id', $id);
      $controller->process();
      return $controller->run();
    }
    elseif ($action & CRM_Core_Action::COPY) {
      $this->copy();
    }

    // finally browse the custom groups
    $this->browse($action, $searchParams);

    // parent run
    return parent::run();
  }


  function browse($action = NULL, $searchParams = array()){
    $template = CRM_Utils_Request::retrieve('template', 'Positive', $this, FALSE, 0);
    $status = CRM_Utils_Request::retrieve('status', 'Positive', $this, FALSE, 0);

    if (empty($searchParams['status_id']) && $status) {
      $searchParams['status'] = $status;
    }

    if ($template) {
      $searchParams['is_template'] = 1;
    }
    else {
      $searchParams['is_template'] = 0;
    }

    $whereClause = $this->whereClause($searchParams);

    $this->_force = $this->_searchResult = NULL;
    $this->search();
    $params = array();
    $this->_force = CRM_Utils_Request::retrieve('force', 'Boolean',
      $this, FALSE
    );
    $this->search();

    $query = "SELECT *
    FROM civicrm_hrvacancy
    $whereClause";

    $dao = CRM_Core_DAO::executeQuery($query);
    $rows = array();

    $vacancyStatuses = CRM_Core_OptionGroup::values('vacancy_status', FALSE);
    $location = CRM_Core_OptionGroup::values('hrjob_location', FALSE);
    while ($dao->fetch()) {
      $rows[$dao->id]['position'] = $dao->position;
      $rows[$dao->id]['location'] = $location[$dao->location];
      $rows[$dao->id]['salary'] = $dao->salary;
      $rows[$dao->id]['start_date'] = $dao->start_date;
      $rows[$dao->id]['end_date'] = $dao->end_date;
      $hasPermission = CRM_HRRecruitment_BAO_HRVacancyPermission::checkVacancyPermission($dao->id, array('administer CiviCRM', 'administer Vacancy', 'Edit Vacancy'));

      if (empty($template)) {
        $rows[$dao->id]['status'] = $vacancyStatuses[$dao->status_id];
      }

      if ($hasPermission) {
        $actionLink = self::links();
        if ($template) {
          $actionLink[CRM_Core_Action::UPDATE]['title'] = ts('Edit Vacancy Template');
          $actionLink[CRM_Core_Action::COPY]['title'] = ts('Copy Vacancy Template');
          $actionLink[CRM_Core_Action::DELETE]['title'] = ts('Delete Vacancy Template');
          $actionLink[CRM_Core_Action::DELETE]['qs'] .= '&template=1';
        }
        $rows[$dao->id]['action'] = CRM_Core_Action::formLink($actionLink, array(), array('id' => $dao->id));
      }
    }

    $this->_isTemplate = (boolean) CRM_Utils_Request::retrieve('template', 'Integer', $this);
    if (isset($this->_isTemplate)) {
      $this->assign('isTemplate', $this->_isTemplate);
    }
    $this->assign('rows', $rows);
  }

  function whereClause($searchParams) {
    $clauses = array();
    $whereClause = "WHERE ";

    foreach ($searchParams as $column => $value) {
      switch ($column) {
        case 'is_template':
          $clauses[] = "$column = $value";
          break;
        case 'position':
          $clauses[] = "$column LIKE '%$value%'";
          break;
        case 'location':
          $clauses[] = "$column IN ('" . implode("','", array_keys($value)) . "')";
          break;
        case 'status_id':
          $clauses[] = "$column IN (" . implode(',', array_keys($value)) . ")";
          break;
        case 'status':
          $clauses[] = "status_id = $value";
          break;
      }
    }

    $whereClause .= implode(' AND ', $clauses );

    return $whereClause;
  }

  function search() {
    if (isset($this->_action) &
      (CRM_Core_Action::ADD |
        CRM_Core_Action::UPDATE |
        CRM_Core_Action::DELETE
      )
    ) {
      return;
    }

    $form = new CRM_Core_Controller_Simple('CRM_HRRecruitment_Form_Search', ts('Search Vacancy'), CRM_Core_Action::ADD);
    $form->setEmbedded(TRUE);
    $form->setParent($this);
    $form->process();
    $form->run();
  }

  /**
   * This function is to make a copy of a Vacancy, including
   * all the fields in the Vacancy wizard
   *
   * @return void
   * @access public
   */
  function copy() {
    $id = CRM_Utils_Request::retrieve('id', 'Positive', $this, TRUE, 0, 'GET');
    $urlString = 'civicrm/vacancy/find';
    $copyVacancy = CRM_HRRecruitment_BAO_HRVacancy::copy($id);
    $urlParams = 'reset=1';
    // Redirect to Copied HRVacancy Configuration
    if ($copyVacancy->id) {
      $urlString = 'civicrm/vacancy/add';
      $urlParams .= '&action=update&id=' . $copyVacancy->id;
    }
    return CRM_Utils_System::redirect(CRM_Utils_System::url($urlString, $urlParams));
  }
}


