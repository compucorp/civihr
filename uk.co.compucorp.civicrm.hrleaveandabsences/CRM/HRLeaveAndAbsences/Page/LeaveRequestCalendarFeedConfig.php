<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfig as LeaveRequestCalendarFeedConfig;

class CRM_HRLeaveAndAbsences_Page_LeaveRequestCalendarFeedConfig extends CRM_Core_Page_Basic {

  /**
   * A list of links available as actions to the items on the page list
   *
   * @var array
   */
  private $links = [];

  /**
   * {@inheritdoc}
   */
  public function run() {
    CRM_Utils_System::setTitle(ts('Calendar Feeds'));
    parent::run();
  }

  /**
   * {@inheritdoc}
   */
  public function browse() {
    $object = new LeaveRequestCalendarFeedConfig();
    $object->orderBy('created_date');
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

    $returnURL = CRM_Utils_System::url($this->userContext(), 'reset=1');
    CRM_Utils_Weight::addOrder($rows, 'CRM_HRLeaveAndAbsences_DAO_LeaveRequestCalendarFeedConfig', 'id', $returnURL);

    CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/leaveandabsence.css');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/hrleaveandabsences.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    CRM_Core_Resources::singleton()->addScriptFile('civicrm', 'js/jquery/jquery.crmEditable.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    $this->assign('rows', $rows);
  }

  /**
   * an array of action links
   *
   * @return array (reference)
   */
  public function &links() {
    if(empty($this->links)) {
      $this->links = [
        CRM_Core_Action::UPDATE  => [
          'name'  => ts('Edit'),
          'url'   => $this->userContext(),
          'qs'    => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Calendar Feed'),
        ],
        CRM_Core_Action::BASIC => [
          'name' => ts('View Feed Link'),
          'class' => '',
          'title' => ts('View Feed Link')
        ],
        CRM_Core_Action::DISABLE => [
          'name'  => ts('Disable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Disable Calendar Feed'),
        ],
        CRM_Core_Action::ENABLE  => [
          'name'  => ts('Enable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Enable Calendar Feed'),
        ],
        CRM_Core_Action::DELETE  => [
          'name'  => ts('Delete'),
          'class' => 'civihr-delete',
          'title' => ts('Delete Calendar Feed'),
        ],
      ];
    }

    return $this->links;
  }

  /**
   * Name of the edit form class
   *
   * @return string
   */
  public function editForm() {
    return 'CRM_HRLeaveAndAbsences_Form_LeaveRequestCalendarFeedConfig';
  }

  /**
   * {@inheritdoc}
   */
  public function editName() {
    return 'Calendar Feeds';
  }

  /**
   * {@inheritdoc}
   */
  public function userContext($mode = null) {
    return 'civicrm/admin/leaveandabsences/calendar-feeds';
  }

  /**
   * {@inheritdoc}
   */
  public function getBAOName() {
    return 'CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfig';
  }

  /**
   * {@inheritdoc}
   */
  public function edit($action, $id = NULL, $imageUpload = FALSE, $pushUserContext = TRUE) {
    if($action & CRM_Core_Action::DELETE) {
      $this->delete($id);
    } else {
      parent::edit($action, $id, $imageUpload, $pushUserContext);
    }
  }

  /**
   * Function for deleting a given LeaveRequestCalendarFeedConfig
   * with the id.
   *
   * @param int $id
   */
  public function delete($id) {
    try {
      civicrm_api3('LeaveRequestCalendarFeedConfig', 'delete', ['id' => $id]);
      CRM_Core_Session::setStatus(ts('The Calendar Feed was deleted'), 'Success', 'success');
    } catch(Exception $ex) {
      $message = ts('Error deleting the Calendar feed.') . ' ' . $ex->getMessage();
      CRM_Core_Session::setStatus($message, 'Error', 'error');
    }

    $url = CRM_Utils_System::url($this->userContext(), 'reset=1&action=browse');
    CRM_Utils_System::redirect($url);
  }

  /**
   * Calculates the links bitmask for the items on the list page
   *
   * @param LeaveRequestCalendarFeedConfig $leaveCalendarFeedConfig
   *
   * @return float|int
   */
  private function calculateLinksMask($leaveCalendarFeedConfig) {
    $mask = array_sum(array_keys($this->links()));

    if($leaveCalendarFeedConfig->is_active) {
      $mask -= CRM_Core_Action::ENABLE;
    } else {
      $mask -= CRM_Core_Action::DISABLE;
    }

    return $mask;
  }
}
