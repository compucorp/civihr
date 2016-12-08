<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_JobContract as JobContractService;
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

    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($periodEntitlement->contact_id, $publicHoliday);

    $this->assertEquals(-1, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    $deletionLogic = new PublicHolidayLeaveRequestDeletion(new JobContractService());
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

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'from_date' => CRM_Utils_Date::processDate($publicHoliday->date),
      'contact_id' => $this->contract['contact_id'],
      'type_id' => $this->absenceType->id,
      'status_id' => 1
    ], true);

    $this->assertEquals(-1, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));

    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($this->contract['contact_id'], $publicHoliday);

    $this->assertEquals(0, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));

    $deletionLogic = new PublicHolidayLeaveRequestDeletion(new JobContractService());
    $deletionLogic->deleteForContact($this->contract['contact_id'], $publicHoliday);

    $this->assertEquals(-1, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));
  }

  public function testCanDeleteAllPublicHolidayLeaveRequestsForFuturePublicHolidays() {
    $contact = ContactFabricator::fabricate();

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
    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact['id'], $publicHoliday1);
    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact['id'], $publicHoliday2);
    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact['id'], $publicHoliday3);

    $this->assertEquals(-3, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    $deletionLogic = new PublicHolidayLeaveRequestDeletion(new JobContractService());
    $deletionLogic->deleteAllInTheFuture();

    // It's -1 instead of 0 because the public holiday 1 is in the past and its
    // respective leave request will not be deleted
    $this->assertEquals(-1, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  public function testItDeletesLeaveRequestsForPublicHolidaysInTheFutureOverlappingTheContractDates() {
    $contact = ContactFabricator::fabricate(['first_name' => 'Contact 1']);

    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('yesterday'),
      new DateTime('+300 days')
    );
    $periodEntitlement->contact_id = $contact['id'];
    $periodEntitlement->type_id = $this->absenceType->id;

    $contract = HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ], [
      'period_start_date' => CRM_Utils_Date::processDate('yesterday'),
      'period_end_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    $publicHoliday1 = PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => CRM_Utils_Date::processDate('yesterday')
    ]);
    $publicHoliday2 = PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => CRM_Utils_Date::processDate('+5 days')
    ]);
    $publicHoliday3 = PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => CRM_Utils_Date::processDate('+201 days')
    ]);

    // The Fabricator will create the leave request even for public holidays in
    // the past
    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact['id'], $publicHoliday1);
    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact['id'], $publicHoliday2);
    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact['id'], $publicHoliday3);

    $this->assertEquals(-3, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    $deletionLogic = new PublicHolidayLeaveRequestDeletion(new JobContractService());
    $deletionLogic->deleteAllForContract($contract['id']);

    // It's -2 instead of 0 because the public holiday 1 is in the past and its
    // respective leave request will not be deleted and the public holiday 3 is
    // after the contract end date and will not be deleted as well
    $this->assertEquals(-2, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  public function testItDeletesLeaveRequestsForAllPublicHolidaysInTheFutureOverlappingAContractWithNoEndDate() {
    $contact = ContactFabricator::fabricate(['first_name' => 'Contact 1']);

    $contract = HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ], [
      'period_start_date' =>  CRM_Utils_Date::processDate('yesterday'),
    ]);

    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+400 days')
    );
    $periodEntitlement->contact_id = $contact['id'];
    $periodEntitlement->type_id = $this->absenceType->id;

    $publicHoliday1 = PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => CRM_Utils_Date::processDate('yesterday')
    ]);
    $publicHoliday2 = PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => CRM_Utils_Date::processDate('+5 days')
    ]);
    $publicHoliday3 = PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => CRM_Utils_Date::processDate('+201 days')
    ]);

    // The Fabricator will create the leave request even for public holidays in
    // the past
    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact['id'], $publicHoliday1);
    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact['id'], $publicHoliday2);
    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact['id'], $publicHoliday3);

    $this->assertEquals(-3, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    $deletionLogic = new PublicHolidayLeaveRequestDeletion(new JobContractService());
    $deletionLogic->deleteAllForContract($contract['id']);

    // It's -1 instead of 0 because the public holiday 1 is in the past and its
    // respective leave request will not be deleted
    $this->assertEquals(-1, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  public function testItDoesntDeleteAnythingIfTheContractIDDoesntExist() {
    $contact = ContactFabricator::fabricate(['first_name' => 'Contact 1']);

    $publicHoliday = $this->instantiatePublicHoliday('today');
    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact['id'], $publicHoliday);

    $this->assertEquals(1, $this->countNumberOfPublicHolidayBalanceChanges());

    $deletionLogic = new PublicHolidayLeaveRequestDeletion(new JobContractService());
    $deletionLogic->deleteAllForContract(9998398298);

    $this->assertEquals(1, $this->countNumberOfPublicHolidayBalanceChanges());
  }

  public function testItDeletesLeaveRequestsForAllContactsWithContractsOverlappingTheGivenPublicHoliday() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact1['id']
    ], [
      'period_start_date' =>  CRM_Utils_Date::processDate('yesterday'),
    ]);

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact2['id']
    ], [
      'period_start_date' =>  CRM_Utils_Date::processDate('yesterday'),
    ]);

    $publicHoliday = $this->instantiatePublicHoliday('+5 days');

    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact1['id'], $publicHoliday);
    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact2['id'], $publicHoliday);

    $this->assertNotNull(LeaveRequest::findPublicHolidayLeaveRequest($contact1['id'], $publicHoliday));
    $this->assertNotNull(LeaveRequest::findPublicHolidayLeaveRequest($contact2['id'], $publicHoliday));

    $deletionLogic = new PublicHolidayLeaveRequestDeletion(new JobContractService());
    $deletionLogic->deleteForAllContacts($publicHoliday);

    $this->assertNull(LeaveRequest::findPublicHolidayLeaveRequest($contact1['id'], $publicHoliday));
    $this->assertNull(LeaveRequest::findPublicHolidayLeaveRequest($contact2['id'], $publicHoliday));
  }

  public function testItDoesntDeleteLeaveRequestsForAllContactsWithoutContractsOverlappingTheGivenPublicHoliday() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact1['id']
    ], [
      'period_start_date' =>  CRM_Utils_Date::processDate('-10 days'),
      'period_end_date' => CRM_Utils_Date::processDate('today')
    ]);

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact2['id']
    ], [
      'period_start_date' =>  CRM_Utils_Date::processDate('+2 days'),
    ]);

    $publicHoliday = $this->instantiatePublicHoliday('+1 day');

    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact1['id'], $publicHoliday);
    PublicHolidayLeaveRequestFabricator::fabricateWithoutValidation($contact2['id'], $publicHoliday);

    $this->assertNotNull(LeaveRequest::findPublicHolidayLeaveRequest($contact1['id'], $publicHoliday));
    $this->assertNotNull(LeaveRequest::findPublicHolidayLeaveRequest($contact2['id'], $publicHoliday));

    $deletionLogic = new PublicHolidayLeaveRequestDeletion(new JobContractService());
    $deletionLogic->deleteForAllContacts($publicHoliday);

    $this->assertNotNull(LeaveRequest::findPublicHolidayLeaveRequest($contact1['id'], $publicHoliday));
    $this->assertNotNull(LeaveRequest::findPublicHolidayLeaveRequest($contact2['id'], $publicHoliday));
  }

  private function instantiatePublicHoliday($date) {
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = CRM_Utils_Date::processDate($date);

    return $publicHoliday;
  }

  private function countNumberOfPublicHolidayBalanceChanges() {
    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id'));

    $bao = new LeaveBalanceChange();
    $bao->type_id = $balanceChangeTypes['Public Holiday'];
    $bao->source_type = LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY;

    return $bao->count();
  }

}
