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
   * Browse all pay scales types
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
      $payScale[$dao->id]['currency'] = $dao->currency;
      $payScale[$dao->id]['amount'] = $dao->amount;
      $payScale[$dao->id]['periodicity'] = $dao->periodicity;
      $payScale[$dao->id]['is_active'] = $dao->is_active;

      $payScale[$dao->id]['action'] = $this->generateActionLinks(
        $dao->id, $dao->pay_scale, $dao->is_active
      );
    }

    $this->assign('rows', $payScale);
  }

  /**
   * Generates action links for given pay scale ID.
   * 
   * @param int $payScaleID
   *   ID of pay scale for which action links need to be generated
   * @param string $payScaleName
   *   Name of pay scale
   * @param boolean $isActive
   *   If pay scale is active or not
   * 
   * @return string
   *   HTML code for the actions of given pay scale
   */
  private function generateActionLinks($payScaleID, $payScaleName, $isActive) {
    // form all action links
    $action = array_sum(array_keys($this->links()));
    $action -= ($isActive ? CRM_Core_Action::ENABLE : CRM_Core_Action::DISABLE);

    // (Not Applicable) pay scale should neither be deleted nor edited!
    if ($payScaleName === 'Not Applicable') {
      $action -= CRM_Core_Action::DELETE;
      $action -= CRM_Core_Action::UPDATE;
    }

    return CRM_Core_Action::formLink($this->links(), $action, ['id' => $payScaleID]);
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
