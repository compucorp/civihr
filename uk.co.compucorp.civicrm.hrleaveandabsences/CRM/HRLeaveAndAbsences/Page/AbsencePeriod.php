<?php

require_once __DIR__ . '/../../../tests/phpunit/helpers/LeaveBalanceChangeHelpersTrait.php';
require_once __DIR__ . '/../../../tests/phpunit/helpers/LeaveRequestHelpersTrait.php';

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

    $this->createThings();

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
      $this->links = [
        CRM_Core_Action::UPDATE  => [
          'name'  => ts('Edit'),
          'url'   => 'civicrm/admin/leaveandabsences/periods',
          'qs'    => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Absence Period'),
        ],
        CRM_Core_Action::DELETE  => [
          'name'  => ts('Delete'),
          'class' => 'civihr-delete',
          'title' => ts('Delete Absence Period'),
        ],
        CRM_Core_Action::BASIC => [
          'name'  => ts('Manage Entitlements'),
          'url'   => 'civicrm/admin/leaveandabsences/periods/manage_entitlements',
          'qs'    => 'id=%%id%%&reset=1',
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

  private function createThings() {
    //===============================================================================================
    // Contract

    $contract = CRM_Hrjobcontract_Test_Fabricator_HRJobContract::fabricate(
      ['contact_id' => 202],
      ['period_start_date' => '2016-01-01']
    );

    CRM_Hrjobcontract_Test_Fabricator_HRJobLeave::fabricate([
      "values" => [
        [
          'jobcontract_id'      => $contract['id'],
          'leave_type'          => 1,
          'leave_amount'        => 20,
          'add_public_holidays' => 1,
        ],
        [
          'jobcontract_id'      => $contract['id'],
          'leave_type'          => 2,
          'leave_amount'        => 0,
          'add_public_holidays' => 0,
        ],
        [
          'jobcontract_id'      => $contract['id'],
          'leave_type'          => 3,
          'leave_amount'        => 0,
          'add_public_holidays' => 0,
        ],
      ]
    ]);

    CRM_Hrjobcontract_Test_Fabricator_HRJobHour::fabricate([
      'jobcontract_id' => $contract['id'],
      'location_standard_hours' => 1,
      'hours_type' => 8,
    ]);

    CRM_Hrjobcontract_Test_Fabricator_HRJobPay::fabricate([
      'jobcontract_id' => $contract['id'],
      'is_paid' => 0,
    ]);

    CRM_Hrjobcontract_Test_Fabricator_HRJobHealth::fabricate([
      'jobcontract_id' => $contract['id'],
    ]);

    CRM_Hrjobcontract_Test_Fabricator_HRJobPension::fabricate([
      'jobcontract_id' => $contract['id'],
    ]);

    CRM_Hrjobcontract_Test_Fabricator_HRJobPension::fabricate([
      'jobcontract_id' => $contract['id'],
    ]);

    //===============================================================================================
    // Absence Periods

    $period2016 = CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod::fabricate([
      'title' => '2016',
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

//===============================================================================================
// 2016 Entitlement

    $entitlement2016 = CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement::fabricate([
      'contact_id' => 202,
      'period_id' => $period2016->id,
      'type_id' => 2
    ]);

    $balanceChangeTypes = array_flip(CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange::buildOptions('type_id'));

    CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange::fabricate([
      'source_id' => $entitlement2016->id,
      'source_type' => CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'amount' => 0,
      'type_id' => $balanceChangeTypes['Leave']
    ]);

    CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange::fabricate([
      'source_id' => $entitlement2016->id,
      'source_type' => CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'amount' => 5,
      'expiry_date' => CRM_Utils_Date::processDate('2016-02-27'),
      'type_id' => $balanceChangeTypes['Brought Forward']
    ]);

//===============================================================================================
// 2016 Leave Requests

    $leaveRequestStatus = array_flip(CRM_HRLeaveAndAbsences_BAO_LeaveRequest::buildOptions('status_id'));
    $sicknessReasons = array_flip(CRM_HRLeaveAndAbsences_BAO_SicknessRequest::buildOptions('reason'));
    $balanceChangeService = new CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange();

    CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest::fabricateWithoutValidation([
      'contact_id' => $entitlement2016->contact_id,
      'type_id' => $entitlement2016->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-05'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-05'),
    ], true);

    CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest::fabricateWithoutValidation([
      'contact_id' => $entitlement2016->contact_id,
      'type_id' => $entitlement2016->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-02-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-01'),
    ], true);

    CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest::fabricateWithoutValidation([
      'contact_id' => $entitlement2016->contact_id,
      'type_id' => $entitlement2016->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-02-26'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-28'),
    ], true);

    CRM_HRLeaveAndAbsences_Test_Fabricator_TOILRequest::fabricateWithoutValidation([
      'contact_id' => $entitlement2016->contact_id,
      'type_id' => $entitlement2016->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-17'),
      'expiry_date' => CRM_Utils_Date::processDate('2016-02-17'),
      'toil_to_accrue' => 1,
      'duration' => 100
    ]);

    CRM_HRLeaveAndAbsences_Test_Fabricator_TOILRequest::fabricateWithoutValidation([
      'contact_id' => $entitlement2016->contact_id,
      'type_id' => $entitlement2016->type_id,
      'from_date' => CRM_Utils_Date::processDate('2016-02-25'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-25'),
      'expiry_date' => CRM_Utils_Date::processDate('2016-03-01'),
      'toil_to_accrue' => 2,
      'duration' => 100
    ]);
  }
}
