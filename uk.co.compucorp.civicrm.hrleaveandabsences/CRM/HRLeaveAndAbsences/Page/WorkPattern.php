<?php

use CRM_HRLeaveAndAbsences_Service_WorkPattern as WorkPatternService;

class CRM_HRLeaveAndAbsences_Page_WorkPattern extends CRM_Core_Page_Basic {

  private $links = array();

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_WorkPattern
   */
  private $workPatternService;

  public function run() {
    CRM_Utils_System::setTitle(ts('Work Patterns'));
    $this->workPatternService = new WorkPatternService();
    parent::run();
  }

  public function browse() {
    $object = new CRM_HRLeaveAndAbsences_BAO_WorkPattern();
    $object->findWithNumberOfWeeksAndHours();
    $rows = [];
    while($object->fetch()) {
      $rows[$object->id] = array();

      CRM_Core_DAO::storeValues($object, $rows[$object->id]);

      // we need to manually add these fields because, since they are not
      // real fields, storeValues will ignore them
      $rows[$object->id]['number_of_weeks'] = (int)$object->number_of_weeks;
      $rows[$object->id]['number_of_hours'] = (float)$object->number_of_hours;

      if($object->number_of_weeks > 1) {
        $rows[$object->id]['number_of_hours'] = ts('Various');
      }

      $rows[$object->id]['action'] = CRM_Core_Action::formLink(
          $this->links(),
          $this->calculateLinksMask($object),
          ['id' => $object->id]
      );
    }

    $returnURL = CRM_Utils_System::url('civicrm/admin/leaveandabsences/work_patterns', 'reset=1');
    CRM_Utils_Weight::addOrder($rows, 'CRM_HRLeaveAndAbsences_DAO_WorkPattern', 'id', $returnURL);

    CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/leaveandabsence.css');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/hrleaveandabsences.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    CRM_Core_Resources::singleton()->addScriptFile('civicrm', 'js/jquery/jquery.crmEditable.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');

    $this->assign('rows', $rows);
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
   * Deletes the Work Pattern with the given ID and redirects the user back to
   * the list page.
   *
   * @param int $id The ID of the Work Pattern to be deleted
   */
  public function delete($id) {
    try {
      CRM_HRLeaveAndAbsences_BAO_WorkPattern::del($id);
      CRM_Core_Session::setStatus(ts('The Work Pattern was deleted'), 'Success', 'success');
    } catch(Exception $ex) {
      $message = ts('Error deleting the Work Pattern.') . ' '. $ex->getMessage();
      CRM_Core_Session::setStatus($message, 'Error', 'error');
    }

    $url = CRM_Utils_System::url('civicrm/admin/leaveandabsences/work_patterns', 'reset=1&action=browse');
    CRM_Utils_System::redirect($url);
  }

  /**
   * name of the BAO to perform various DB manipulations
   *
   * @return string
   * @access public
   */
  public function getBAOName() {
    return 'CRM_HRLeaveAndAbsences_BAO_WorkPattern';
  }

  /**
   * an array of action links
   *
   * @return array (reference)
   * @access public
   */
  public function &links() {
    if(empty($this->links)) {
      $this->links = [
        CRM_Core_Action::UPDATE  => [
          'name'  => ts('Edit'),
          'url'   => 'civicrm/admin/leaveandabsences/work_patterns',
          'qs'    => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Work Pattern'),
        ],
        CRM_Core_Action::DISABLE => [
          'name'  => ts('Disable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Disable Work Pattern'),
        ],
        CRM_Core_Action::ENABLE  => [
          'name'  => ts('Enable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Enable Work Pattern'),
        ],
        CRM_Core_Action::DELETE  => [
          'name'  => ts('Delete'),
          'class' => 'civihr-delete',
          'title' => ts('Delete Work Pattern'),
        ],
        CRM_Core_Action::BASIC => [
          'name' => ts('Set as default'),
          'class' => 'civihr-set-as-default',
          'title' => ts('Set this Work Pattern as default')
        ]
      ];
    }

    return $this->links;
  }

  /**
   * name of the edit form class
   *
   * @return string
   * @access public
   */
  public function editForm() {
    return 'CRM_HRLeaveAndAbsences_Form_WorkPattern';
  }

  /**
   * name of the form
   *
   * @return string
   * @access public
   */
  public function editName() {
    return 'Work Patterns';
  }

  /**
   * userContext to pop back to
   *
   * @param int $mode mode that we are in
   *
   * @return string
   * @access public
   */
  public function userContext($mode = null) {
    return 'civicrm/admin/leaveandabsences/work_patterns';
  }

  private function calculateLinksMask($workPattern) {
    $mask = array_sum(array_keys($this->links()));

    if($workPattern->is_active) {
      $mask -= CRM_Core_Action::ENABLE;
    } else {
      $mask -= CRM_Core_Action::DISABLE;
    }

    if($workPattern->is_default) {
      $mask -= CRM_Core_Action::BASIC;
      $mask -= CRM_Core_Action::DISABLE;
    }

    if($this->canNotDelete($workPattern->id)) {
      $mask -= CRM_Core_Action::DELETE;
    }

    return $mask;
  }

  /**
   * Checks whether a WorkPattern object cannot be deleted.
   *
   * @param int $workPatternID
   *
   * @return bool
   */
  private function canNotDelete($workPatternID) {
    return $this->workPatternService->workPatternHasEverBeenUsed($workPatternID);
  }
}
