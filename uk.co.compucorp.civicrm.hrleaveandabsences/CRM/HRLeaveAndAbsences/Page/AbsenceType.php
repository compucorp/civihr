<?php

class CRM_HRLeaveAndAbsences_Page_AbsenceType extends CRM_Core_Page_Basic {

  private $links = array();

  public function run() {
    CRM_Utils_System::setTitle(ts('Leave/Absence Types'));
    parent::run();
  }

  public function browse() {
    $object = new CRM_HRLeaveAndAbsences_BAO_AbsenceType();
    $object->orderBy('weight');
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

    $returnURL = CRM_Utils_System::url('civicrm/admin/leaveandabsences/types', 'reset=1');
    CRM_Utils_Weight::addOrder($rows, 'CRM_HRLeaveAndAbsences_DAO_AbsenceType', 'id', $returnURL);

    $this->assign('rows', $rows);
  }

  /**
   * name of the BAO to perform various DB manipulations
   *
   * @return string
   * @access public
   */
  public function getBAOName() {
    return 'CRM_HRLeaveAndAbsences_BAO_AbsenceType';
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
          'url'   => 'civicrm/admin/leaveandabsences/type',
          'qs'    => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Leave/Absence Type'),
        ],
        CRM_Core_Action::DISABLE => [
          'name'  => ts('Disable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Disable Leave/Absence Type'),
        ],
        CRM_Core_Action::ENABLE  => [
          'name'  => ts('Enable'),
          'class' => 'crm-enable-disable',
          'title' => ts('Enable Leave/Absence Type'),
        ],
        CRM_Core_Action::DELETE  => [
          'name'  => ts('Delete'),
          'url'   => 'civicrm/admin/leaveandabsences/type',
          'qs'    => 'action=delete&id=%%id%%',
          'title' => ts('Delete Leave/Absence Type'),
        ],
        CRM_Core_Action::BASIC => [
          'name' => ts('Set as default'),
          'class' => 'civihr-set-as-default',
          'title' => ts('Set this Leave/Absence as default')
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
    // TODO: Implement editForm() method.
  }

  /**
   * name of the form
   *
   * @return string
   * @access public
   */
  public function editName() {
    // TODO: Implement editName() method.
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
    // TODO: Implement userContext() method.
  }

  private function calculateLinksMask($absenceType) {
    $mask = array_sum(array_keys($this->links()));

    if($absenceType->is_reserved) {
      $mask -= CRM_Core_Action::DELETE;
    }

    if($absenceType->is_active) {
      $mask -= CRM_Core_Action::ENABLE;
    } else {
      $mask -= CRM_Core_Action::DISABLE;
    }

    if($absenceType->is_default) {
      $mask -= CRM_Core_Action::BASIC;
    }

    return $mask;
  }
}
