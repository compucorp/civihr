<?php

require_once 'CRM/Core/Page.php';

class CRM_Hrjobcontract_Page_HoursLocation extends CRM_Core_Page_Basic {
  /**
   * The action links that we need to display for the browse screen
   *
   * @var array
   * @static
   */
  static $_links = null;
  
  /**
   * Get BAO Name
   *
   * @return string Classname of BAO.
   */
  function getBAOName() {
    return 'CRM_Hrjobcontract_BAO_HoursLocation';
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
          'url'   => 'civicrm/hours_location',
          'qs'    => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Hours Location'),
        ),
        CRM_Core_Action::DISABLE => array(
          'name'  => ts('Disable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Disable Hours Location'),
        ),
        CRM_Core_Action::ENABLE  => array(
          'name'  => ts('Enable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Enable Hours Location'),
        ),
        CRM_Core_Action::DELETE  => array(
          'name'  => ts('Delete'),
          'url'   => 'civicrm/hours_location',
          'qs'    => 'action=delete&id=%%id%%',
          'title' => ts('Delete Hours Location'),
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
    CRM_Utils_System::setTitle(ts('Hours Location'));
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
   * Browse all absence types
   *
   *
   * @return void
   * @access public
   * @static
   */
  function browse() {
    // get all hours location sorted by id
    $hoursLocation = array();
    $dao = new CRM_Hrjobcontract_DAO_HoursLocation();
    $dao->orderBy('id');
    $dao->find();

    while ($dao->fetch()) {
      $hoursLocation[$dao->id] = array();
      $hoursLocation[$dao->id]['id'] = $dao->id;
      $hoursLocation[$dao->id]['location'] = $dao->location;
      $hoursLocation[$dao->id]['standard_hours'] = $dao->standard_hours;
      $hoursLocation[$dao->id]['periodicity'] = $dao->periodicity;
      $hoursLocation[$dao->id]['is_active'] = $dao->is_active;

      // form all action links
      $action = array_sum(array_keys($this->links()));


      if ($dao->is_active) {
        $action -= CRM_Core_Action::ENABLE;
      }
      else {
        $action -= CRM_Core_Action::DISABLE;
      }

      //if this absence type has its related activities/leaves then don't show DELETE action
      $isDelete = FALSE;
      /*if ($dao->debit_activity_type_id) {
        $result = civicrm_api3('Activity', 'get', array('activity_type_id' => $dao->debit_activity_type_id));
        if (count($result['values'])) {
          $isDelete = TRUE;
        }
      }
      if (!$isDelete && $dao->credit_activity_type_id) {
        $result = civicrm_api3('Activity', 'get', array('activity_type_id' => $dao->credit_activity_type_id));
        if (count($result['values'])) {
          $isDelete = TRUE;
        }
      }*/

      if ($isDelete) {
        $action -= CRM_Core_Action::DELETE;
      }

      $hoursLocation[$dao->id]['action'] = CRM_Core_Action::formLink(self::links(), $action,
        array('id' => $dao->id)
      );
    }

    $this->assign('rows', $hoursLocation);
  }

  /**
   * Get name of edit form
   *
   * @return string Classname of edit form.
   */
  function editForm() {
    return 'CRM_Hrjobcontract_Form_HoursLocation';
  }

  /**
   * Get edit form name
   *
   * @return string name of this page.
   */
  function editName() {
    return 'Hours Location';
  }

  /**
   * Get user context.
   *
   * @return string user context.
   */
  function userContext($mode = null) {
    return 'civicrm/hours_location';
  }
}
