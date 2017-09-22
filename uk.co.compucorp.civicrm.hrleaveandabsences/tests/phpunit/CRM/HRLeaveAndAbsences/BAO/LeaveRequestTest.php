<?php

use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeaveRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeaveRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeavePeriodEntitlementHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_MailHelpersTrait;

  /**
   * @var CRM_HRLeaveAndAbsences_BAO_AbsenceType
   */
  private $absenceType;

  public function setUp() {
    // In order to make tests simpler, we disable the foreign key checks,
    // as a way to allow the creation of leave request records related
    // to a non-existing leave period entitlement
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');

    // We delete everything two avoid problems with the default absence types
    // created during the extension installation
    $tableName = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");

    $messageSpoolTable = CRM_Mailing_BAO_Spool::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$messageSpoolTable}");

    // This is needed for the tests regarding public holiday leave requests
    $this->absenceType = AbsenceTypeFabricator::fabricate([
      'must_take_public_holiday_as_leave' => 1
    ]);
    $this->leaveRequestDayTypes = $this->getLeaveRequestDayTypes();
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 1;');
  }

  public function testALeaveRequestWithSameStartAndEndDateShouldCreateOnlyOneLeaveRequestDate() {
    $fromDate = new DateTime();
    $date = $fromDate->format('YmdHis');
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1, //The status is not important here. We just need a value to be stored in the DB
      'from_date' => $date,
      'from_date_type' => 1,
      'to_date' => $date,
      'to_date_type' => 1,
    ]);

    $dates = $leaveRequest->getDates();
    $this->assertCount(1, $dates);
    $this->assertEquals($fromDate->format('Y-m-d'), $dates[0]->date);
  }

  public function testALeaveRequestWithStartAndEndDatesShouldCreateMultipleLeaveRequestDates() {
    $fromDate = new DateTime();
    $toDate = new DateTime('+3 days');
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
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

  public function testUpdatingALeaveRequestShouldNotDuplicateTheLeaveRequestDates() {
    $fromDate = new DateTime();
    $date = $fromDate->format('YmdHis');
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $date,
      'from_date_type' => 1,
      'to_date' => $date,
      'to_date_type' => 1
    ]);

    $dates = $leaveRequest->getDates();
    $this->assertCount(1, $dates);
    $this->assertEquals($fromDate->format('Y-m-d'), $dates[0]->date);

    $fromDate = $fromDate->modify('+1 day');
    $toDate = clone $fromDate;
    $toDate->modify('+1 day');

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'id' => $leaveRequest->id,
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
    ]);

    $dates = $leaveRequest->getDates();
    $this->assertCount(2, $dates);
    $this->assertEquals($fromDate->format('Y-m-d'), $dates[0]->date);
    $this->assertEquals($toDate->format('Y-m-d'), $dates[1]->date);
  }

  public function testUpdatingALeaveRequestShouldNotThrowOverLappingLeaveRequestExceptionWhenItOnlyOverlapsWithItself() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => '2016-01-01']
    );

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 20);

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => 1,
      'pattern_id' => $workPattern->id
    ]);
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => CRM_Utils_Date::processDate('2016-11-02'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-11-04'),
      'to_date_type' => 1
    ], true);

    //updating leave request
    $leaveRequest2 = LeaveRequest::create([
      'id' => $leaveRequest1->id,
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-03'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-11-05'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
    $this->assertInstanceOf(LeaveRequest::class, $leaveRequest2);
  }

  public function testCanFindAPublicHolidayLeaveRequestForAContact() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-01')
    ]);

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

  public function testCalculateBalanceChangeForALeaveRequestForAContact() {
    $periodStartDate = date('Y-01-01');

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => $periodStartDate]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contract['contact_id'],
      'pattern_id' => $workPattern->id
    ]);

    $fromDate = new DateTime('2016-11-13');
    $toDate = new DateTime('2016-11-15');
    $fromType = $this->leaveRequestDayTypes['half_day_am']['value'];
    $toType = $this->leaveRequestDayTypes['half_day_am']['value'];

    $expectedResultsBreakdown = [
      'amount' => 0,
      'breakdown' => []
    ];

    // Start date is a sunday, Weekend
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-11-13',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['weekend']['id'],
        'value' => $this->leaveRequestDayTypes['weekend']['value'],
        'label' => $this->leaveRequestDayTypes['weekend']['label']
      ]
    ];

    // The next day is a monday, which is a working day
    $expectedResultsBreakdown['amount'] += 1;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-11-14',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['all_day']['id'],
        'value' => $this->leaveRequestDayTypes['all_day']['value'],
        'label' => $this->leaveRequestDayTypes['all_day']['label']
      ]
    ];

    // last day is a tuesday, which is a working day, half day will be deducted
    $expectedResultsBreakdown['amount'] += 0.5;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-11-15',
      'amount' => 0.5,
      'type' => [
        'id' => $this->leaveRequestDayTypes['half_day_am']['id'],
        'value' => $this->leaveRequestDayTypes['half_day_am']['value'],
        'label' => $this->leaveRequestDayTypes['half_day_am']['label']
      ]
    ];

    $expectedResultsBreakdown['amount'] *= -1;

    $result = LeaveRequest::calculateBalanceChange($contract['contact_id'], $fromDate, $fromType, $toDate, $toType);
    $this->assertEquals($expectedResultsBreakdown, $result);
  }

  public function testCalculateBalanceChangeWhenOneOfTheRequestedLeaveDaysIsAPublicHoliday() {
    $periodStartDate = date('2016-01-01');

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => $periodStartDate]
    );

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-30')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' => $this->absenceType->id,
    ]);

    //create a public holiday for a date that is between the leave request days
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = date('2016-11-14');

    $this->assertNull(LeaveRequest::findPublicHolidayLeaveRequest($periodEntitlement->contact_id, $publicHoliday));
    PublicHolidayLeaveRequestFabricator::fabricate($periodEntitlement->contact_id, $publicHoliday);

    $fromDate = new DateTime('2016-11-14');
    $toDate = new DateTime('2016-11-15');
    $fromType = $this->leaveRequestDayTypes['all_day']['value'];
    $toType = $this->leaveRequestDayTypes['all_day']['value'];

    $expectedResultsBreakdown = [
      'amount' => 0,
      'breakdown' => []
    ];

    // Starting date is a monday, but a public holiday
    $expectedResultsBreakdown['amount'] += 0;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-11-14',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['public_holiday']['id'],
        'value' => $this->leaveRequestDayTypes['public_holiday']['value'],
        'label' => $this->leaveRequestDayTypes['public_holiday']['label']
      ]
    ];

    // last day is a tuesday, which is a working day
    $expectedResultsBreakdown['amount'] += 1.0;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-11-15',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['all_day']['id'],
        'value' => $this->leaveRequestDayTypes['all_day']['value'],
        'label' => $this->leaveRequestDayTypes['all_day']['label']
      ]
    ];

    $expectedResultsBreakdown['amount'] *= -1;

    $result = LeaveRequest::calculateBalanceChange($periodEntitlement->contact_id, $fromDate, $fromType, $toDate, $toType);
    $this->assertEquals($expectedResultsBreakdown, $result);
  }

  public function testCalculateBalanceChangeForALeaveRequestForAContactWithMultipleWeeks() {
    $periodStartDate = new DateTime('2016-01-01');

    $contract = HRJobContractFabricator::fabricate(
      [ 'contact_id' => 1 ],
      [ 'period_start_date' => $periodStartDate->format('Y-m-d') ]
    );

    // Week 1 weekdays: monday, wednesday and friday
    // Week 2 weekdays: tuesday and thursday
    $pattern = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contract['contact_id'],
      'pattern_id' => $pattern->id,
      'effective_date' => $periodStartDate->format('YmdHis')
    ]);

    $fromDate = new DateTime('2016-07-31');
    $toDate = new DateTime('2016-08-15');
    $fromType = $this->leaveRequestDayTypes['all_day']['value'];
    $toType = $this->leaveRequestDayTypes['half_day_am']['value'];

    $expectedResultsBreakdown = [
      'amount' => 0,
      'breakdown' => []
    ];

    // Start day (2016-07-31), a sunday
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-07-31',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['weekend']['id'],
        'value' => $this->leaveRequestDayTypes['weekend']['value'],
        'label' => $this->leaveRequestDayTypes['weekend']['label']
      ]
    ];

    // Since the start date is a sunday, the end of the week, the following day
    // (2016-08-01) should be on the second week. Monday of the second week is
    // not a working day
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-01',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['non_working_day']['id'],
        'value' => $this->leaveRequestDayTypes['non_working_day']['value'],
        'label' => $this->leaveRequestDayTypes['non_working_day']['label']
      ]
    ];

    // The next day is a tuesday, which is a working day on the second week, so
    $expectedResultsBreakdown['amount'] += 1;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-02',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['all_day']['id'],
        'value' => $this->leaveRequestDayTypes['all_day']['value'],
        'label' => $this->leaveRequestDayTypes['all_day']['label']
      ]
    ];

    // Wednesday is not a working day on the second week
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-03',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['non_working_day']['id'],
        'value' => $this->leaveRequestDayTypes['non_working_day']['value'],
        'label' => $this->leaveRequestDayTypes['non_working_day']['label']
      ]
    ];

    // Thursday is a working day on the second week
    $expectedResultsBreakdown['amount'] += 1;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-04',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['all_day']['id'],
        'value' => $this->leaveRequestDayTypes['all_day']['value'],
        'label' => $this->leaveRequestDayTypes['all_day']['label']
      ]
    ];

    // Friday, Saturday and Sunday are not working days on the second week,
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-05',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['non_working_day']['id'],
        'value' => $this->leaveRequestDayTypes['non_working_day']['value'],
        'label' => $this->leaveRequestDayTypes['non_working_day']['label']
      ]
    ];

    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-06',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['weekend']['id'],
        'value' => $this->leaveRequestDayTypes['weekend']['value'],
        'label' => $this->leaveRequestDayTypes['weekend']['label']
      ]
    ];

    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-07',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['weekend']['id'],
        'value' => $this->leaveRequestDayTypes['weekend']['value'],
        'label' => $this->leaveRequestDayTypes['weekend']['label']
      ]
    ];

    // Now, since we hit sunday, the following day will be on the third week
    // since the start date, but the work pattern only has 2 weeks, so we
    // rotate back to use the week 1 from the pattern

    // Monday is a working day on the first week
    $expectedResultsBreakdown['amount'] += 1;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-08',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['all_day']['id'],
        'value' => $this->leaveRequestDayTypes['all_day']['value'],
        'label' => $this->leaveRequestDayTypes['all_day']['label']
      ]
    ];

    // Tuesday is not a working day on the first week
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-09',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['non_working_day']['id'],
        'value' => $this->leaveRequestDayTypes['non_working_day']['value'],
        'label' => $this->leaveRequestDayTypes['non_working_day']['label']
      ]
    ];
    // Wednesday is a working day on the first week
    $expectedResultsBreakdown['amount'] += 1;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-10',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['all_day']['id'],
        'value' => $this->leaveRequestDayTypes['all_day']['value'],
        'label' => $this->leaveRequestDayTypes['all_day']['label']
      ]
    ];
    // Thursday is not a working day on the first week
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-11',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['non_working_day']['id'],
        'value' => $this->leaveRequestDayTypes['non_working_day']['value'],
        'label' => $this->leaveRequestDayTypes['non_working_day']['label']
      ]
    ];

    // Friday is a working day on the first week
    $expectedResultsBreakdown['amount'] += 1;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-12',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['all_day']['id'],
        'value' => $this->leaveRequestDayTypes['all_day']['value'],
        'label' => $this->leaveRequestDayTypes['all_day']['label']
      ]
    ];

    // Saturday and Sunday are not working days on the first week
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-13',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['weekend']['id'],
        'value' => $this->leaveRequestDayTypes['weekend']['value'],
        'label' => $this->leaveRequestDayTypes['weekend']['label']
      ]
    ];

    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-14',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['weekend']['id'],
        'value' => $this->leaveRequestDayTypes['weekend']['value'],
        'label' => $this->leaveRequestDayTypes['weekend']['label']
      ]
    ];
    // Hit sunday again, so we are now on the fourth week since the start date.
    // The work pattern will rotate and use the week 2

    // Monday is not a working day on week 2
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-15',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['non_working_day']['id'],
        'value' => $this->leaveRequestDayTypes['non_working_day']['value'],
        'label' => $this->leaveRequestDayTypes['non_working_day']['label']
      ]
    ];
    $expectedResultsBreakdown['amount'] *= -1;

    $result = LeaveRequest::calculateBalanceChange($contract['contact_id'], $fromDate, $fromType, $toDate, $toType);
    $this->assertEquals($expectedResultsBreakdown, $result);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The from_date field should not be empty
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutAStartDate() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The to_date field should not be empty
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutAnEndDate() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+4 days'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The contact_id field should not be empty
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutContactID() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+4 days'),
      'from_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The type_id field should not be empty
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutTypeID() {
    LeaveRequest::create([
      'status_id' => 1,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+4 days'),
      'from_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The status_id field should not be empty
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutStatusID() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+4 days'),
      'from_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The to_date_type field should not be empty
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutToDateType() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('now'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The from_date_type field should not be empty
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutFromDateType() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('now'),
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The request_type field should not be empty
   */
  public function testLeaveRequestCanNotBeCreatedWhenRequestTypeIsEmpty() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value']
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Leave Request start date cannot be greater than the end date
   */
  public function testALeaveRequestEndDateShouldNotBeGreaterThanStartDate() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+4 days'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('now'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Absence Type is not active
   */
  public function testLeaveRequestShouldNotBeCreatedWhenAbsenceTypeIsNotActive() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'is_active' => 0
    ]);

    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('now'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  public function testNumberOfDaysOfLeaveRequestShouldNotBeGreaterMaxConsecutiveLeaveDaysForAbsenceType() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'max_consecutive_leave_days' => 2.5
    ]);

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException',
      'Only a maximum 2.5 days leave can be taken in one request. Please modify days of this request'
    );

    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('now'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  public function testAUserCannotCancelOwnLeaveRequestWhenAbsenceTypeDoesNotAllowIt() {
    $contactID = 5;
    $this->registerCurrentLoggedInContactInSession($contactID);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_request_cancelation' => AbsenceType::REQUEST_CANCELATION_NO
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => CRM_Utils_Date::processDate('now'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1
    ]);

    $this->setExpectedException('CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException', 'Absence Type does not allow leave request cancellation');
    //cancel leave request
    LeaveRequest::create([
      'id' => $leaveRequest->id,
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['cancelled'],
      'from_date' => CRM_Utils_Date::processDate('now'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  public function testAUserCannotCancelOwnLeaveRequestWhenAbsenceTypeAllowsItInAdvanceOfStartDateAndFromDateIsLessThanToday() {
    $contactID = 5;
    $this->registerCurrentLoggedInContactInSession($contactID);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_request_cancelation' => AbsenceType::REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => CRM_Utils_Date::processDate('-1 day'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1
    ]);

    $this->setExpectedException('CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException', 'Leave Request with past days cannot be cancelled');
    //cancel leave request
    LeaveRequest::create([
      'id' => $leaveRequest->id,
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['cancelled'],
      'from_date' => CRM_Utils_Date::processDate('-1 day'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('+4 days'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  public function testFindOverlappingLeaveRequestsForOneOverlappingLeaveRequest() {
    $contactID = 1;
    $fromDate1 = new DateTime('2016-11-02');
    $toDate1 = new DateTime('2016-11-04');

    $fromDate2 = new DateTime('2016-11-05');
    $toDate2 = new DateTime('2016-11-10');

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate1->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate1->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    //The start date and end date has dates in only leaveRequest1
    $startDate = '2016-11-01';
    $endDate = '2016-11-03';

    $overlappingRequests = LeaveRequest::findOverlappingLeaveRequests($contactID, $startDate, $endDate);
    $this->assertCount(1, $overlappingRequests);
    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests[0]);
    $this->assertEquals($leaveRequest1->id, $overlappingRequests[0]->id);
  }

  public function testManagerCanCancelOrRejectLeaveRequestEvenIfBalanceIsGreaterThanEntitlementBalanceWhenAllowOveruseFalse() {
    $manager = ContactFabricator::fabricate();
    $staff = ContactFabricator::fabricate();
    $periodStartDate = CRM_Utils_Date::processDate('2016-01-01');
    $periodEndDate = CRM_Utils_Date::processDate('2016-12-31');
    $requestDate = CRM_Utils_Date::processDate('2016-06-14');
    $requestDateType = $this->leaveRequestDayTypes['all_day']['value'];

    $this->registerCurrentLoggedInContactInSession($manager['id']);
    $this->setContactAsLeaveApproverOf($manager, $staff);

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => $periodStartDate,
      'end_date'   => $periodEndDate,
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate(['allow_overuse' => 0]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => $staff['id'],
      'period_id' => $absencePeriod->id
    ]);

    $entitlementBalanceChange = 0;
    $this->createLeaveBalanceChange($periodEntitlement->id, $entitlementBalanceChange);
    $periodStartDate = $absencePeriod->start_date;

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => $periodStartDate]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    $leaveRequestData = [
      'type_id' => $absenceType->id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => $requestDate,
      'from_date_type' => $requestDateType,
      'to_date' => $requestDate,
      'to_date_type' => $requestDateType,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];

    // Testing leave request rejection and cancelling
    foreach (LeaveRequest::getCancelledStatuses() as $statusId) {
      // Create new request with Awaiting Approval status
      $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($leaveRequestData);
      // Change status of the leave request
      $updatedLeaveRequestData = $leaveRequestData;
      $updatedLeaveRequestData['id'] = $leaveRequest->id;
      $updatedLeaveRequestData['status_id'] = $statusId;
      $updatedLeaveRequest = LeaveRequest::create($updatedLeaveRequestData);

      $this->assertEquals($updatedLeaveRequest->status_id, $statusId);
    }
  }

  public function testFindOverlappingLeaveRequestsForMoreThanOneOverlappingLeaveRequests() {
    $contactID = 1;
    $fromDate1 = new DateTime('2016-11-02');
    $toDate1 = new DateTime('2016-11-04');

    $fromDate2 = new DateTime('2016-11-05');
    $toDate2 = new DateTime('2016-11-10');

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate1->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate1->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    //The start date and end date has dates in both leave request dates in both leaveRequest1 and leaveRequest2
    $startDate = '2016-11-01';
    $endDate = '2016-11-06';

    $overlappingRequests = LeaveRequest::findOverlappingLeaveRequests($contactID, $startDate, $endDate);
    $this->assertCount(2, $overlappingRequests);
    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests[0]);
    $this->assertEquals($leaveRequest1->id, $overlappingRequests[0]->id);

    $this->assertEquals($leaveRequest2->id, $overlappingRequests[1]->id);
    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests[1]);
  }

  public function testFindOverlappingLeaveRequestsDoesNotCountSoftDeletedLeaveRequestAsOverlappingLeaveRequest() {
    $contactID = 1;
    $fromDate = new DateTime('2016-11-01');

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $fromDate->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    LeaveRequest::softDelete($leaveRequest->id);

    //The start date and end date has dates in leave request dates in leaveRequest
    //leaveRequest has been soft deleted and will not be counted as an overlapping leave request
    $startDate = '2016-11-01';
    $endDate = '2016-11-02';

    $overlappingRequests = LeaveRequest::findOverlappingLeaveRequests($contactID, $startDate, $endDate);
    $this->assertCount(0, $overlappingRequests);
  }

  public function testFindOverlappingLeaveRequestsForMultipleOverlappingLeaveRequestAndExcludePublicHolidayTrue() {
    $contactID = 1;
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = '2016-11-11';

    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    PublicHolidayLeaveRequestFabricator::fabricate($contactID, $publicHoliday);

    $fromDate1 = new DateTime('2016-11-02');
    $toDate1 = new DateTime('2016-11-04');

    $fromDate2 = new DateTime('2016-11-05');
    $toDate2 = new DateTime('2016-11-10');

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate1->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate1->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    //The start date and end date has dates in both leave request dates in both leaveRequest1 and leaveRequest2
    //public holiday is excluded by default
    $startDate = '2016-11-01';
    $endDate = '2016-11-12';
    $overlappingRequests = LeaveRequest::findOverlappingLeaveRequests($contactID, $startDate, $endDate);
    $this->assertCount(2, $overlappingRequests);
    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests[0]);
    $this->assertEquals($leaveRequest1->id, $overlappingRequests[0]->id);

    $this->assertEquals($leaveRequest2->id, $overlappingRequests[1]->id);
    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests[1]);
  }

  public function testFindOverlappingLeaveRequestsForMultipleOverlappingLeaveRequestAndExcludePublicHolidayFalse() {
    $contactID = 1;
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = '2016-11-11';

    $fromDate1 = new DateTime('2016-11-02');
    $toDate1 = new DateTime('2016-11-04');

    $fromDate2 = new DateTime('2016-11-05');
    $toDate2 = new DateTime('2016-11-10');

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate1->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate1->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    PublicHolidayLeaveRequestFabricator::fabricate($contactID, $publicHoliday);
    $publicHolidayLeaveRequest = LeaveRequest::findPublicHolidayLeaveRequest($contactID, $publicHoliday);

    //The start date and end date has dates in both leave request dates in both leaveRequest1,
    //leaveRequest2 and public holiday
    $startDate = '2016-11-01';
    $endDate = '2016-11-12';
    $overlappingRequests = LeaveRequest::findOverlappingLeaveRequests($contactID, $startDate, $endDate, [], false);
    $this->assertCount(3, $overlappingRequests);
    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests[0]);
    $this->assertEquals($leaveRequest1->id, $overlappingRequests[0]->id);

    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests[1]);
    $this->assertEquals($leaveRequest2->id, $overlappingRequests[1]->id);

    $this->assertEquals($publicHolidayLeaveRequest->id, $overlappingRequests[2]->id);
    $this->assertEquals($publicHolidayLeaveRequest->id, $overlappingRequests[2]->id);
  }

  public function testFindOverlappingLeaveRequestsFilteredBySpecificStatusesAndPublicHolidayCondition() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);
    $contactID = 1;
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = '2016-11-11';

    $fromDate1 = new DateTime('2016-11-02');
    $toDate1 = new DateTime('2016-11-04');

    $fromDate2 = new DateTime('2016-11-05');
    $toDate2 = new DateTime('2016-11-10');

    $fromDate3 = new DateTime('2016-11-12');
    $toDate3 = new DateTime('2016-11-15');

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));
    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => $fromDate1->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate1->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['rejected'],
      'from_date' => $fromDate3->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate3->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    PublicHolidayLeaveRequestFabricator::fabricate($contactID, $publicHoliday);

    //The start date and end date has dates in leave request dates for leaveRequest1, leaveRequest2
    //leaveRequest3 and PublicHolidayLeaveRequest, but we have filtered by only 'More Information Required'
    //therefore only one overlapping Leave Request is expected
    $startDate = '2016-11-02';
    $endDate = '2016-11-15';
    $filterStatus = [$leaveRequestStatuses['more_information_required']];
    $overlappingRequests = LeaveRequest::findOverlappingLeaveRequests($contactID, $startDate, $endDate, $filterStatus);
    $this->assertCount(1, $overlappingRequests);
    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests[0]);
    $this->assertEquals($leaveRequest2->id, $overlappingRequests[0]->id);

    //The start date and end date has dates in leave request dates for leaveRequest1, leaveRequest2,
    //leaveRequest3 and PublicHolidayLeaveRequest, but we have filtered by only 'More Information Required' and 'Awaiting Approval'
    //and overlapping public holiday leave requests is not excluded.
    //However two leave request is expected because, Public holiday leave requests have status 'Admin Approved' by default
    $startDate = '2016-11-01';
    $endDate = '2016-11-16';
    $filterStatus = [$leaveRequestStatuses['more_information_required'], $leaveRequestStatuses['awaiting_approval']];
    $overlappingRequests2 = LeaveRequest::findOverlappingLeaveRequests($contactID, $startDate, $endDate, $filterStatus, false);
    $this->assertCount(2, $overlappingRequests2);
    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests[0]);
    $this->assertEquals($leaveRequest1->id, $overlappingRequests2[0]->id);

    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests2[1]);
    $this->assertEquals($leaveRequest2->id, $overlappingRequests2[1]->id);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage This leave request overlaps with another request. Please modify dates of this request
   */
  public function testALeaveRequestShouldNotBeCreatedWhenThereAreOverlappingLeaveRequests() {
    $contactID = 1;
    $fromDate1 = new DateTime('2016-11-02');
    $toDate1 = new DateTime('2016-11-04');

    $fromDate2 = new DateTime('2016-11-05');
    $toDate2 = new DateTime('2016-11-10');

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => $fromDate1->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate1->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['rejected'],
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    //from date and to date have date in both leave request
    $fromDate = new DateTime('2016-11-03');
    $toDate = new DateTime('2016-11-05');

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  public function testLeaveRequestCanBeCreatedWhenThereIsAnOverlappingPublicHolidayLeaveRequest() {
    $contactID = 1;
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = '2016-11-11';

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => '2016-01-01']
    );

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 20);

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contactID,
      'pattern_id' => $workPattern->id
    ]);

    PublicHolidayLeaveRequestFabricator::fabricate($contactID, $publicHoliday);

    //this date overlaps with public holiday and a Rejected status leave request
    $fromDate = new DateTime('2016-11-05');
    $toDate = new DateTime('2016-11-11');
    $leaveRequest = LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
    $this->assertEquals($leaveRequest->from_date, $fromDate->format('YmdHis'));
  }


  public function testLeaveRequestCanBeCreatedWhenThereAreNoOverlappingLeaveRequestsWithApprovedStatus() {
    $contactID = 1;

    $fromDate2 = new DateTime('2016-11-05');
    $toDate2 = new DateTime('2016-11-10');

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => '2016-01-01']
    );

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 20);

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contactID,
      'pattern_id' => $workPattern->id
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['rejected'],
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    //this date overlaps with a Rejected status leave request
    $fromDate = new DateTime('2016-11-05');
    $toDate = new DateTime('2016-11-11');
    $leaveRequest = LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
    $this->assertEquals($leaveRequest->from_date, $fromDate->format('YmdHis'));
  }

  public function testLeaveRequestCannotBeCreatedWhenBalanceChangeGreaterThanPeriodEntitlementBalanceChangeWhenAllowOveruseFalse() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
      'allow_overuse' => 0
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $entitlementBalanceChange = 3;
    $this->createLeaveBalanceChange($periodEntitlement->id, $entitlementBalanceChange);
    $periodStartDate = date('2016-01-01');

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => $periodStartDate]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $periodEntitlement->contact_id,
      'pattern_id' => $workPattern->id
    ]);

    $fromDate = new DateTime('2016-11-14');
    $toDate = new DateTime('2016-11-17');
    $fromType = $this->leaveRequestDayTypes['all_day']['value'];
    $toType = $this->leaveRequestDayTypes['all_day']['value'];

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException',
      'There are only '. $entitlementBalanceChange. ' days leave available. This request cannot be made or approved'
    );

    //four working days which will create a balance change of 4 and is greater than entitlement balance
    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  public function testLeaveRequestWithHalfDaysCanBeCreatedWhenBalanceChangeIsEqualToTheRemainingBalanceWhenAbsenceTypeDoesntAllowOveruse() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 1.5);
    $periodStartDate = date('2016-01-01');

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => $periodStartDate]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $periodEntitlement->contact_id,
      'pattern_id' => $workPattern->id
    ]);

    //two working days, but the second day is half, so the balance change
    //will be 1.5, exactly the same as the remaining balance (which, since we
    //don't have any other deductions, it's the same as the entitlement)
    $leaveRequest = LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-14'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-11-15'),
      'to_date_type' => $this->leaveRequestDayTypes['half_day_am']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
    $this->assertNotNull($leaveRequest->id);
  }

  public function testLeaveRequestCanBeCreatedWhenBalanceChangeGreaterThanPeriodBalanceChangeAndAbsenceTypeAllowOveruseTrue() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
      'allow_overuse' => 1
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 3);
    $periodStartDate = date('2016-01-01');

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => $periodStartDate]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $periodEntitlement->contact_id,
      'pattern_id' => $workPattern->id
    ]);

    $fromDate = new DateTime('2016-11-14');
    $toDate = new DateTime('2016-11-17');
    $fromType = $this->leaveRequestDayTypes['all_day']['value'];
    $toType = $this->leaveRequestDayTypes['all_day']['value'];

    //four working days which will create a balance change of 4
    $leaveRequest = LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
    $this->assertEquals($leaveRequest->from_date, $fromDate->format('YmdHis'));
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Leave Request must have at least one working day to be created
   */
  public function testLeaveRequestCanNotBeCreatedWhenLeaveRequestHasNoWorkingDay() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 3);
    $periodStartDate = date('2016-01-01');

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => $periodStartDate]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $periodEntitlement->contact_id,
      'pattern_id' => $workPattern->id
    ]);

    //both days are on weekends
    $fromDate = new DateTime('2016-11-12');
    $toDate = new DateTime('2016-11-13');
    $fromType = $this->leaveRequestDayTypes['all_day']['value'];
    $toType = $this->leaveRequestDayTypes['all_day']['value'];

    LeaveRequest::create([
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Contact does not have period entitlement for the absence type
   */
  public function testLeaveRequestCanNotBeCreatedWhenContactHasNoPeriodEntitlementForTheAbsenceType() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $contactID = 1;
    $leaveDate = new DateTime('2016-11-15');
    $dateType = $this->leaveRequestDayTypes['all_day']['value'];

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $leaveDate->format('YmdHis'),
      'from_date_type' => $dateType,
      'to_date' => $leaveDate->format('YmdHis'),
      'to_date_type' => $dateType,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Leave Request must have at least one working day to be created
   */
  public function testLeaveRequestCanNotBeCreatedWhenLeaveRequestDateIsAPublicHoliday() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 3);
    $periodStartDate = date('2016-01-01');

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => $periodStartDate]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $periodEntitlement->contact_id,
      'pattern_id' => $workPattern->id
    ]);

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = '2016-11-16';
    PublicHolidayLeaveRequestFabricator::fabricate($periodEntitlement->contact_id, $publicHoliday);

    //there's a public holiday on the leave request day
    $fromDate = new DateTime('2016-11-16');
    $fromType = $this->leaveRequestDayTypes['all_day']['value'];
    $date = $fromDate->format('YmdHis');

    LeaveRequest::create([
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => $date,
      'from_date_type' => $fromType,
      'to_date' => $date,
      'to_date_type' => $fromType,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The request_type is invalid
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsInvalid() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'request_type' => 'çfdklajfewiojçdasojfdsa'. microtime()
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The toil_duration can not be empty when request_type is toil
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsToilAndToilDurationIsEmpty() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The toil_to_accrue can not be empty when request_type is toil
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsToilAndToilToAccrueIsEmpty() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'toil_duration' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The toil_duration should be empty when request_type is not toil
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsNotToilAndToilDurationIsNotEmpty() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'toil_duration' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_SICKNESS
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The toil_to_accrue should be empty when request_type is not toil
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsNotToilAndToilToAccrueIsNotEmpty() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'toil_to_accrue' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The toil_expiry_date should be empty when request_type is not toil
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsNotToilAndToilExpiryDateIsNotEmpty() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'toil_expiry_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The sickness_reason can not be empty when request_type is sickness
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsSicknessAndSicknessReasonIsEmpty() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_SICKNESS
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The sickness_reason should be empty when request_type is not sickness
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsNotSicknessAndSicknessReasonIsNotEmpty() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'sickness_reason' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The sickness_required_documents should be empty when request_type is not sickness
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsNotSicknessAndSicknessRequiredDocumentIsNotEmpty() {
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'sickness_required_documents' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The Leave request dates are not contained within a valid absence period
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheDatesAreNotContainedInValidAbsencePeriod() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    //the dates are outside of the absence period dates
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The Leave request dates are not contained within a valid absence period
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheDatesOverlapTwoAbsencePeriods() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2015-12-31'),
    ]);
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    //four working days which will create a balance change of 0 i.e the days are on weekends
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage This leave request is after your contract end date. Please modify dates of this request
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheDatesOverlapTwoContractsWithALapseBetweenTheContracts() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 30);
    $periodStartDate1 = '2016-01-01';
    $periodEndDate1 = '2016-06-30';

    $periodStartDate2 = '2016-07-02';
    $periodEndDate2 = '2016-07-31';

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      [
        'period_start_date' => $periodStartDate1,
        'period_end_date' => $periodEndDate1
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      [
        'period_start_date' => $periodStartDate2,
        'period_end_date' => $periodEndDate2
      ]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $periodEntitlement->contact_id,
      'pattern_id' => $workPattern->id
    ]);

    //The from date and to date overlaps the two job contracts with a lapse of 1 day without any contract between
    //the contract dates.
    LeaveRequest::create([
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-06-29'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-07-03'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage This leave request is after your contract end date. Please modify dates of this request
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheDatesOverlapMoreThanTwoContractsWithALapseBetweenTheContracts() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 30);
    $periodStartDate1 = '2016-06-01';
    $periodEndDate1 = '2016-06-08';

    $periodStartDate2 = '2016-06-09';
    $periodEndDate2 = '2016-06-12';

    $periodStartDate3 = '2016-06-14';

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      [
        'period_start_date' => $periodStartDate1,
        'period_end_date' => $periodEndDate1
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      [
        'period_start_date' => $periodStartDate2,
        'period_end_date' => $periodEndDate2
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => $periodStartDate3]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $periodEntitlement->contact_id,
      'pattern_id' => $workPattern->id
    ]);

    //The from date and to date overlaps the three job contracts with a lapse of
    // 1 day without any contract between the last two contracts
    LeaveRequest::create([
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-06-07'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-06-16'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  public function testLeaveRequestCanBeCreatedWhenTheDatesOverlapTwoContractsWithNoLapseBetweenTheContracts() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 30);
    $periodStartDate1 = '2016-01-01';
    $periodEndDate1 = '2016-06-30';

    $periodStartDate2 = '2016-07-01';
    $periodEndDate2 = '2016-07-31';

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      [
        'period_start_date' => $periodStartDate1,
        'period_end_date' => $periodEndDate1
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      [
        'period_start_date' => $periodStartDate2,
        'period_end_date' => $periodEndDate2
      ]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $periodEntitlement->contact_id,
      'pattern_id' => $workPattern->id
    ]);

    $leaveRequest = LeaveRequest::create([
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-06-29'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-07-03'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $this->assertNotNull($leaveRequest->id);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage This absence type does not allow sickness requests
   */
  public function testLeaveRequestCanNotBeCreatedWhenRequestTypeIsSicknessButAbsenceTypeIsNotSickType() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2015-12-31'),
    ]);
    $absenceType = AbsenceTypeFabricator::fabricate(['is_sick' => false]);

    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'sickness_reason' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_SICKNESS
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage This absence type does not allow TOIL requests
   */
  public function testLeaveRequestCanNotBeCreatedWhenRequestTypeIsToilButAbsenceTypeDoesNotAllowToilAccrual() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2015-12-31'),
    ]);
    $absenceType = AbsenceTypeFabricator::fabricate(['allow_accruals_request' => false]);

    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'toil_duration' => 1,
      'toil_to_accrue' => 10,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The TOIL to accrue amount is not valid
   */
  public function testLeaveRequestCanNotBeCreatedWhenRequestTypeIsToilAndToilToAccrueIsNotAValidAmount() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2015-12-31'),
    ]);
    $absenceType = AbsenceTypeFabricator::fabricate(['allow_accruals_request' => true]);

    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'toil_duration' => 1000,
      'toil_to_accrue' => 10,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The TOIL to accrue amount is not valid
   */
  public function testLeaveRequestCanNotBeCreatedWhenRequestTypeIsToilAndToilToAccrueIsNotNumeric() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2015-12-31'),
    ]);
    $absenceType = AbsenceTypeFabricator::fabricate(['allow_accruals_request' => true]);

    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'toil_duration' => 1000,
      'toil_to_accrue' => "4 days",
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The TOIL to accrue amount is not valid
   */
  public function testLeaveRequestCanNotBeCreatedWhenRequestTypeIsToilAndToilToAccrueIsAValidAmountButNotNumeric() {
   //create an option value that is non numeric for toil amount option group
    $toilAmount = '10 Days';
    $result = civicrm_api3('OptionValue', 'create',[
      'option_group_id' => 'hrleaveandabsences_toil_amounts',
      'value' => $toilAmount,
      'label' => 'Ten Days',
    ]);

    //check that option value was successfully created
    $this->assertNotNull($result['id']);

    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2015-12-31'),
    ]);
    $absenceType = AbsenceTypeFabricator::fabricate(['allow_accruals_request' => true]);

    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-11-12'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'toil_duration' => 1000,
      'toil_to_accrue' => $toilAmount,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage You may only request TOIL for overtime to be worked in the future. Please modify the date of this request
   */
  public function testLeaveRequestCanNotBeCreatedWhenRequestTypeIsToilAndDatesAreInThePastAndAbsenceTypeDoesNotAllow() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date'   => CRM_Utils_Date::processDate('+10 days'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'allow_accrue_in_the_past' => false
    ]);

    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('-2 days'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('-1 day'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'toil_duration' => 1,
      'toil_to_accrue' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  public function testLeaveRequestCanNotBeCreatedWhenRequestTypeIsToilAndToilToAccrueIsGreaterThanTheMaximumAllowed() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('today'),
      'end_date'   => CRM_Utils_Date::processDate('+100 days'),
    ]);

    $maxLeaveAccrual = 1;
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => $maxLeaveAccrual,
    ]);

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException',
      'The maximum amount of leave that you can accrue is '. $maxLeaveAccrual . ' days. Please modify the dates of this request'
    );
    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  public function testLeaveRequestCanNotBeCreatedWhenRequestTypeIsToilAndToilAmountPlusApprovedToilForPeriodIsGreaterThanMaximumAllowed() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date'   => CRM_Utils_Date::processDate('+10 days'),
    ]);

    $maxLeaveAccrual = 4;
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => $maxLeaveAccrual,
    ]);

    $contactID  = 1;
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    //Approved TOIL for period is 3
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('-6 days'),
      'to_date' => CRM_Utils_Date::processDate('-5 days'),
      'toil_to_accrue' => 3,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+100 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException',
      'The maximum amount of leave that you can accrue is '. $maxLeaveAccrual . ' days. Please modify the dates of this request'
    );

    //Total TOIL for period = 3 + 2 which is greater than 4 (the allowed maximum)
    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  public function testOpenLeaveRequestOfRequestTypeToilWillNotBeUpdatedIfToilToAccrueAmountIsMoreThanMaxLeaveAccrual() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('today'),
      'end_date'   => CRM_Utils_Date::processDate('+10 days'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'max_leave_accrual' => 3,
      'allow_accruals_request' => true,
      'is_active' => 1,
    ]);

    $params = [
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 3,
      'from_date' => date('YmdHis'),
      'from_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'to_date' => date('YmdHis'),
      'to_date_type' => $this->leaveRequestDayTypes['all_day']['value'],
      'toil_to_accrue' => 3,
      'toil_duration' => 300,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ];

    $toilRequest = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    // decrease the max leave accrual
    $maxLeaveAccrual = 1;
    AbsenceType::create([
      'id' => $absenceType->id,
      'max_leave_accrual' => $maxLeaveAccrual,
      'allow_accruals_request' => true,
      'color' => '#000000'
    ]);

    $this->setExpectedException(
      CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException::class,
      'The maximum amount of leave that you can accrue is '. $maxLeaveAccrual . ' days. Please modify the dates of this request'
    );

    // update the TOIL request status
    $params['id'] = $toilRequest->id;
    $params['status_id'] = 1;
    LeaveRequest::create($params);
  }

  public function testDeleteAllNonExpiredTOILRequestsForAbsenceType() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
    ]);

    // this one is not expired, but it will not be deleted
    // because from_date is < than the given start date
    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'toil_to_accrue' => 3,
      'toil_duration' => 300,
      'toil_expiry_date' => null,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    // this one will not be deleted because from_date is < than the given start date
    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'toil_to_accrue' => 3,
      'toil_duration' => 300,
      'toil_expiry_date' => CRM_Utils_Date::processDate('2016-03-01'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    // the from_date matches the given start date, but it is already
    // expired, so it will not be deleted too
    $leaveRequest3 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-03-11'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-12'),
      'toil_to_accrue' => 3,
      'toil_duration' => 300,
      'toil_expiry_date' => CRM_Utils_Date::processDate('2016-06-10'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    // the from_date value matches the given start date
    // but the expiry_date is in the future, so this one will be
    // deleted
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('next monday'),
      'to_date' => CRM_Utils_Date::processDate('next monday'),
      'toil_to_accrue' => 3,
      'toil_duration' => 300,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+6 months'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    LeaveRequest::deleteAllNonExpiredTOILRequestsForAbsenceType($absenceType->id, new DateTime('2016-03-11'));

    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals(3, $leaveRequest->N);

    $nonDeletedLeaveRequestIDS = [];
    while($leaveRequest->fetch()) {
      $nonDeletedLeaveRequestIDS[] = $leaveRequest->id;
    }

    $this->assertContains($leaveRequest1->id, $nonDeletedLeaveRequestIDS);
    $this->assertContains($leaveRequest2->id, $nonDeletedLeaveRequestIDS);
    $this->assertContains($leaveRequest3->id, $nonDeletedLeaveRequestIDS);
  }

  public function testFindByIdThrowsAnExceptionWhenFindingASoftDeletedLeaveRequest() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-02'),
      'status_id' => 1
    ]);

    LeaveRequest::softDelete($leaveRequest->id);

    $this->setExpectedException('Exception', "Unable to find a " . LeaveRequest::class . " with id {$leaveRequest->id}.");
    LeaveRequest::findById($leaveRequest->id);
  }

  public function testFindByIdThrowsAnExceptionWhenFindingADeletedLeaveRequest() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-02'),
      'status_id' => 1
    ]);

    $leaveRequest->delete();

    $this->setExpectedException('Exception', "Unable to find a " . LeaveRequest::class . " with id {$leaveRequest->id}.");
    LeaveRequest::findById($leaveRequest->id);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Leave Request can not be soft deleted during an update, use the delete method instead!
   */
  public function testValidateParamsThrowsAnExceptionWhenTryingToChangeIsDeletedValueForALeaveRequestDuringAnUpdate() {
    $params = [
      'id' => 1,
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-02'),
      'status_id' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE,
      'is_deleted' => 1
    ];

    LeaveRequest::validateParams($params);
  }

  public function testLeaveRequestIsDeletedValueCanNotBeSetWhenCreatingALeaveRequest() {
    $params = [
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-02'),
      'status_id' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE,
      'is_deleted' => 1
    ];
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $leaveRequestRecord = LeaveRequest::findById($leaveRequest->id);
    $this->assertEquals(0, $leaveRequestRecord->is_deleted);
  }

  public function testEmailGetsSentWhenLeaveRequestIsCreatedAndUpdated() {
    $manager1 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Manager1', 'last_name' => 'Manager1'], 'manager1@dummysite.com'
    );

    $leaveContact = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Staff1', 'last_name' => 'Staff1'], 'staffmember@dummysite.com'
    );

    $this->setContactAsLeaveApproverOf($manager1, $leaveContact);

    $params = [
      'type_id' => 1,
      'contact_id' => $leaveContact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date_type' => 1,
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    //emails redirected to the database are stored in the message spool table
    $result = $this->getEmailNotificationsFromDatabase(['staffmember@dummysite.com', 'manager1@dummysite.com']);

    //To make sure that duplicate emails were not sent but one mail per recipient
    $this->assertEquals(2, $result->N);

    $emails = [];
    while($result->fetch()) {
      $emails[] = ['email' => $result->recipient_email, 'body' => $result->body, 'headers' => $result->headers];
    }

    $recipientEmails = array_column($emails, 'email');
    sort($recipientEmails);

    $expectedEmails = ['manager1@dummysite.com', 'staffmember@dummysite.com'];
    $this->assertEquals($recipientEmails, $expectedEmails);

    foreach($emails as $email) {
      $this->assertNotEmpty($email['body']);
      $this->assertNotEmpty($email['headers']);
    }

    //delete emails sent when leave request is created
    $this->deleteEmailNotificationsInDatabase();
    $result = $this->getEmailNotificationsFromDatabase(['staffmember@dummysite.com', 'manager1@dummysite.com']);
    $this->assertEquals(0, $result->N);

    //update Leave Request
    $params['id'] = $leaveRequest->id;
    $params['from_date_type'] = 2;
    LeaveRequestFabricator::fabricateWithoutValidation($params);

    $result = $this->getEmailNotificationsFromDatabase(['staffmember@dummysite.com', 'manager1@dummysite.com']);

    //To make sure that duplicate emails were not sent but one mail per recipient
    $this->assertEquals(2, $result->N);

    $emails = [];
    while($result->fetch()) {
      $emails[] = ['email' => $result->recipient_email, 'body' => $result->body, 'headers' => $result->headers];
    }

    $recipientEmails = array_column($emails, 'email');
    sort($recipientEmails);

    $expectedEmails = ['manager1@dummysite.com', 'staffmember@dummysite.com'];
    $this->assertEquals($recipientEmails, $expectedEmails);

    foreach($emails as $email) {
      $this->assertNotEmpty($email['body']);
      $this->assertNotEmpty($email['headers']);
    }
  }

  public function testToilCanBeAccruedWhenTheCurrentBalanceForPeriodEntitlementIsZero() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
      'allow_accruals_request' => true,
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $periodStartDate = date('2016-01-01');

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => $periodStartDate]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $periodEntitlement->contact_id,
      'pattern_id' => $workPattern->id
    ]);

    $this->assertEquals(0, $periodEntitlement->getBalance());

    $leaveRequest = LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('today'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date_type' => 1,
      'toil_to_accrue' => 3,
      'toil_duration' => 120,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);

    $this->assertNotNull($leaveRequest->id);
  }

  public function testToilCanBeAccruedWhenTheToilRequestHasNoWorkingDay() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 day'),
      'end_date' => CRM_Utils_Date::processDate('+10 days'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
      'allow_accruals_request' => true,
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $periodStartDate = date('2016-01-01');

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => $periodStartDate]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $periodEntitlement->contact_id,
      'pattern_id' => $workPattern->id
    ]);

    $this->assertEquals(0, $periodEntitlement->getBalance());

    $toilRequest = LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('saturday'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('sunday'),
      'to_date_type' => 1,
      'toil_to_accrue' => 2.5,
      'toil_duration' => 120,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);

    $this->assertNotNull($toilRequest->id);
  }

  public function testToilToAccrueAmountIsSavedCorrectlyWhenAmountToAccrueIsAFloatingNumber() {
    $toilToAccrueAmounts = [1.5, 1.8, 2.5];
    foreach ($toilToAccrueAmounts as $toilToAccrueAmount) {
      LeaveRequestFabricator::fabricateWithoutValidation([
        'contact_id' => 1,
        'type_id' => 1,
        'toil_to_accrue'=> $toilToAccrueAmount,
        'from_date' => CRM_Utils_Date::processDate('yesterday'),
        'to_date' => CRM_Utils_Date::processDate('today'),
        'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
      ]);
    }

    $toilAccrued = new LeaveRequest();
    $toilAccrued->request_type = LeaveRequest::REQUEST_TYPE_TOIL;
    $toilAccrued->find();
    $this->assertEquals(3, $toilAccrued->N);

    $toilAmounts = [];
    while($toilAccrued->fetch()){
      $toilAmounts[] = $toilAccrued->toil_to_accrue;
    }

    //check that the toil accrued amount was saved in the db correctly
    sort($toilAmounts);
    $this->assertEquals($toilAmounts, $toilToAccrueAmounts);
  }

  public function testUpdatingToilThrowsExceptionWhenUpdatingApprovedToilWithAToilAmountGreaterThanWhatWasInitiallyApprovedAndTotalToilToBeAccruedIsGreaterThanMaximumForTheAbsenceType() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 day'),
      'end_date' => CRM_Utils_Date::processDate('+15 days'),
    ]);

    $maxLeaveAccrual = 4;
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => $maxLeaveAccrual
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => $period->start_date]
    );

    $params = [
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'toil_to_accrue'=> 2,
      'from_date' => CRM_Utils_Date::processDate('monday'),
      'to_date' => CRM_Utils_Date::processDate('monday'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
      'from_date_type' => 1,
      'to_date_type' => 1,
      'toil_duration' => 60
    ];

    $toilRequest1 = LeaveRequestFabricator::fabricateWithoutValidation($params, true);
    $params['from_date'] = CRM_Utils_Date::processDate('friday');
    $params['to_date'] = CRM_Utils_Date::processDate('friday');
    $toilRequest2 = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    //two TOIL requests created and total accrued TOIL is 2 + 2 = 4
    $this->assertEquals(4, $periodEntitlement->getBalance());

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException',
      'The maximum amount of leave that you can accrue is '. $maxLeaveAccrual . ' days. Please modify the dates of this request'
    );

    //User tries to update the second TOIL with a toil_to_accrue value of 3. Initial value for this TOIL is 2
    //max leave accrual for this absence type is 4.
    //Total toil to be accrued if update goes through = 2(first TOIL) + 3 (Second TOIL) = 5
    //since 5 > 4(max lave accrual), an exception is thrown.
    $params['id'] = $toilRequest2->id;
    $params['toil_to_accrue'] = 3;
    LeaveRequestFabricator::fabricate($params);
  }

  public function testToilCanBeAccruedWhenUpdatingApprovedToilWithAToilAmountGreaterThanWhatWasInitiallyApprovedAndTotalToilToBeAccruedIsNotGreaterThanMaximumForTheAbsenceType() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 day'),
      'end_date' => CRM_Utils_Date::processDate('+15 days'),
    ]);

    $contactID = 1;
    $periodStartDate = new DateTime('-2 days');
    HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => $periodStartDate->format('Y-m-d')]
    );

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 5
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $params = [
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'toil_to_accrue'=> 2,
      'from_date' => CRM_Utils_Date::processDate('monday'),
      'to_date' => CRM_Utils_Date::processDate('monday'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
      'from_date_type' => 1,
      'to_date_type' => 1,
      'toil_duration' => 60
    ];

    $toilRequest1 = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    $params['from_date'] = CRM_Utils_Date::processDate('friday');
    $params['to_date'] = CRM_Utils_Date::processDate('friday');
    $toilRequest2 = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    //two TOIL requests created and total accrued TOIL is 2 + 2 = 4
    $this->assertEquals(4, $periodEntitlement->getBalance());

    $params['id'] = $toilRequest2->id;
    $params['toil_to_accrue'] = 3;
    $toilRequest = LeaveRequestFabricator::fabricate($params, true);

    //User tries to update the second TOIL with a toil_to_accrue value of 3. Initial value for this TOIL is 2
    //max leave accrual for this absence type is 5.
    //Total toil to be accrued if update goes through = 2(first TOIL) + 3 (Second TOIL) = 5
    //Update goes through since total projected TOIl is not greater than max leave accrual.
    $this->assertEquals(5, $periodEntitlement->getBalance());
  }

  public function testToilCanBeAccruedWhenUpdatingApprovedToilWithAToilAmountLesserThanWhatWasInitiallyApprovedAndTotalToilToBeAccruedIsNotGreaterThanMaximumForTheAbsenceType() {
    $contactID = 1;
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 day'),
      'end_date' => CRM_Utils_Date::processDate('+15 days'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 5
    ]);

    $periodStartDate = new DateTime('-2 days');
    HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => $periodStartDate->format('Y-m-d')]
    );

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'period_id' => $period->id
    ]);

    $params = [
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'toil_to_accrue'=> 2,
      'from_date' => CRM_Utils_Date::processDate('monday'),
      'to_date' => CRM_Utils_Date::processDate('monday'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
      'from_date_type' => 1,
      'to_date_type' => 1,
      'toil_duration' => 60
    ];

    $toilRequest1 = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    $params['from_date'] = CRM_Utils_Date::processDate('friday');
    $params['to_date'] = CRM_Utils_Date::processDate('friday');
    $toilRequest2 = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    //two TOIL requests created and total accrued TOIL is 2 + 2 = 4
    $this->assertEquals(4, $periodEntitlement->getBalance());

    $params['id'] = $toilRequest2->id;
    $params['toil_to_accrue'] = 1;
    $toilRequest = LeaveRequestFabricator::fabricate($params, true);

    //User tries to update the second TOIL with a toil_to_accrue value of 1. Initial value for this TOIL is 2
    //max leave accrual for this absence type is 5.
    //Total toil to be accrued if update goes through = 2(first TOIL) + 1 (Second TOIL) = 3
    //Update goes through since total projected TOIl is not greater than max leave accrual.
    $this->assertEquals(3, $periodEntitlement->getBalance());
  }

  public function testToilCanBeAccruedWhenUpdatingApprovedToilWithAToilAmountSameAsWhatWasInitiallyApprovedAndTotalToilToBeAccruedIsNotGreaterThanMaximumForTheAbsenceType() {
    $contactID = 1;
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 day'),
      'end_date' => CRM_Utils_Date::processDate('+15 days'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 5
    ]);

    $periodStartDate = new DateTime('-5 days');
    HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => $periodStartDate->format('Y-m-d')]
    );

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'period_id' => $period->id
    ]);

    $params = [
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'toil_to_accrue'=> 2,
      'from_date' => CRM_Utils_Date::processDate('monday'),
      'to_date' => CRM_Utils_Date::processDate('monday'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
      'from_date_type' => 1,
      'to_date_type' => 1,
      'toil_duration' => 60
    ];

    $toilRequest1 = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    $params['from_date'] = CRM_Utils_Date::processDate('friday');
    $params['to_date'] = CRM_Utils_Date::processDate('friday');
    $toilRequest2 = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    //two TOIL requests created and total accrued TOIL is 2 + 2 = 4
    $this->assertEquals(4, $periodEntitlement->getBalance());

    $params['id'] = $toilRequest2->id;
    $params['status_id'] = 3;
    $toilRequest = LeaveRequestFabricator::fabricate($params, true);

    //User tries to update the second TOIL without changing the toil_to_accrue value. Initial value for this TOIL is 2
    //max leave accrual for this absence type is 5.
    //Total toil to be accrued remains unchanged if update goes through = 2(first TOIL) + 1 (Second TOIL) = 4
    //Update goes through since total projected TOIl is not greater than max leave accrual.
    //getBalance is 2 because it is the sum of approved Balance changes for a period. Since the status of the Second TOIL
    //has been changed to 3(Awaiting approval) it is no longer counted in getBalance.
    $this->assertEquals(2, $periodEntitlement->getBalance());
  }

  public function testUpdatingToilThrowsExceptionWhenUpdatingApprovedToilWithDatesNotInSamePeriodAsPreviouslyApprovedToilAndTotalToilAccruedIsGreaterThanMaximumForThePeriodContainingTheDates() {
    $period1 = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-01-30'),
    ]);

    $period2 = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-02-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-02-28'),
    ]);

    $maxLeaveAccrual = 5;
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => $maxLeaveAccrual,
      'allow_accrue_in_the_past' => 1
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => '2016-01-01']
    );

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'period_id' => $period1->id
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'period_id' => $period2->id
    ]);

    $params = [
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'toil_to_accrue'=> 2,
      'from_date' => CRM_Utils_Date::processDate('2016-01-06'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-06'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
      'from_date_type' => 1,
      'to_date_type' => 1,
      'toil_duration' => 60
    ];

    $toilRequest1Period1 = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    $params['from_date'] = CRM_Utils_Date::processDate('2016-01-07');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-07');
    $toilRequest2Period1 = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    $params['from_date'] = CRM_Utils_Date::processDate('2016-02-11');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-02-11');
    $toilRequest1Period2 = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    $params['from_date'] = CRM_Utils_Date::processDate('2016-02-12');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-02-12');
    $toilRequest2Period2 = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    //two TOIL requests created and total accrued TOIL is 2 + 2 = 4 for PeriodEntitlement 1
    $this->assertEquals(4, $periodEntitlement1->getBalance());

    //two TOIL requests created and total accrued TOIL is 2 + 2 = 4 for PeriodEntitlement 2
    $this->assertEquals(4, $periodEntitlement2->getBalance());

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException',
      'The maximum amount of leave that you can accrue is '. $maxLeaveAccrual . ' days. Please modify the dates of this request'
    );

    //User tries to update the TOIL accrued in Period 2 with dates in Period 1.
    //If there was a bug, since the TOIL is approved, its value is supposed to be deducted from the Total TOIl for
    //period 1 during the validation and then most likely the TOIL would be accrued for Period 1.
    //
    //Exception is thrown because Max leave accrual is 5 and total TOIL already accrued for period 1 is 4
    //So the code is seeing this TOIL as a new TOIL to be accrued for this period not as an Approved TOIL to be updated which is correct.
    //4(already accrued) + 2(to be accrued)  = 6 and is greater than max leave accrual for Period 1.
    $params['id'] = $toilRequest2Period2->id;
    $params['toil_to_accrue'] = 2;
    $params['from_date'] = CRM_Utils_Date::processDate('2016-01-08');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-08');
    LeaveRequestFabricator::fabricate($params, true);
  }

  public function testLeaveDatesAreNotDeletedAndRecreatedWhenUpdatingALeaveRequestAndLeaveDatesDidNotChange() {
    $fromDate = CRM_Utils_Date::processDate('2016-01-08');
    $toDate = CRM_Utils_Date::processDate('2016-01-10');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate,
      'from_date_type' => 1,
      'to_date' => $toDate,
      'to_date_type' => 1,
    ];
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $dates = $leaveRequest->getDates();
    $beforeDatesID = [];
    foreach($dates as $date) {
      $beforeDatesID[] = $date->id;
    }

    //update leave request without changing the dates
    $params['id'] = $leaveRequest->id;
    LeaveRequestFabricator::fabricateWithoutValidation($params);
    $dates = $leaveRequest->getDates();
    $afterDatesID = [];
    foreach($dates as $date) {
      $afterDatesID[] = $date->id;
    }

    //the leave dates were not deleted because the ID's are still the same.
    $this->assertEquals($beforeDatesID, $afterDatesID);
  }

  public function testDatesChangedReturnsTrueWhenOnlyFromDateTypeChanges(){
    $fromDate = CRM_Utils_Date::processDate('2016-01-08');
    $toDate = CRM_Utils_Date::processDate('2016-01-10');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate,
      'from_date_type' => 1,
      'to_date' => $toDate,
      'to_date_type' => 1,
    ];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    //update leave request
    $params['id'] = $leaveRequest->id;
    $params['from_date_type'] = 2;

    $this->assertTrue(LeaveRequest::datesChanged($params));
  }

  public function testDatesChangedReturnsTrueWhenOnlyToDateTypeChanges(){
    $fromDate = CRM_Utils_Date::processDate('2016-01-08');
    $toDate = CRM_Utils_Date::processDate('2016-01-10');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate,
      'from_date_type' => 1,
      'to_date' => $toDate,
      'to_date_type' => 1,
    ];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    //update leave request
    $params['id'] = $leaveRequest->id;
    $params['to_date_type'] = 2;

    $this->assertTrue(LeaveRequest::datesChanged($params));
  }

  public function testDatesChangedReturnsTrueWhenOnlyToDateChanges(){
    $fromDate = CRM_Utils_Date::processDate('2016-01-08');
    $toDate = CRM_Utils_Date::processDate('2016-01-10');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate,
      'from_date_type' => 1,
      'to_date' => $toDate,
      'to_date_type' => 1,
    ];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    //update leave request
    $params['id'] = $leaveRequest->id;
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-18');

    $this->assertTrue(LeaveRequest::datesChanged($params));
  }

  public function testDatesChangedReturnsTrueWhenOnlyFromDateChanges(){
    $fromDate = CRM_Utils_Date::processDate('2016-01-08');
    $toDate = CRM_Utils_Date::processDate('2016-01-10');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate,
      'from_date_type' => 1,
      'to_date' => $toDate,
      'to_date_type' => 1,
    ];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    //update leave request
    $params['id'] = $leaveRequest->id;
    $params['from_date'] = CRM_Utils_Date::processDate('2016-01-09');

    $this->assertTrue(LeaveRequest::datesChanged($params));
  }

  public function testDatesChangedReturnsTrueWhenAllTheDateParameterChanges(){
    $fromDate = CRM_Utils_Date::processDate('2016-01-08');
    $toDate = CRM_Utils_Date::processDate('2016-01-10');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate,
      'from_date_type' => 1,
      'to_date' => $toDate,
      'to_date_type' => 1,
    ];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    //update leave request
    $params['id'] = $leaveRequest->id;
    $params['from_date'] = CRM_Utils_Date::processDate('2016-01-09');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-15');
    $params['to_date_type'] = 2;
    $params['from_date_type'] = 2;

    $this->assertTrue(LeaveRequest::datesChanged($params));
  }

  public function testDatesChangedReturnsTrueWhenOnlyToDateAndToDateTypeChanges(){
    $fromDate = CRM_Utils_Date::processDate('2016-01-08');
    $toDate = CRM_Utils_Date::processDate('2016-01-10');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate,
      'from_date_type' => 1,
      'to_date' => $toDate,
      'to_date_type' => 1,
    ];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    //update leave request
    $params['id'] = $leaveRequest->id;
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-15');
    $params['to_date_type'] = 2;

    $this->assertTrue(LeaveRequest::datesChanged($params));
  }

  public function testDatesChangedReturnsTrueWhenOnlyFromDateAndFromDateTypeChanges(){
    $fromDate = CRM_Utils_Date::processDate('2016-01-08');
    $toDate = CRM_Utils_Date::processDate('2016-01-10');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate,
      'from_date_type' => 1,
      'to_date' => $toDate,
      'to_date_type' => 1,
    ];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    //update leave request
    $params['id'] = $leaveRequest->id;
    $params['from_date'] = CRM_Utils_Date::processDate('2016-01-15');
    $params['from_date_type'] = 2;

    $this->assertTrue(LeaveRequest::datesChanged($params));
  }

  public function testDatesChangedReturnsFalseWhenAllTheDateParameterDoesNotChange(){
    $fromDate = CRM_Utils_Date::processDate('2016-01-08');
    $toDate = CRM_Utils_Date::processDate('2016-01-10');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate,
      'from_date_type' => 1,
      'to_date' => $toDate,
      'to_date_type' => 1,
    ];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    //update leave request without changing any date parameter
    $params['id'] = $leaveRequest->id;

    $this->assertFalse(LeaveRequest::datesChanged($params));
  }

  public function testCanReturnTheListOfAllStatuses() {
    $leaveStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    $statuses = LeaveRequest::getStatuses();

    $this->assertEquals($leaveStatuses, $statuses);
  }

  public function testCanReturnTheListOfApprovedStatuses() {
    $leaveStatuses = LeaveRequest::getStatuses();

    $approvedStatuses = LeaveRequest::getApprovedStatuses();

    $this->assertCount(2, $approvedStatuses);
    $this->assertContains($leaveStatuses['approved'], $approvedStatuses);
    $this->assertContains($leaveStatuses['admin_approved'], $approvedStatuses);
  }

  public function testCanReturnTheListOfOpenStatuses() {
    $leaveStatuses = LeaveRequest::getStatuses();

    $openStatuses = LeaveRequest::getOpenStatuses();

    $this->assertCount(2, $openStatuses);
    $this->assertContains($leaveStatuses['awaiting_approval'], $openStatuses);
    $this->assertContains($leaveStatuses['more_information_required'], $openStatuses);
  }

  public function testCanReturnTheListOfCancelledStatuses() {
    $leaveStatuses = LeaveRequest::getStatuses();

    $cancelledStatuses = LeaveRequest::getCancelledStatuses();

    $this->assertCount(2, $cancelledStatuses);
    $this->assertContains($leaveStatuses['cancelled'], $cancelledStatuses);
    $this->assertContains($leaveStatuses['rejected'], $cancelledStatuses);
  }

  public function testLeaveRequestIsCreatedWhenBalanceIsGreaterThanEntitlementBalanceWhenAllowOveruseFalseAndValidationModeIsImport() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
      'allow_overuse' => 0
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $entitlementBalanceChange = 3;
    $this->createLeaveBalanceChange($periodEntitlement->id, $entitlementBalanceChange);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);

    //four working days which will create a balance change of 4 and is greater than entitlement balance
    $leaveRequest = LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-14'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-11-17'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ], LeaveRequest::IMPORT_VALIDATION);

    $this->assertNotNull($leaveRequest->id);
  }

  public function testLeaveRequestBeCreatedWhenLeaveRequestHasNoWorkingDayAndValidationModeIsImport() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 3);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);

    //both leave days are on weekends
    $leaveRequest = LeaveRequest::create([
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-12'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-11-13'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ], LeaveRequest::IMPORT_VALIDATION);

    $this->assertNotNull($leaveRequest->id);
  }

  public function testAnAlreadyApprovedLeaveRequestCanBeUpdatedWhenEntitlementBalanceIsZero() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 3);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);

    //3 working days. balance change of -3
    $params = [
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-14'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-11-16'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];

    $leaveRequest = LeaveRequestFabricator::fabricate($params, true);

    $this->assertNotNull($leaveRequest->id);
    //Entitlement balance is Zero. The three leave days have been deducted.
    $this->assertEquals(0, $periodEntitlement->getBalance());

    $params['id'] = $leaveRequest->id;
    $params['status'] = 3;

    //Update Leave Request status
    $leaveRequest = LeaveRequest::create($params);
    $this->assertNotNull($leaveRequest->id);
  }

  public function testApprovedRequestCanNotBeUpdatedWhenCurrentBalanceIsZeroAndDatesChangeAndBalanceChangeIsGreaterThanPreviousBalanceChange() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $entitlementBalance = 3;
    $this->createLeaveBalanceChange($periodEntitlement->id, $entitlementBalance);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);

    //3 working days. Balance change of -3
    $params = [
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-14'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-11-16'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];

    $leaveRequest = LeaveRequestFabricator::fabricate($params, true);

    $this->assertNotNull($leaveRequest->id);
    //Entitlement balance is Zero. The three leave days have been deducted.
    $this->assertEquals(0, $periodEntitlement->getBalance());

    //Update leave request and add one more day to the request so that
    //It will now create a balance change of -4 as opposed to -3 previously.
    $params['id'] = $leaveRequest->id;
    $params['from_date'] = CRM_Utils_Date::processDate('2016-11-14');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-11-17');

    //Since the dates are changed, the request is treated as a fresh request as if it is just being requested
    //with entitlement balance change being same as it was before it was requested.
    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException',
      'There are only ' . $entitlementBalance .' days leave available. This request cannot be made or approved'
    );
    LeaveRequest::create($params);
  }

  public function testUpdatingAlreadyApprovedLeaveThrowsExceptionWhenLeaveDatesNotInSamePeriodAsPreviouslyApprovedLeaveAndLeaveBalanceIsGreaterThanEntitlementBalanceForThatPeriod() {
    $period1 = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-01-30'),
    ]);

    $period2 = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-02-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-02-28'),
    ]);

    $contactId = 1;
    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contactId],
      ['period_start_date' => '2016-01-01']
    );

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactId,
      'period_id' => $period1->id
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactId,
      'period_id' => $period2->id
    ]);

    $entitlement1Balance = 3;
    $entitlement2Balance = 0;
    $this->createLeaveBalanceChange($periodEntitlement1->id, $entitlement1Balance);
    $this->createLeaveBalanceChange($periodEntitlement2->id, $entitlement2Balance);

    $params = [
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactId,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-05'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-01-07'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];

    $leaveRequest = LeaveRequestFabricator::fabricate($params, true);

    $this->assertNotNull($leaveRequest->id);
    //Entitlement balance is Zero. The three leave days have been deducted.
    $this->assertEquals(0, $periodEntitlement1->getBalance());

    //Update Leave Request dates with dates being in the second period.
    //The balance deducted when leave request was made will not be added to $entitlement2Balance
    //since the leave request was not initially approved in the second period.
    //But the entitlement balance is Zero in second period, so an exception will be thrown.
    $params['id'] = $leaveRequest->id;
    $params['from_date'] = CRM_Utils_Date::processDate('2016-02-09');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-02-10');

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException',
      'There are only '. $entitlement2Balance .' days leave available. This request cannot be made or approved'
    );
    LeaveRequest::create($params);
  }

  public function testAnAlreadyApprovedLeaveRequestCanBeUpdatedWhenEntitlementBalanceIsZeroAndChangeBalanceIsFalseAndDatesDidNotChange() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 3);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);

    //3 working days. balance change of -3
    $params = [
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-14'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-11-16'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];

    $leaveRequest = LeaveRequestFabricator::fabricate($params, true);

    $this->assertNotNull($leaveRequest->id);
    //Entitlement balance is Zero. The three leave days have been deducted.
    $this->assertEquals(0, $periodEntitlement->getBalance());

    $params['id'] = $leaveRequest->id;
    $params['status'] = 3;
    $params['change_balance'] = false;

    //Update Leave Request status
    $leaveRequest = LeaveRequest::create($params);
    $this->assertNotNull($leaveRequest->id);
  }

  public function testAnAlreadyApprovedLeaveRequestCanNotBeUpdatedWhenEntitlementBalanceIsZeroAndChangeBalanceIsFalseAndDatesChanged() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $entitlementBalance = 3;
    $this->createLeaveBalanceChange($periodEntitlement->id, 3);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);

    //3 working days. balance change of -3
    $params = [
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-14'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-11-16'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];

    $leaveRequest = LeaveRequestFabricator::fabricate($params, true);

    $this->assertNotNull($leaveRequest->id);
    //Entitlement balance is Zero. The three leave days have been deducted.
    $this->assertEquals(0, $periodEntitlement->getBalance());

    //Date has changed, leave request now five working days
    //resulting in balance change of 5
    $params['id'] = $leaveRequest->id;
    $params['from_date'] = CRM_Utils_Date::processDate('2016-11-14');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-11-18');
    $params['change_balance'] = false;

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException',
      'There are only '. $entitlementBalance .' days leave available. This request cannot be made or approved'
    );

    //Update Leave Request
    $leaveRequest = LeaveRequest::create($params);
  }

  public function testAnAlreadyApprovedLeaveRequestCannotBeUpdatedWhenEntitlementBalanceIsZeroAndChangeBalanceIsTrueAndWorkPatternHasChangedAndDatesDidNotChange() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $entitlementBalance = 3;
    $this->createLeaveBalanceChange($periodEntitlement->id, $entitlementBalance);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours(['is_default' => 1]);

    //3 working days. balance change of -3
    //Mon, Wed Fri
    $params = [
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-14'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-11-18'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];

    $leaveRequest = LeaveRequestFabricator::fabricate($params);

    //We need to use the balance change service here so that it will create balance changes
    //using leave day amount from the work pattern
    $balanceChangeService = new LeaveBalanceChangeService();
    $balanceChangeService->createForLeaveRequest($leaveRequest);

    $this->assertNotNull($leaveRequest->id);
    //Entitlement balance is Zero. The three leave days have been deducted.
    $this->assertEquals(0, $periodEntitlement->getBalance());

    //Add a contact work pattern active in the period when the leave request was created
    //Mon to Fri All working days
    $workPattern1 = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $params['contact_id'],
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-11-12')
    ]);

    //Leave Request balance would have changed as from when it was initially created
    //Balance would now be -5 and an exception would be thrown since
    //leaveBalance is now greater than entitlement balance
    $params['id'] = $leaveRequest->id;
    $params['status'] = 3;
    $params['change_balance'] = true;

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException',
      'There are only '. $entitlementBalance .' days leave available. This request cannot be made or approved'
    );

    LeaveRequest::create($params);
  }

  public function testAnAlreadyApprovedLeaveRequestCanBeUpdatedWhenEntitlementBalanceIsZeroAndChangeBalanceIsFalseAndWorkPatternHasChangedAndDatesDidNotChange() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $entitlementBalance = 3;
    $this->createLeaveBalanceChange($periodEntitlement->id, $entitlementBalance);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours(['is_default' => 1]);

    //3 working days. balance change of -3
    //Mon, Wed Fri
    $params = [
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-14'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-11-18'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];

    $leaveRequest = LeaveRequestFabricator::fabricate($params);

    //We need to use the balance change service here so that it will create balance changes
    //using leave day amount from the work pattern
    $balanceChangeService = new LeaveBalanceChangeService();
    $balanceChangeService->createForLeaveRequest($leaveRequest);

    $this->assertNotNull($leaveRequest->id);
    //Entitlement balance is Zero. The three leave days have been deducted.
    $this->assertEquals(0, $periodEntitlement->getBalance());

    //Add a contact work pattern active in the period when the leave request was created
    //Mon to Fri All working days
    $workPattern1 = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $params['contact_id'],
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-11-12')
    ]);

    //Leave Request balance will not change since change_balance is false
    $params['id'] = $leaveRequest->id;
    $params['status'] = 3;
    $params['change_balance'] = false;

    $leaveRequest = LeaveRequest::create($params);
    $this->assertNotNull($leaveRequest->id);
  }

  public function testAnAlreadyApprovedLeaveRequestCanNotBeUpdatedWhenEntitlementBalanceIsZeroAndChangeBalanceIsTrueAndDatesChanged() {
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'period_id' => $period->id
    ]);

    $entitlementBalance = 3;
    $this->createLeaveBalanceChange($periodEntitlement->id, 3);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);

    //3 working days. balance change of -3
    $params = [
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-11-14'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-11-16'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];

    $leaveRequest = LeaveRequestFabricator::fabricate($params, true);

    $this->assertNotNull($leaveRequest->id);
    //Entitlement balance is Zero. The three leave days have been deducted.
    $this->assertEquals(0, $periodEntitlement->getBalance());

    //Date has changed, leave request now five working days
    //resulting in balance change of 5
    $params['id'] = $leaveRequest->id;
    $params['from_date'] = CRM_Utils_Date::processDate('2016-11-14');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-11-18');
    $params['change_balance'] = true;

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException',
      'There are only '. $entitlementBalance .' days leave available. This request cannot be made or approved'
    );

    //Update Leave Request
    $leaveRequest = LeaveRequest::create($params);
    $this->assertNotNull($leaveRequest->id);
  }

  public function testToilToAccrueChangedReturnsTrueWhenToilToAccrueChanges(){
    $date = CRM_Utils_Date::processDate('2016-01-10');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'from_date' => $date,
      'to_date' => $date,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
      'toil_to_accrue' => 2
    ];

    $toilRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    //update Toil request
    $params['id'] = $toilRequest->id;
    $params['toil_to_accrue'] = 1;

    $this->assertTrue(LeaveRequest::toilToAccrueChanged($params));
  }

  public function testToilToAccrueChangedReturnsFalseWhenToilToAccrueDoesNotChange(){
    $date = CRM_Utils_Date::processDate('2016-01-08');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'from_date' => $date,
      'to_date' => $date,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
      'toil_to_accrue' => 1
    ];

    $toilRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    //update Toil request
    $params['id'] = $toilRequest->id;

    $this->assertFalse(LeaveRequest::toilToAccrueChanged($params));
  }

  public function testToilToAccrueChangedReturnsNullWhenRequestTypeIsNotToil(){
    $date = CRM_Utils_Date::processDate('2016-01-08');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'from_date' => $date,
      'to_date' => $date,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE,
    ];

    $toilRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    //update Toil request
    $params['id'] = $toilRequest->id;

    $this->assertNUll(LeaveRequest::toilToAccrueChanged($params));
  }

  public function testToilToAccrueChangedReturnsNullWhenCreatingANewRequest(){
    $date = CRM_Utils_Date::processDate('2016-01-08');
    $params = [
      'type_id' => 1,
      'contact_id' => 1,
      'from_date' => $date,
      'to_date' => $date,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
      'toil_to_accrue' => 1
    ];

    $this->assertNull(LeaveRequest::toilToAccrueChanged($params));
  }
}
