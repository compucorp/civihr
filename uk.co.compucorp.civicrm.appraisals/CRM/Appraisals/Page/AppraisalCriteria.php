<?php

require_once 'CRM/Core/Page.php';

class CRM_Appraisals_Page_AppraisalCriteria extends CRM_Core_Page_Basic {
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
    return 'CRM_Appraisals_BAO_AppraisalCriteria';
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
          'url'   => 'civicrm/appraisal_criteria',
          'qs'    => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Appraisal Criteria'),
        ),
        CRM_Core_Action::DISABLE => array(
          'name'  => ts('Disable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Disable Appraisal Criteria'),
        ),
        CRM_Core_Action::ENABLE  => array(
          'name'  => ts('Enable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Enable Appraisal Criteria'),
        ),
        CRM_Core_Action::DELETE  => array(
          'name'  => ts('Delete'),
          'url'   => 'civicrm/appraisal_criteria',
          'qs'    => 'action=delete&id=%%id%%',
          'title' => ts('Delete Appraisal Criteria'),
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
    CRM_Utils_System::setTitle(ts('Appraisal Criteria'));
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
    // get all appraisal criteria sorted by value
    $appraisalCriteria = array();
    $dao = new CRM_Appraisals_DAO_AppraisalCriteria();
    $dao->orderBy('value');
    $dao->find();
    $count = $dao->count();
    $i = 1;

    while ($dao->fetch()) {
      $appraisalCriteria[$dao->id] = array();
      $appraisalCriteria[$dao->id]['id'] = $dao->id;
      $appraisalCriteria[$dao->id]['value'] = $dao->value;
      $appraisalCriteria[$dao->id]['label'] = $dao->label;
      $appraisalCriteria[$dao->id]['is_active'] = $dao->is_active;

      // form all action links
      $action = array_sum(array_keys($this->links()));


      if ($dao->is_active) {
        $action -= CRM_Core_Action::ENABLE;
      }
      else {
        $action -= CRM_Core_Action::DISABLE;
      }

      $canBeDeleted = TRUE;
      if ($i !== $count) {
        $canBeDeleted = FALSE;
      }
      
      if (!$canBeDeleted) {
        $action -= CRM_Core_Action::DELETE;
      }

      $appraisalCriteria[$dao->id]['action'] = CRM_Core_Action::formLink(self::links(), $action,
        array('id' => $dao->id)
      );
      $i++;
    }

    $this->assign('rows', $appraisalCriteria);
  }

  /**
   * Get name of edit form
   *
   * @return string Classname of edit form.
   */
  function editForm() {
    return 'CRM_Appraisals_Form_AppraisalCriteria';
  }

  /**
   * Get edit form name
   *
   * @return string name of this page.
   */
  function editName() {
    return 'Appraisal Criteria';
  }

  /**
   * Get user context.
   *
   * @return string user context.
   */
  function userContext($mode = null) {
    return 'civicrm/appraisal_criteria';
  }
}
