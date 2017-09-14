<?php

class CRM_HRLeaveAndAbsences_Page_PublicHoliday extends CRM_Core_Page_Basic {

  private $links = array();

  /**
   * Page entry point.
   */
  public function run() {
    CRM_Utils_System::setTitle(ts('Public Holidays'));
    parent::run();
  }

  /**
   * Browse action.
   */
  public function browse() {
    $object = new CRM_HRLeaveAndAbsences_BAO_PublicHoliday();
    $object->orderBy('date');
    $object->find();
    $rows = [];
    while($object->fetch()) {
      $rows[$object->id] = array();

      CRM_Core_DAO::storeValues($object, $rows[$object->id]);

      $rows[$object->id]['action'] = CRM_Core_Action::formLink(
          $this->links(),
          $this->calculateLinksMask($object),
          ['id' => $object->id]
      );
    }

    $returnURL = CRM_Utils_System::url('civicrm/admin/leaveandabsences/public_holidays', 'reset=1');
    CRM_Utils_Weight::addOrder($rows, 'CRM_HRLeaveAndAbsences_DAO_PublicHoliday', 'id', $returnURL);

    CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/leaveandabsence.css');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/hrleaveandabsences.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    CRM_Core_Resources::singleton()->addScriptFile('civicrm', 'js/jquery/jquery.crmEditable.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');

    $this->assign('rows', $rows);
  }

  /**
   * Edit action.
   *
   * @param string $action
   * @param int $id
   * @param bool $imageUpload
   * @param bool $pushUserContext
   */
  public function edit($action, $id = NULL, $imageUpload = FALSE, $pushUserContext = TRUE) {
    if($action & CRM_Core_Action::DELETE) {
      $this->delete($id);
    } else {
      parent::edit($action, $id, $imageUpload, $pushUserContext);
    }
  }

  /**
   * Delete action.
   *
   * @param int $id
   */
  public function delete($id) {
    try {
      CRM_HRLeaveAndAbsences_BAO_PublicHoliday::del($id);
      CRM_Core_Session::setStatus(ts('The Public Holiday was deleted'), 'Success', 'success');
    } catch(Exception $ex) {
      $message = ts('Error deleting the Public Holiday.') . ' '. $ex->getMessage();
      CRM_Core_Session::setStatus($message, 'Error', 'error');
    }

    $url = CRM_Utils_System::url('civicrm/admin/leaveandabsences/public_holidays', 'reset=1&action=browse');
    CRM_Utils_System::redirect($url);
  }

  /**
   * Return a name of the BAO to perform various DB manipulations.
   *
   * @return string
   * @access public
   */
  public function getBAOName() {
    return 'CRM_HRLeaveAndAbsences_BAO_PublicHoliday';
  }

  /**
   * Return an array of action links.
   *
   * @return array (reference)
   * @access public
   */
  public function &links() {
    if(empty($this->links)) {
      $this->links = [
        CRM_Core_Action::UPDATE  => [
          'name'  => ts('Edit'),
          'url'   => 'civicrm/admin/leaveandabsences/public_holidays',
          'qs'    => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Public Holiday'),
        ],
        CRM_Core_Action::DISABLE => [
          'name'  => ts('Disable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Disable Public Holiday'),
        ],
        CRM_Core_Action::ENABLE  => [
          'name'  => ts('Enable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Enable Public Holiday'),
        ],
        CRM_Core_Action::DELETE  => [
          'name'  => ts('Delete'),
          'class' => 'civihr-delete',
          'title' => ts('Delete Public Holiday'),
        ],
      ];
    }

    return $this->links;
  }

  /**
   * Return a name of the edit form class.
   *
   * @return string
   * @access public
   */
  public function editForm() {
    return 'CRM_HRLeaveAndAbsences_Form_PublicHoliday';
  }

  /**
   * Return a name of the form.
   *
   * @return string
   * @access public
   */
  public function editName() {
    return 'Public Holidays';
  }

  /**
   * Return userContext to pop back to.
   *
   * @param int $mode mode that we are in
   *
   * @return string
   * @access public
   */
  public function userContext($mode = null) {
    return 'civicrm/admin/leaveandabsences/public_holidays';
  }

  /**
   * Return Link mask value depending on Public Holiday properties.
   *
   * @param CRM_HRLeaveAndAbsences_BAO_PublicHoliday $publicHoliday
   * @return int
   */
  private function calculateLinksMask($publicHoliday) {
    $mask = array_sum(array_keys($this->links()));

    if($publicHoliday->is_active) {
      $mask -= CRM_Core_Action::ENABLE;
    } else {
      $mask -= CRM_Core_Action::DISABLE;
    }

    return $mask;
  }
}
