<?php

/**
 * Class CRM_HRLeaveAndAbsences_Page_AbsencePeriod
 *
 * This is the class that handles requests to the Absence Period list and form
 * pages.
 */
class CRM_HRLeaveAndAbsences_Page_AbsencePeriod extends CRM_Core_Page_Basic {

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
    CRM_Utils_System::setTitle(ts('Absence Periods'));
    parent::run();
  }

  /**
   * {@inheritdoc}
   */
  public function browse() {
    $object = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $object->orderBy('weight');
    $object->find();
    $rows = [];
    while($object->fetch()) {
      $rows[$object->id] = [];

      CRM_Core_DAO::storeValues($object, $rows[$object->id]);

      $rows[$object->id]['action'] = CRM_Core_Action::formLink(
        $this->links(),
        $this->calculateLinksMask($object),
        ['id' => $object->id]
      );
    }

    $returnURL = CRM_Utils_System::url('civicrm/admin/leaveandabsences/periods', 'reset=1');
    CRM_Utils_Weight::addOrder($rows, 'CRM_HRLeaveAndAbsences_DAO_AbsencePeriod', 'id', $returnURL);

    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/hrleaveandabsences.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/hrleaveandabsences.list.absenceperiod.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    CRM_Core_Resources::singleton()->addScriptFile('civicrm', 'js/jquery/jquery.crmEditable.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');

    $this->assign('rows', $rows);
  }

  /**
   * name of the BAO to perform various DB manipulations
   *
   * @return string
   * @access public
   */
  public function getBAOName() {
    return 'CRM_HRLeaveAndAbsences_BAO_AbsencePeriod';
  }

  /**
   * An array of all action links available to the items on the list page
   *
   * @return array
   */
  public function &links() {
    if(empty($this->links)) {
      $returnUrl = CRM_Utils_System::url(
        'civicrm/admin/leaveandabsences/periods',
        http_build_query([
          'action' => 'browse',
          'reset' => 1,
        ])
      );

      $this->links = [
        CRM_Core_Action::UPDATE  => [
          'name' => ts('Edit'),
          'url' => 'civicrm/admin/leaveandabsences/periods',
          'qs' => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Absence Period'),
        ],
        CRM_Core_Action::DELETE  => [
          'name' => ts('Delete'),
          'class' => 'civihr-delete',
          'title' => ts('Delete Absence Period'),
        ],
        CRM_Core_Action::BASIC => [
          'name' => ts('Manage Entitlements'),
          'url' => 'civicrm/admin/leaveandabsences/periods/manage_entitlements',
          'qs' => 'id=%%id%%&reset=1&returnUrl=' . urlencode($returnUrl),
          'class' => 'civihr-manage-entitlements',
          'title' => ts('Manage entitlements for this Absence Period'),
        ]
      ];
    }

    return $this->links;
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
    return 'civicrm/admin/leaveandabsences/periods';
  }

  /**
   * Name of the edit form class
   *
   * @return string
   * @access public
   */
  public function editForm() {
    return 'CRM_HRLeaveAndAbsences_Form_AbsencePeriod';
  }

  /**
   * Name of the form
   *
   * @return string
   * @access public
   */
  public function editName() {
    return 'Absence Periods';
  }

  /**
   * Calculates the links bitmask for the items on the list page
   *
   * @return number The links bitmask
   */
  private function calculateLinksMask() {
    $mask = array_sum(array_keys($this->links()));

    return $mask;
  }
}
