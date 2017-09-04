<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveBalanceChangeTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveBalanceChangeTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;

  private $leaveBalanceChangeService;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $this->leaveBalanceChangeService = new LeaveBalanceChangeService();
  }

  public function testItCanCreateBalanceChangesForALeaveRequest() {
    $contact = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);

    $leaveRequestDateTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));

    // a 9 days leave request, from friday to saturday of the next week
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $leaveRequestDateTypes['all_day'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-09'),
      'to_date_type' => $leaveRequestDateTypes['all_day'],
    ]);

    $this->leaveBalanceChangeService->createForLeaveRequest($leaveRequest);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used and there are 3 weekend days on the
    // leave period (2 saturdays and 1 sunday), the balance change will be -6
    // (the working days of all the 9 days requested)
    $this->assertEquals(-6, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is -6, we must have 9 balance changes, one for
    // each date
    $this->assertCount(9, $balanceChanges);
  }

  public function testCanSetTheTypeOfTheLeaveRequestDatesToWhichItCreatesTheBalanceChangesTo() {
    $contact = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => '2017-09-04']
    );

    WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours(['is_default' => true]);

    $leaveRequestDateTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = '2017-09-06';

    PublicHolidayLeaveRequestFabricator::fabricate(
      $contact['id'],
      $publicHoliday
    );

    // a 8 days leave request, from monday to monday
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2017-09-04'),
      'from_date_type' => $leaveRequestDateTypes['half_day_pm'],
      'to_date' => CRM_Utils_Date::processDate('2017-09-11'),
      'to_date_type' => $leaveRequestDateTypes['all_day'],
    ]);

    $dates = $leaveRequest->getDates();
    foreach($dates as $date) {
      $this->assertNull($date->type);
    }

    $this->leaveBalanceChangeService->createForLeaveRequest($leaveRequest);

    $dates = $leaveRequest->getDates();
    // According to the work pattern, monday is a working day, but a half day
    // was passed to the day type, so that's is what this date's type should be
    $this->assertEquals($leaveRequestDateTypes['half_day_pm'], $dates[0]->type);
    $this->assertEquals($leaveRequestDateTypes['non_working_day'], $dates[1]->type);

    // Wednesday is a working day on the first week of the work pattern, but
    // we have a public holiday on this date, so the date's type should be
    // public holiday instead of all day
    $this->assertEquals($leaveRequestDateTypes['public_holiday'], $dates[2]->type);
    $this->assertEquals($leaveRequestDateTypes['non_working_day'], $dates[3]->type);
    $this->assertEquals($leaveRequestDateTypes['all_day'], $dates[4]->type);
    $this->assertEquals($leaveRequestDateTypes['weekend'], $dates[5]->type);
    $this->assertEquals($leaveRequestDateTypes['weekend'], $dates[6]->type);

    // This is a monday again, but on the second week of the pattern mondays are
    // not working days
    $this->assertEquals($leaveRequestDateTypes['non_working_day'], $dates[7]->type);
  }

  public function testItCanCreateBalanceChangesForALeaveRequestOfTypeTOIL() {
    $leaveRequestDateTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));

    $expiryDate = new DateTime('2016-03-01');

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $leaveRequestDateTypes['all_day'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-02'),
      'to_date_type' => $leaveRequestDateTypes['all_day'],
      'toil_to_accrue' => 2,
      'toil_duration' => 500,
      'toil_expiry_date' => $expiryDate->format('YmdHis'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ]);

    $this->leaveBalanceChangeService->createForLeaveRequest($leaveRequest);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // It should be the same as the toil_to_accrue amount
    $this->assertEquals(2, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // There should have 2 balance changes, one for each of the dates of the request
    $this->assertCount(2, $balanceChanges);

    // The toil_to_accrue amount is stored only on the balance change for the
    // first date. For all the other dates, the amount should be 0. The expiry
    // date is also only store in the balance change for the first date.
    $this->assertEquals(2, $balanceChanges[0]->amount);
    $this->assertEquals($expiryDate->format('Y-m-d'), $balanceChanges[0]->expiry_date);
    $this->assertEquals(0, $balanceChanges[1]->amount);
    $this->assertNull($balanceChanges[1]->expiry_date);
  }
}
