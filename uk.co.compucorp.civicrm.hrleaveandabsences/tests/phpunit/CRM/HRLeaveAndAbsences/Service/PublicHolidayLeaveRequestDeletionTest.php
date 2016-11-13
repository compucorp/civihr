<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion as PublicHolidayLeaveRequestDeletion;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletionTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletionTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_ContractHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeavePeriodEntitlementHelpersTrait;

  /**
   * @var CRM_HRLeaveAndAbsences_BAO_AbsenceType
   */
  private $absenceType;

  public function setUp() {
    // We delete everything two avoid problems with the default absence types
    // created during the extension installation
    $tableName = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");

    $this->absenceType = AbsenceTypeFabricator::fabricate([
      'must_take_public_holiday_as_leave' => 1
    ]);
  }

  public function testCanDeleteAPublicHolidayLeaveRequestForASingleContact() {
    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();
    $periodEntitlement->contact_id = 2;
    $periodEntitlement->type_id = $this->absenceType->id;

    $publicHoliday = $this->instantiatePublicHoliday('2016-01-01');

    PublicHolidayLeaveRequestFabricator::fabricate($periodEntitlement->contact_id, $publicHoliday);

    $this->assertEquals(-1, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    $deletionLogic = new PublicHolidayLeaveRequestDeletion();
    $deletionLogic->deleteForContact($periodEntitlement->contact_id, $publicHoliday);

    $this->assertEquals(0, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  public function testItUpdatesOverlappingLeaveRequestDatesAfterDeletingAPublicHolidayLeaveRequests() {
    // We need the Work Pattern and the contract in order to be able to
    // recalculate the deduction after deleting the Public Holiday Leave Request
    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);
    $this->createContract();
    $this->setContractDates('2016-01-01', '2016-12-31');

    $publicHoliday = $this->instantiatePublicHoliday('2016-10-10');

    $leaveRequest = LeaveRequestFabricator::fabricate([
      'from_date' => CRM_Utils_Date::processDate($publicHoliday->date),
      'contact_id' => $this->contract['contact_id'],
      'type_id' => $this->absenceType->id
    ], true);

    $this->assertEquals(-1, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));

    PublicHolidayLeaveRequestFabricator::fabricate($this->contract['contact_id'], $publicHoliday);

    $this->assertEquals(0, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));

    $deletionLogic = new PublicHolidayLeaveRequestDeletion();
    $deletionLogic->deleteForContact($this->contract['contact_id'], $publicHoliday);

    $this->assertEquals(-1, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));
  }

  private function instantiatePublicHoliday($date) {
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = CRM_Utils_Date::processDate($date);

    return $publicHoliday;
  }
}
