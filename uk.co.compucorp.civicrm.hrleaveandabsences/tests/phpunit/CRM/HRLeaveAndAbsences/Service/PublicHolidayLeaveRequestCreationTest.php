<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation as PublicHolidayLeaveRequestCreation;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHoliday as PublicHolidayFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreationTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreationTest extends BaseHeadlessTest {

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

  public function testCanCreateAPublicHolidayLeaveRequestForASingleContact() {
    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();
    $periodEntitlement->contact_id = 2;
    $periodEntitlement->type_id = $this->absenceType->id;

    $creationLogic = new PublicHolidayLeaveRequestCreation();
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = CRM_Utils_Date::processDate('first monday of this year');

    $creationLogic->createForContact($periodEntitlement->contact_id, $publicHoliday);

    $this->assertEquals(-1, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  public function testItUpdatesTheBalanceChangeForOverlappingLeaveRequestDayToZero() {
    $contactID = 2;

    $leaveRequest = LeaveRequestFabricator::fabricate([
      'contact_id' => $contactID,
      'type_id' => $this->absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01')
    ], true);

    $this->assertEquals(-1, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));

    $creationLogic = new PublicHolidayLeaveRequestCreation();
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = CRM_Utils_Date::processDate('2016-01-01');

    $creationLogic->createForContact($contactID, $publicHoliday);

    $this->assertEquals(0, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));
  }

  public function testCanCreatePublicHolidayLeaveRequestsForAllPublicHolidaysInTheFuture() {
    $contact = ContactFabricator::fabricate(['first_name' => 'Contact 1']);

    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();
    $periodEntitlement->contact_id = $contact['id'];
    $periodEntitlement->type_id = $this->absenceType->id;

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ], [
      'period_start_date' => '2016-01-01',
    ]);

    $this->assertEquals(0, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' =>  CRM_Utils_Date::processDate('2016-01-01')
    ]);

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' =>  CRM_Utils_Date::processDate('tomorrow')
    ]);

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' =>  CRM_Utils_Date::processDate('+5 days')
    ]);

    $creationLogic = new PublicHolidayLeaveRequestCreation();
    $creationLogic->createForAllInTheFuture();

    // It's -2 instead of -3 because the first public holiday is in the past
    // and we should not create a leave request for it
    $this->assertEquals(-2, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  public function testItDoesntCreateLeaveRequestsForAllPublicHolidaysInTheFutureIfThereIsNoMTPHLAbsenceTypes() {
    AbsenceType::del($this->absenceType->id);

    $contact = ContactFabricator::fabricate(['first_name' => 'Contact 1']);

    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();
    $periodEntitlement->contact_id = $contact['id'];
    $periodEntitlement->type_id = $this->absenceType->id;

    $this->assertEquals(0, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' =>  CRM_Utils_Date::processDate('tomorrow')
    ]);

    $creationLogic = new PublicHolidayLeaveRequestCreation();
    $creationLogic->createForAllInTheFuture();

    // The Balance is still 0 because no Leave Request was created
    $this->assertEquals(0, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }
}
