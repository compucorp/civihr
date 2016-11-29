<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeaveRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeaveRequestTest extends BaseHeadlessTest {

  /**
   * @var CRM_HRLeaveAndAbsences_BAO_AbsenceType
   */
  private $absenceType;

  public function setUp() {
    // In order to make tests simpler, we disable the foreign key checks,
    // as a way to allow the creation of leave request records related
    // to a non-existing leave period entitlement
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

    // We delete everything two avoid problems with the default absence types
    // created during the extension installation
    $tableName = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");

    // This is needed for the tests regarding public holiday leave requests
    $this->absenceType = AbsenceTypeFabricator::fabricate([
      'must_take_public_holiday_as_leave' => 1
    ]);
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");
  }

  public function testALeaveRequestWithOnlyTheStartDateShouldCreateOnlyOneLeaveRequestDate()
  {
    $fromDate = new DateTime();
    $leaveRequest = LeaveRequest::create([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1, //The status is not important here. We just need a value to be stored in the DB
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1 //The type is not important here. We just need a value to be stored in the DB
    ]);

    $dates = $leaveRequest->getDates();
    $this->assertCount(1, $dates);
    $this->assertEquals($fromDate->format('Y-m-d'), $dates[0]->date);
  }

  public function testALeaveRequestWithStartAndEndDatesShouldCreateMultipleLeaveRequestDates()
  {
    $fromDate = new DateTime();
    $toDate = new DateTime('+3 days');
    $leaveRequest = LeaveRequest::create([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1, //The status is not important here. We just need a value to be stored in the DB
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1, //The type is not important here. We just need a value to be stored in the DB
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1 //The type is not important here. We just need a value to be stored in the DB
    ]);

    $dates = $leaveRequest->getDates();
    $this->assertCount(4, $dates);
    $this->assertEquals($fromDate->format('Y-m-d'), $dates[0]->date);
    $this->assertEquals(date('Y-m-d', strtotime('+1 day')), $dates[1]->date);
    $this->assertEquals(date('Y-m-d', strtotime('+2 days')), $dates[2]->date);
    $this->assertEquals($toDate->format('Y-m-d'), $dates[3]->date);
  }

  public function testUpdatingALeaveRequestShouldNotDuplicateTheLeaveRequestDates()
  {
    $fromDate = new DateTime();
    $leaveRequest = LeaveRequest::create([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1
    ]);

    $dates = $leaveRequest->getDates();
    $this->assertCount(1, $dates);
    $this->assertEquals($fromDate->format('Y-m-d'), $dates[0]->date);

    $fromDate = $fromDate->modify('+1 day');
    $toDate = clone $fromDate;
    $toDate->modify('+1 day');

    $leaveRequest = LeaveRequest::create([
      'id' => $leaveRequest->id,
      'from_date' => $fromDate->format('YmdHis'),
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
    ]);

    $dates = $leaveRequest->getDates();
    $this->assertCount(2, $dates);
    $this->assertEquals($fromDate->format('Y-m-d'), $dates[0]->date);
    $this->assertEquals($toDate->format('Y-m-d'), $dates[1]->date);
  }

  public function testCanFindAPublicHolidayLeaveRequestForAContact() {
    $contactID = 2;

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = '2016-01-01';

    $this->assertNull(LeaveRequest::findPublicHolidayLeaveRequest($contactID, $publicHoliday));

    PublicHolidayLeaveRequestFabricator::fabricate($contactID, $publicHoliday);

    $leaveRequest = LeaveRequest::findPublicHolidayLeaveRequest($contactID, $publicHoliday);
    $this->assertInstanceOf(LeaveRequest::class, $leaveRequest);
    $this->assertEquals($publicHoliday->date, $leaveRequest->from_date);
    $this->assertEquals($contactID, $leaveRequest->contact_id);
  }

  public function testShouldReturnNullIfItCantFindAPublicHolidayLeaveRequestForAContact() {
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = '2016-01-03';

    $this->assertNull(LeaveRequest::findPublicHolidayLeaveRequest(3, $publicHoliday));
  }

  public function testGetBalanceChangeByAbsenceTypeShouldIncludeBalanceForAllAbsenceTypes() {
    $contact = ContactFabricator::fabricate();

    $absenceType1 = AbsenceTypeFabricator::fabricate();
    $absenceType2 = AbsenceTypeFabricator::fabricate();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType1->id
    ]);

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType2->id
    ]);

    LeaveRequestFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $absenceType1->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+5 days')
    ], true);

    LeaveRequestFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $absenceType1->id,
      'from_date' => CRM_Utils_Date::processDate('+8 days'),
      'to_date' => CRM_Utils_Date::processDate('+9 days')
    ], true);

    LeaveRequestFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $absenceType2->id,
      'from_date' => CRM_Utils_Date::processDate('+20 days'),
      'to_date' => CRM_Utils_Date::processDate('+35 days')
    ], true);

    $result = LeaveRequest::getBalanceChangeByAbsenceType($contact['id'], $absencePeriod->id);

    $expectedResult = [
      $absenceType1->id => -7,
      $absenceType2->id => -16,
    ];

    $this->assertEquals($expectedResult, $result);
  }

  public function testGetBalanceChangeByAbsenceTypeShouldShouldReturn0ForAnAbsenceTypeWithNoLeaveRequests() {
    $contact = ContactFabricator::fabricate();

    $absenceType1 = AbsenceTypeFabricator::fabricate();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType1->id
    ]);

    $result = LeaveRequest::getBalanceChangeByAbsenceType($contact['id'], $absencePeriod->id);

    $expectedResult = [ $absenceType1->id => 0];

    $this->assertEquals($expectedResult, $result);
  }

  public function testGetBalanceChangeByAbsenceTypeCanReturnTheBalanceForLeaveRequestsWithSpecificsStatuses() {
    $contact = ContactFabricator::fabricate();

    $absenceType = AbsenceTypeFabricator::fabricate();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType->id,
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    LeaveRequestFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    LeaveRequestFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+3 days'),
      'to_date' => CRM_Utils_Date::processDate('+5 days'),
      'status_id' => $leaveRequestStatuses['More Information Requested']
    ], true);

    LeaveRequestFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+6 days'),
      'to_date' => CRM_Utils_Date::processDate('+9 days'),
      'status_id' => $leaveRequestStatuses['Cancelled']
    ], true);

    $result = LeaveRequest::getBalanceChangeByAbsenceType(
      $contact['id'],
      $absencePeriod->id,
      [$leaveRequestStatuses['Waiting Approval']]
    );
    $expectedResult = [$absenceType->id => -2];
    $this->assertEquals($expectedResult, $result);

    $result = LeaveRequest::getBalanceChangeByAbsenceType(
      $contact['id'],
      $absencePeriod->id,
      [$leaveRequestStatuses['More Information Requested']]
    );
    $expectedResult = [$absenceType->id => -3];
    $this->assertEquals($expectedResult, $result);

    $result = LeaveRequest::getBalanceChangeByAbsenceType(
      $contact['id'],
      $absencePeriod->id,
      [
        $leaveRequestStatuses['Waiting Approval'],
        $leaveRequestStatuses['More Information Requested'],
        $leaveRequestStatuses['Cancelled'],
      ]
    );
    $expectedResult = [$absenceType->id => -9];
    $this->assertEquals($expectedResult, $result);
  }

}
