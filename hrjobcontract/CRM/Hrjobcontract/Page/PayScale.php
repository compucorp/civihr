<?php

require_once 'CRM/Core/Page.php';

class CRM_Hrjobcontract_Page_PayScale extends CRM_Core_Page_Basic {
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
    return 'CRM_Hrjobcontract_BAO_PayScale';
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
          'url'   => 'civicrm/pay_scale',
          'qs'    => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Pay Scale'),
        ),
        CRM_Core_Action::DISABLE => array(
          'name'  => ts('Disable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Disable Pay Scale'),
        ),
        CRM_Core_Action::ENABLE  => array(
          'name'  => ts('Enable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Enable Pay Scale'),
        ),
        CRM_Core_Action::DELETE  => array(
          'name'  => ts('Delete'),
          'url'   => 'civicrm/pay_scale',
          'qs'    => 'action=delete&id=%%id%%',
          'title' => ts('Delete Pay Scale'),
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
    CRM_Utils_System::setTitle(ts('Pay Scale'));
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
    // get all pay scale sorted by id
    $payScale = array();
    $dao = new CRM_Hrjobcontract_DAO_PayScale();
    $dao->orderBy('id');
    $dao->find();

    while ($dao->fetch()) {
      $payScale[$dao->id] = array();
      $payScale[$dao->id]['id'] = $dao->id;
      $payScale[$dao->id]['pay_scale'] = $dao->pay_scale;
      $payScale[$dao->id]['pay_grade'] = $dao->pay_grade;
      $payScale[$dao->id]['currency'] = $dao->currency;
      $payScale[$dao->id]['amount'] = $dao->amount;
      $payScale[$dao->id]['periodicity'] = $dao->periodicity;
      $payScale[$dao->id]['is_active'] = $dao->is_active;

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

      if ($isDelete) {
        $action -= CRM_Core_Action::DELETE;
      }

      $payScale[$dao->id]['action'] = CRM_Core_Action::formLink(self::links(), $action,
        array('id' => $dao->id)
      );
    }

    $this->assign('rows', $payScale);
  }

  /**
   * Get name of edit form
   *
   * @return string Classname of edit form.
   */
  function editForm() {
    return 'CRM_Hrjobcontract_Form_PayScale';
  }

  /**
   * Get edit form name
   *
   * @return string name of this page.
   */
  function editName() {
    return 'Pay Scale';
  }

  /**
   * Get user context.
   *
   * @return string user context.
   */
  function userContext($mode = null) {
    return 'civicrm/pay_scale';
  }
}
