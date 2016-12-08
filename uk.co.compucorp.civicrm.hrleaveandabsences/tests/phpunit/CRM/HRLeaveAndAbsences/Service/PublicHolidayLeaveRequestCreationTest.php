<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_JobContract as JobContractService;
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

    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = CRM_Utils_Date::processDate('first monday of this year');

    $creationLogic->createForContact($periodEntitlement->contact_id, $publicHoliday);

    $this->assertEquals(-1, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  public function testItDoesNotCreateALeaveRequestIfTheresIsAlreadyALeaveRequestForTheGivenPublicHolidayAndContact() {
    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();
    $periodEntitlement->contact_id = 2;
    $periodEntitlement->type_id = $this->absenceType->id;

    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());

    $date = new DateTime('first monday of this year');
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = $date->format('YmdHis');

    $creationLogic->createForContact($periodEntitlement->contact_id, $publicHoliday);

    $this->assertEquals(1, $this->countNumberOfLeaveRequests($periodEntitlement->contact_id, $date->format('Ymd')));

    $creationLogic->createForContact($periodEntitlement->contact_id, $publicHoliday);

    $this->assertEquals(1, $this->countNumberOfLeaveRequests($periodEntitlement->contact_id, $date->format('Ymd')));
  }

  public function testItUpdatesTheBalanceChangeForOverlappingLeaveRequestDayToZero() {
    $contactID = 2;

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contactID,
      'type_id' => $this->absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'status_id' => 1
    ], true);

    $this->assertEquals(-1, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));

    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = CRM_Utils_Date::processDate('2016-01-01');

    $creationLogic->createForContact($contactID, $publicHoliday);

    $this->assertEquals(0, LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest));
  }

  public function testCanCreatePublicHolidayLeaveRequestsForAllPublicHolidaysInTheFuture() {
    $contact = ContactFabricator::fabricate();

    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('2016-01-01'),
      new DateTime('+10 days')
    );
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

    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $creationLogic->createForAllInTheFuture();

    // It's -2 instead of -3 because the first public holiday is in the past
    // and we should not create a leave request for it
    $this->assertEquals(-2, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  public function testItDoesNotDuplicateLeaveRequestsWhenCreatingLeaveRequestsForAllPublicHolidaysInTheFuture() {
    $contact = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ], [
      'period_start_date' => '2016-01-01',
    ]);

    $date = new DateTime('+5 days');
    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => $date->format('Ymd')
    ]);

    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $creationLogic->createForAllInTheFuture();

    $this->assertEquals(1, $this->countNumberOfLeaveRequests($contact['id'], $date->format('Ymd')));

    $creationLogic->createForAllInTheFuture();

    $this->assertEquals(1, $this->countNumberOfLeaveRequests($contact['id'], $date->format('Ymd')));
  }

  public function testItDoesntCreateLeaveRequestsForAllPublicHolidaysInTheFutureIfThereIsNoMTPHLAbsenceTypes() {
    AbsenceType::del($this->absenceType->id);

    $contact = ContactFabricator::fabricate();

    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime()
    );
    $periodEntitlement->contact_id = $contact['id'];
    $periodEntitlement->type_id = $this->absenceType->id;

    $this->assertEquals(0, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' =>  CRM_Utils_Date::processDate('tomorrow')
    ]);

    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $creationLogic->createForAllInTheFuture();

    // The Balance is still 0 because no Leave Request was created
    $this->assertEquals(0, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  public function testItCreatesLeaveRequestsForAllPublicHolidaysInTheFutureOverlappingTheContractDates() {
    $contact = ContactFabricator::fabricate(['first_name' => 'Contact 1']);

    $contract = HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ], [
      'period_start_date' =>  CRM_Utils_Date::processDate('yesterday'),
      'period_end_date' =>   CRM_Utils_Date::processDate('+100 days'),
    ]);

    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime('-10 days'),
      new DateTime('+200 days')
    );
    $periodEntitlement->contact_id = $contact['id'];
    $periodEntitlement->type_id = $this->absenceType->id;

    $this->assertEquals(0, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' =>  CRM_Utils_Date::processDate('yesterday')
    ]);

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' =>  CRM_Utils_Date::processDate('+5 days')
    ]);

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' =>  CRM_Utils_Date::processDate('+47 days')
    ]);

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' =>  CRM_Utils_Date::processDate('+101 days')
    ]);

    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $creationLogic->createAllForContract($contract['id']);

    // The balance should be -2 because only two leave requests were created:
    // The one for +5 days and the other one for + 47 days.
    // The holiday for "yesterday" overlaps the contract, but it is in the past,
    // so nothing will be created. The holiday for "+101 days" is in the future,
    // but it doesn't overlap the contract dates and no leave request will be
    // created for it as well
    $this->assertEquals(-2, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  public function testItCreatesLeaveRequestsForAllPublicHolidaysInTheFutureOverlappingAContractWithNoEndDate() {
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

    $this->assertEquals(0, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' =>  CRM_Utils_Date::processDate('+5 days')
    ]);

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' =>  CRM_Utils_Date::processDate('+47 days')
    ]);

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' =>  CRM_Utils_Date::processDate('+332 days')
    ]);

    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $creationLogic->createAllForContract($contract['id']);

    // Since there's no end date for the contract,
    // leave request will be created for all the public holidays in the
    // future
    $this->assertEquals(-3, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  public function testItDoesntDuplicateLeaveRequestsWhenCreatingRequestsForAllPublicHolidaysOverlappingAContract() {
    $contact = ContactFabricator::fabricate(['first_name' => 'Contact 1']);

    $contract = HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ], [
      'period_start_date' =>  CRM_Utils_Date::processDate('yesterday'),
    ]);

    $date = new DateTime('+5 days');
    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => $date->format('Ymd')
    ]);

    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $creationLogic->createAllForContract($contract['id']);

    $this->assertEquals(1, $this->countNumberOfLeaveRequests($contact['id'], $date->format('Ymd')));

    $creationLogic->createAllForContract($contract['id']);

    $this->assertEquals(1, $this->countNumberOfLeaveRequests($contact['id'], $date->format('Ymd')));
  }

  public function testItDoesntCreateAnythingIfTheContractIDDoesntExist() {
    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $creationLogic->createAllForContract(9998398298);

    $this->assertEquals(0, $this->countNumberOfPublicHolidayBalanceChanges());
  }

  public function testItCreatesLeaveRequestsForAllContactsWithContractsOverlappingTheGivenPublicHoliday() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact1['id']
    ], [
      'period_start_date' => CRM_Utils_Date::processDate('5 days ago'),
    ]);

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact2['id']
    ], [
      'period_start_date' => CRM_Utils_Date::processDate('tomorrow'),
    ]);

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = date('Y-m-d', strtotime('+5 days'));

    $this->assertNull(LeaveRequest::findPublicHolidayLeaveRequest($contact1['id'], $publicHoliday));
    $this->assertNull(LeaveRequest::findPublicHolidayLeaveRequest($contact2['id'], $publicHoliday));

    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $creationLogic->createForAllContacts($publicHoliday);

    $leaveRequestContact1 = LeaveRequest::findPublicHolidayLeaveRequest($contact1['id'], $publicHoliday);
    $leaveRequestContact2 = LeaveRequest::findPublicHolidayLeaveRequest($contact2['id'], $publicHoliday);

    $this->assertInstanceOf(LeaveRequest::class, $leaveRequestContact1);
    $this->assertEquals($publicHoliday->date, $leaveRequestContact1->from_date);
    $this->assertInstanceOf(LeaveRequest::class, $leaveRequestContact2);
    $this->assertEquals($publicHoliday->date, $leaveRequestContact2->from_date);
  }

  public function testItDoesntCreatesLeaveRequestsForAllContactsWithoutContractsOverlappingTheGivenPublicHoliday() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact1['id']
    ], [
      'period_start_date' => CRM_Utils_Date::processDate('5 days ago'),
      'period_end_date' => CRM_Utils_Date::processDate('+5 days')
    ]);

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact2['id']
    ], [
      'period_start_date' => CRM_Utils_Date::processDate('+7 days'),
    ]);

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = date('Y-m-d', strtotime('+6 days'));

    $this->assertNull(LeaveRequest::findPublicHolidayLeaveRequest($contact1['id'], $publicHoliday));
    $this->assertNull(LeaveRequest::findPublicHolidayLeaveRequest($contact2['id'], $publicHoliday));

    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $creationLogic->createForAllContacts($publicHoliday);

    $this->assertNull(LeaveRequest::findPublicHolidayLeaveRequest($contact1['id'], $publicHoliday));
    $this->assertNull(LeaveRequest::findPublicHolidayLeaveRequest($contact2['id'], $publicHoliday));
  }

  public function testItDoesntDuplicateLeaveRequestsWhenCreatingRequestsForAllContactsWithContractsOverlappingAPublicHoliday() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact1['id']
    ], [
      'period_start_date' => CRM_Utils_Date::processDate('5 days ago'),
    ]);

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact2['id']
    ], [
      'period_start_date' => CRM_Utils_Date::processDate('today'),
    ]);

    $date = new DateTime('+5 days');
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = $date->format('Y-m-d');

    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $creationLogic->createForAllContacts($publicHoliday);

    $this->assertEquals(1, $this->countNumberOfLeaveRequests($contact1['id'], $date->format('Ymd')));
    $this->assertEquals(1, $this->countNumberOfLeaveRequests($contact2['id'], $date->format('Ymd')));

    $creationLogic->createForAllContacts($publicHoliday);

    $this->assertEquals(1, $this->countNumberOfLeaveRequests($contact1['id'], $date->format('Ymd')));
    $this->assertEquals(1, $this->countNumberOfLeaveRequests($contact2['id'], $date->format('Ymd')));
  }

  private function countNumberOfPublicHolidayBalanceChanges() {
    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id'));

    $bao = new LeaveBalanceChange();
    $bao->type_id = $balanceChangeTypes['Public Holiday'];
    $bao->source_type = LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY;

    return $bao->count();
  }

  private function countNumberOfLeaveRequests($contactID, $date) {
    $bao = new LeaveRequest();
    $bao->contact_id = $contactID;
    $bao->from_date = $date;

    return $bao->count();
  }
}
