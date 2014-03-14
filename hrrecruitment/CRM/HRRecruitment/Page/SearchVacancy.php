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
    $this->browse();

    // parent run
    return parent::run();
  }

  function browse($action = NULL){
    $params = array();
    $whereClause = $this->whereClause($params, TRUE, $this->_force);
    $this->_force = $this->_searchResult = NULL;
    $this->search();
    $params = array();
    $this->_force = CRM_Utils_Request::retrieve('force', 'Boolean',
                                                $this, FALSE
                                                );
    $this->search(); 
    $query = "SELECT *
    FROM civicrm_hrvacancy
    WHERE  $whereClause";

    $permissions = array(CRM_Core_Permission::VIEW);
    if (CRM_Core_Permission::check('edit contributions')) {
      $permissions[] = CRM_Core_Permission::EDIT;
    }
    if (CRM_Core_Permission::check('delete in CiviContribute')) {
      $permissions[] = CRM_Core_Permission::DELETE;
    }
    $mask = CRM_Core_Action::mask($permissions);
    $dao = CRM_Core_DAO::executeQuery($query,$params, TRUE, 'CRM_HRRecruitment_DAO_HRVacancy');
    $rowsVacancy =array();

    while ($dao->fetch()) {
      $row = array();
      $vacanciesId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'vacancy_status', 'id', 'name');
      $paramsVacancy = array(
        'sequential' => 1,
        'option_group_id' => $vacanciesId,
        'value' => $dao->status_id,
      );
      $result = civicrm_api3('OptionValue', 'get', $paramsVacancy);
      if (isset($result['values'][0]['label'])) {
        $row['status_label'] =  $result['values'][0]['label'];
      }
      $row['status_id'] = $dao->status_id;
      $row['id'] = $dao->id;
      $row['position'] = $dao->position;
      $row['location'] = $dao->location;
      $row['salary'] = $dao->salary;
      $row['startdate'] = $dao->start_date;
      $row['enddate'] =$dao->end_date;
      $row['action'] = CRM_Core_Action::formLink(self::links(), $mask,array('id' => $dao->id));
      $rowsVacancy[] = $row;
    }
    $this->assign('rows', $rowsVacancy);
  }

  function whereClause() {
    $values    = array();
    $clauses   = array();
    $title     = $this->get('job_position');

    if ($title) {
      $clauses[] = "position LIKE '%".$title."%'";
    }
    $value = $this->get('status_type_id');
    $val = array();
    if ($value) {
      if (is_array($value)) {
        foreach ($value as $k => $v) {
          if ($v) {
            $val[$k] = $k;
          }
        }
        $type = implode(',', $val);
      }
      $clauses[] = "status_id IN ({$type})";
    }
    $location= $this->get('location_type_id');
    $val = array();
    if ($location) {
      if (is_array($location)) {
        foreach ($location as $k => $v) {
          if ($v) {
            $val[$k] = "'".$k."'";
          }
        }
        $type = implode(',', $val);
      }
      $clauses[] = "location IN ({$type})";
    }
    return !empty($clauses) ? implode(' AND ', $clauses) : '(1)';
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
    $urlString = 'civicrm/vacancy/search';
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


