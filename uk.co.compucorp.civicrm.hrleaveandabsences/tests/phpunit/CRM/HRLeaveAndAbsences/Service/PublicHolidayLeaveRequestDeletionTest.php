<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion as PublicHolidayLeaveRequestDeletion;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHoliday as PublicHolidayFabricator;
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

  public function testCanDeleteAllPublicHolidayLeaveRequestsForFuturePublicHolidays() {
    $contact = ContactFabricator::fabricate(['first_name' => 'Contact 1']);

    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('2016-01-01'),
      new DateTime('+2 years')
    );
    $periodEntitlement->contact_id = $contact['id'];
    $periodEntitlement->type_id = $this->absenceType->id;

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ], [
      'period_start_date' => '2016-01-01',
    ]);

    $this->assertEquals(0, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    $publicHoliday1 = PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => CRM_Utils_Date::processDate('yesterday')
    ]);
    $publicHoliday2 = PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => CRM_Utils_Date::processDate('+5 days')
    ]);
    $publicHoliday3 = PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => CRM_Utils_Date::processDate('+2 years')
    ]);
    PublicHolidayLeaveRequestFabricator::fabricate($contact['id'], $publicHoliday1);
    PublicHolidayLeaveRequestFabricator::fabricate($contact['id'], $publicHoliday2);
    PublicHolidayLeaveRequestFabricator::fabricate($contact['id'], $publicHoliday3);

    $this->assertEquals(-3, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    $deletionLogic = new PublicHolidayLeaveRequestDeletion();
    $deletionLogic->deleteAllInTheFuture();

    // It's -1 instead of 0 because the public holiday 1 is in the past and its
    // respective leave request will not be deleted
    $this->assertEquals(-1, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  private function instantiatePublicHoliday($date) {
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = CRM_Utils_Date::processDate($date);

    return $publicHoliday;
  }
}
