<?php

class CRM_HRLeaveAndAbsences_Page_WorkPattern extends CRM_Core_Page_Basic {

  private $links = array();

  public function run() {
    CRM_Utils_System::setTitle(ts('Work Patterns'));
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
      $rows[$object->id]['number_of_hours'] = $object->number_of_hours ?: 0;
      $rows[$object->id]['number_of_weeks'] = $object->number_of_weeks ?: 0;

      $rows[$object->id]['action'] = CRM_Core_Action::formLink(
          $this->links(),
          $this->calculateLinksMask($object),
          ['id' => $object->id]
      );
    }

    $returnURL = CRM_Utils_System::url('civicrm/admin/leaveandabsences/work_patterns', 'reset=1');
    CRM_Utils_Weight::addOrder($rows, 'CRM_HRLeaveAndAbsences_DAO_WorkPattern', 'id', $returnURL);

    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/hrleaveandabsences.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');

    $this->assign('rows', $rows);
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
    }

    return $mask;
  }
}
