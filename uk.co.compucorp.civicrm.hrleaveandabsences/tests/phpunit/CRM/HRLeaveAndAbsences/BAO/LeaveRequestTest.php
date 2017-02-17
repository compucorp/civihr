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
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

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
    $this->leaveRequestDayTypes = $this->getLeaveRequestDayTypes();
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");
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
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => $leaveRequestStatuses['Waiting Approval'],
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
    $fromType = $this->leaveRequestDayTypes['1/2 AM']['value'];
    $toType = $this->leaveRequestDayTypes['1/2 AM']['value'];

    $expectedResultsBreakdown = [
      'amount' => 0,
      'breakdown' => []
    ];

    // Start date is a sunday, Weekend
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-11-13',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['Weekend']['id'],
        'value' => $this->leaveRequestDayTypes['Weekend']['value'],
        'label' => $this->leaveRequestDayTypes['Weekend']['label']
      ]
    ];

    // The next day is a monday, which is a working day
    $expectedResultsBreakdown['amount'] += 1;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-11-14',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['All Day']['id'],
        'value' => $this->leaveRequestDayTypes['All Day']['value'],
        'label' => $this->leaveRequestDayTypes['All Day']['label']
      ]
    ];

    // last day is a tuesday, which is a working day, half day will be deducted
    $expectedResultsBreakdown['amount'] += 0.5;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-11-15',
      'amount' => 0.5,
      'type' => [
        'id' => $this->leaveRequestDayTypes['1/2 AM']['id'],
        'value' => $this->leaveRequestDayTypes['1/2 AM']['value'],
        'label' => $this->leaveRequestDayTypes['1/2 AM']['label']
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
    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
    $toType = $this->leaveRequestDayTypes['All Day']['value'];

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
        'id' => $this->leaveRequestDayTypes['Public Holiday']['id'],
        'value' => $this->leaveRequestDayTypes['Public Holiday']['value'],
        'label' => $this->leaveRequestDayTypes['Public Holiday']['label']
      ]
    ];

    // last day is a tuesday, which is a working day
    $expectedResultsBreakdown['amount'] += 1.0;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-11-15',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['All Day']['id'],
        'value' => $this->leaveRequestDayTypes['All Day']['value'],
        'label' => $this->leaveRequestDayTypes['All Day']['label']
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
    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
    $toType = $this->leaveRequestDayTypes['1/2 AM']['value'];

    $expectedResultsBreakdown = [
      'amount' => 0,
      'breakdown' => []
    ];

    // Start day (2016-07-31), a sunday
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-07-31',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['Weekend']['id'],
        'value' => $this->leaveRequestDayTypes['Weekend']['value'],
        'label' => $this->leaveRequestDayTypes['Weekend']['label']
      ]
    ];

    // Since the start date is a sunday, the end of the week, the following day
    // (2016-08-01) should be on the second week. Monday of the second week is
    // not a working day
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-01',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['Non Working Day']['id'],
        'value' => $this->leaveRequestDayTypes['Non Working Day']['value'],
        'label' => $this->leaveRequestDayTypes['Non Working Day']['label']
      ]
    ];

    // The next day is a tuesday, which is a working day on the second week, so
    $expectedResultsBreakdown['amount'] += 1;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-02',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['All Day']['id'],
        'value' => $this->leaveRequestDayTypes['All Day']['value'],
        'label' => $this->leaveRequestDayTypes['All Day']['label']
      ]
    ];

    // Wednesday is not a working day on the second week
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-03',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['Non Working Day']['id'],
        'value' => $this->leaveRequestDayTypes['Non Working Day']['value'],
        'label' => $this->leaveRequestDayTypes['Non Working Day']['label']
      ]
    ];

    // Thursday is a working day on the second week
    $expectedResultsBreakdown['amount'] += 1;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-04',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['All Day']['id'],
        'value' => $this->leaveRequestDayTypes['All Day']['value'],
        'label' => $this->leaveRequestDayTypes['All Day']['label']
      ]
    ];

    // Friday, Saturday and Sunday are not working days on the second week,
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-05',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['Non Working Day']['id'],
        'value' => $this->leaveRequestDayTypes['Non Working Day']['value'],
        'label' => $this->leaveRequestDayTypes['Non Working Day']['label']
      ]
    ];

    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-06',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['Weekend']['id'],
        'value' => $this->leaveRequestDayTypes['Weekend']['value'],
        'label' => $this->leaveRequestDayTypes['Weekend']['label']
      ]
    ];

    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-07',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['Weekend']['id'],
        'value' => $this->leaveRequestDayTypes['Weekend']['value'],
        'label' => $this->leaveRequestDayTypes['Weekend']['label']
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
        'id' => $this->leaveRequestDayTypes['All Day']['id'],
        'value' => $this->leaveRequestDayTypes['All Day']['value'],
        'label' => $this->leaveRequestDayTypes['All Day']['label']
      ]
    ];

    // Tuesday is not a working day on the first week
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-09',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['Non Working Day']['id'],
        'value' => $this->leaveRequestDayTypes['Non Working Day']['value'],
        'label' => $this->leaveRequestDayTypes['Non Working Day']['label']
      ]
    ];
    // Wednesday is a working day on the first week
    $expectedResultsBreakdown['amount'] += 1;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-10',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['All Day']['id'],
        'value' => $this->leaveRequestDayTypes['All Day']['value'],
        'label' => $this->leaveRequestDayTypes['All Day']['label']
      ]
    ];
    // Thursday is not a working day on the first week
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-11',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['Non Working Day']['id'],
        'value' => $this->leaveRequestDayTypes['Non Working Day']['value'],
        'label' => $this->leaveRequestDayTypes['Non Working Day']['label']
      ]
    ];

    // Friday is a working day on the first week
    $expectedResultsBreakdown['amount'] += 1;
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-12',
      'amount' => 1.0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['All Day']['id'],
        'value' => $this->leaveRequestDayTypes['All Day']['value'],
        'label' => $this->leaveRequestDayTypes['All Day']['label']
      ]
    ];

    // Saturday and Sunday are not working days on the first week
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-13',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['Weekend']['id'],
        'value' => $this->leaveRequestDayTypes['Weekend']['value'],
        'label' => $this->leaveRequestDayTypes['Weekend']['label']
      ]
    ];

    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-14',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['Weekend']['id'],
        'value' => $this->leaveRequestDayTypes['Weekend']['value'],
        'label' => $this->leaveRequestDayTypes['Weekend']['label']
      ]
    ];
    // Hit sunday again, so we are now on the fourth week since the start date.
    // The work pattern will rotate and use the week 2

    // Monday is not a working day on week 2
    $expectedResultsBreakdown['breakdown'][] = [
      'date' => '2016-08-15',
      'amount' => 0,
      'type' => [
        'id' => $this->leaveRequestDayTypes['Non Working Day']['id'],
        'value' => $this->leaveRequestDayTypes['Non Working Day']['value'],
        'label' => $this->leaveRequestDayTypes['Non Working Day']['label']
      ]
    ];
    $expectedResultsBreakdown['amount'] *= -1;

    $result = LeaveRequest::calculateBalanceChange($contract['contact_id'], $fromDate, $fromType, $toDate, $toType);
    $this->assertEquals($expectedResultsBreakdown, $result);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Leave Requests should have a start date
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
   * @expectedExceptionMessage Leave Requests should have an end date
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutAnEndDate() {
    $fromDate = new DateTime('+4 days');
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Leave Request should have a contact
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutContactID() {
    $fromDate = new DateTime('+4 days');
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Leave Request should have an Absence Type
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutTypeID() {
    $fromDate = new DateTime('+4 days');
    LeaveRequest::create([
      'status_id' => 1,
      'contact_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The Leave Request status should not be empty
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutStatusID() {
    $fromDate = new DateTime('+4 days');
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The type of To Date should not be empty
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutToDateType() {
    $toDate= new DateTime('+4 days');
    $fromDate = new DateTime();
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The type of From Date should not be empty
   */
  public function testALeaveRequestShouldNotBeCreatedWithoutFromDateType() {
    $toDate= new DateTime('+4 days');
    $fromDate = new DateTime();
    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Leave Request start date cannot be greater than the end date
   */
  public function testALeaveRequestEndDateShouldNotBeGreaterThanStartDate() {
    $fromDate = new DateTime('+4 days');
    $toDate = new DateTime();
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

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Absence Type is not active
   */
  public function testLeaveRequestShouldNotBeCreatedWhenAbsenceTypeIsNotActive() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'is_active' => 0
    ]);

    $fromDate = new DateTime();
    $toDate = new DateTime('+4 days');
    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Leave Request days cannot be greater than maximum consecutive days for absence type
   */
  public function testNumberOfDaysOfLeaveRequestShouldNotBeGreaterMaxConsecutiveLeaveDaysForAbsenceType() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'max_consecutive_leave_days' => 2
    ]);

    $fromDate = new DateTime();
    $toDate = new DateTime('+4 days');
    LeaveRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
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

    $fromDate = new DateTime();
    $toDate = new DateTime('+4 days');
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Waiting Approval'],
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1
    ]);

    $this->setExpectedException('CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException', 'Absence Type does not allow leave request cancellation');
    //cancel leave request
    LeaveRequest::create([
      'id' => $leaveRequest->id,
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Cancelled'],
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
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

    $fromDate = new DateTime('-1 day');
    $toDate = new DateTime('+4 days');
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Waiting Approval'],
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1
    ]);

    $this->setExpectedException('CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException', 'Leave Request with past days cannot be cancelled');
    //cancel leave request
    LeaveRequest::create([
      'id' => $leaveRequest->id,
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Cancelled'],
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
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
    $contactID = 1;
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = '2016-11-11';

    $fromDate1 = new DateTime('2016-11-02');
    $toDate1 = new DateTime('2016-11-04');

    $fromDate2 = new DateTime('2016-11-05');
    $toDate2 = new DateTime('2016-11-10');

    $fromDate3 = new DateTime('2016-11-12');
    $toDate3 = new DateTime('2016-11-15');

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Waiting Approval'],
      'from_date' => $fromDate1->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate1->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['More Information Requested'],
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Rejected'],
      'from_date' => $fromDate3->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate3->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    PublicHolidayLeaveRequestFabricator::fabricate($contactID, $publicHoliday);

    //The start date and end date has dates in leave request dates for leaveRequest1, leaveRequest2
    //leaveRequest3 and PublicHolidayLeaveRequest, but we have filtered by only 'More Information Requested'
    //therefore only one overlapping Leave Request is expected
    $startDate = '2016-11-02';
    $endDate = '2016-11-15';
    $filterStatus = [$leaveRequestStatuses['More Information Requested']];
    $overlappingRequests = LeaveRequest::findOverlappingLeaveRequests($contactID, $startDate, $endDate, $filterStatus);
    $this->assertCount(1, $overlappingRequests);
    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests[0]);
    $this->assertEquals($leaveRequest2->id, $overlappingRequests[0]->id);

    //The start date and end date has dates in leave request dates for leaveRequest1, leaveRequest2,
    //leaveRequest3 and PublicHolidayLeaveRequest, but we have filtered by only 'More Information Requested' and 'Waiting Approval'
    //and overlapping public holiday leave requests is not excluded.
    //However two leave request is expected because, Public holiday leave requests have status 'Admin Approved' by default
    $startDate = '2016-11-01';
    $endDate = '2016-11-16';
    $filterStatus = [$leaveRequestStatuses['More Information Requested'], $leaveRequestStatuses['Waiting Approval']];
    $overlappingRequests2 = LeaveRequest::findOverlappingLeaveRequests($contactID, $startDate, $endDate, $filterStatus, false);
    $this->assertCount(2, $overlappingRequests2);
    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests[0]);
    $this->assertEquals($leaveRequest1->id, $overlappingRequests2[0]->id);

    $this->assertInstanceOf(LeaveRequest::class, $overlappingRequests2[1]);
    $this->assertEquals($leaveRequest2->id, $overlappingRequests2[1]->id);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage This Leave request has dates that overlaps with an existing leave request
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

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Waiting Approval'],
      'from_date' => $fromDate1->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate1->format('YmdHis'),
      'to_date_type' => 1
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Rejected'],
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

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => $contactID,
      'status_id' => $leaveRequestStatuses['Rejected'],
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

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage Balance change for the leave request cannot be greater than the remaining balance of the period
   */
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
    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
    $toType = $this->leaveRequestDayTypes['All Day']['value'];

    //four working days which will create a balance change of 4

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
    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
    $toType = $this->leaveRequestDayTypes['All Day']['value'];

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
    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
    $toType = $this->leaveRequestDayTypes['All Day']['value'];

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
    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
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
   * @expectedExceptionMessage The request_type should not be empty
   */
  public function testLeaveRequestCanNotBeCreatedWhenRequestTypeIsEmpty() {
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2015-11-13');

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value']
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The request_type is invalid
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsInvalid() {
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2015-11-13');

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'request_type' => 'çfdklajfewiojçdasojfdsa'. microtime()
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The toil_duration can not be empty when request_type is toil
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsToilAndToilDurationIsEmpty() {
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2015-11-13');

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The toil_to_accrue can not be empty when request_type is toil
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsToilAndToilToAccrueIsEmpty() {
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2015-11-13');

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'toil_duration' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The toil_duration should be empty when request_type is not toil
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsNotToilAndToilDurationIsNotEmpty() {
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2015-11-13');

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'toil_duration' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_SICKNESS
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The toil_to_accrue should be empty when request_type is not toil
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsNotToilAndToilToAccrueIsNotEmpty() {
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2015-11-13');

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'toil_to_accrue' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The toil_expiry_date should be empty when request_type is not toil
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsNotToilAndToilExpiryDateIsNotEmpty() {
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2015-11-13');

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'toil_expiry_date' => $fromDate->format('Y-m-d'),
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The sickness_reason can not be empty when request_type is sickness
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsSicknessAndSicknessReasonIsEmpty() {
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2015-11-13');

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_SICKNESS
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The sickness_reason should be empty when request_type is not sickness
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsNotSicknessAndSicknessReasonIsNotEmpty() {
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2015-11-13');

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'sickness_reason' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The sickness_required_documents should be empty when request_type is not sickness
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheRequestTypeIsNotSicknessAndSicknessRequiredDocumentIsNotEmpty() {
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2015-11-13');

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
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
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2015-11-13');
    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
    $toType = $this->leaveRequestDayTypes['All Day']['value'];

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
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
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2016-01-13');
    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
    $toType = $this->leaveRequestDayTypes['All Day']['value'];

    LeaveRequest::create([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
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
   * @expectedExceptionMessage The Leave request dates must not have dates in more than one contract period
   */
  public function testLeaveRequestCanNotBeCreatedWhenTheDatesOverlapMoreThanOneContract() {
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
    $periodStartDate1 = date('2016-01-01');
    $periodEndDate1 = date('2016-06-30');

    $periodStartDate2 = date('2016-07-01');
    $periodEndDate2 = date('2016-12-31');

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

    //The from date and to date overlaps the two job contracts
    $fromDate = new DateTime('2016-06-25');
    $toDate = new DateTime('2016-07-13');
    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
    $toType = $this->leaveRequestDayTypes['All Day']['value'];

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
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'sickness_reason' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_SICKNESS
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage This absence type does not allow TOIL requests
   */
  public function testLeaveRequestCanNotBeCreatedWhenRequestTypeIsToilButAbsenceTypeIsNotAToilType() {
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
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'toil_duration' => 1,
      'toil_to_accrue' => 10,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The TOIL to accrue amount is not valid
   */
  public function testLeaveRequestCanNotBeCreatedWhenRequestTypeIsToilButToilToAccruedItNotAValidAmount() {
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
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2015-11-13'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'toil_duration' => 1000,
      'toil_to_accrue' => 10,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage You cannot request TOIL for past days
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
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('-1 day'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'toil_duration' => 1,
      'toil_to_accrue' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);
  }
}
