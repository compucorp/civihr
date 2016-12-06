<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
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

/**
 * Class api_v3_LeaveRequestTest
 *
 * @group headless
 */
class api_v3_LeaveRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  public function setUp() {
    // In order to make tests simpler, we disable the foreign key checks,
    // as a way to allow the creation of leave request records related
    // to a non-existing leave period entitlement
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

    // We delete everything two avoid problems with the default absence types
    // created during the extension installation
    $tableName = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");
    $this->leaveRequestDayTypes = $this->leaveRequestDayTypeOptionsBuilder();
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id, period_id
   */
  public function testGetBalanceChangeByAbsenceTypeShouldNotAllowParamsWithoutContactIDAndPeriodID() {
    civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', []);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id
   */
  public function testGetBalanceChangeByAbsenceTypeShouldNotAllowParamsWithoutContactID() {
    civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'period_id' => 1
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: period_id
   */
  public function testGetBalanceChangeByAbsenceTypeShouldNotAllowParamsWithoutPeriodID() {
    civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => 1
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage The statuses parameter only supports the IN operator
   *
   * @dataProvider invalidGetBalanceChangeByAbsenceTypeStatusesOperators
   */
  public function testGetBalanceChangeByAbsenceTypeShouldOnlyAllowTheINOperator($operator) {
    civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => 1,
      'period_id' => 1,
      'statuses' => [$operator => [1]]
    ]);
  }

  public function testGetBalanceChangeByAbsenceTypeDoesNotThrowAnErrorWhenUsingTheEqualsOperatorForStatuses() {
    $values = civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => 1,
      'period_id' => 1,
      'statuses' => 1
    ]);

    $this->assertEquals(0, $values['is_error']);
  }

  public function testGetBalanceChangeByAbsenceTypeCanBeFilteredByStatuses() {
    $contact = ContactFabricator::fabricate();

    $absenceType = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => true]);

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType->id
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    LeaveRequestFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+5 days'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    LeaveRequestFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+8 days'),
      'to_date' => CRM_Utils_Date::processDate('+9 days'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    LeaveRequestFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+20 days'),
      'to_date' => CRM_Utils_Date::processDate('+35 days'),
      'status_id' => $leaveRequestStatuses['Rejected']
    ], true);

    $result = civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => $contact['id'],
      'period_id' => $absencePeriod->id,
      'statuses' => ['IN' => [$leaveRequestStatuses['Approved'], $leaveRequestStatuses['Rejected']]]
    ]);
    $expectedResult = [$absenceType->id => -21];
    $this->assertEquals($expectedResult, $result['values']);

    $result = civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => $contact['id'],
      'period_id' => $absencePeriod->id,
      'statuses' => ['IN' => [$leaveRequestStatuses['Waiting Approval'], $leaveRequestStatuses['Rejected']]]
    ]);
    $expectedResult = [$absenceType->id => -18];
    $this->assertEquals($expectedResult, $result['values']);
  }

  public function testGetBalanceChangeByAbsenceTypeCanBeFilteredForPublicHolidays() {
    $contact = ContactFabricator::fabricate();

    $absenceType = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => true]);

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
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = date('Y-m-d', strtotime('+40 days'));

    PublicHolidayLeaveRequestFabricator::fabricate($contact['id'], $publicHoliday);

    // Passing the public_holiday param, it will sum the balance only for the
    // public holidays
    $publicHolidaysOnly = true;
    $result = civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => $contact['id'],
      'period_id' => $absencePeriod->id,
      'public_holiday' => $publicHolidaysOnly
    ]);
    $expectedResult = [$absenceType->id => -1];
    $this->assertEquals($expectedResult, $result['values']);

    // Without passing the public_holiday param, it will sum the balance
    // for everything, except the public holidays
    $result = civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => $contact['id'],
      'period_id' => $absencePeriod->id,
    ]);
    $expectedResult = [$absenceType->id => -2];
    $this->assertEquals($expectedResult, $result['values']);
  }

  public function testGetDoesntReturnPublicHolidayLeaveRequestsIfThePublicHolidayParamIsNotPresentOrIsFalse() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    // The get endpoint only returns leave requests overlapping contracts, so
    // we need them in place
    HRJobContractFabricator::fabricate(['contact_id' => 1], ['period_start_date' => '-1 day']);
    HRJobContractFabricator::fabricate(['contact_id' => 2], ['period_start_date' => '-1 day']);

    $absenceType = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => true]);

    $leaveRequest1 = LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricate([
      'contact_id' => 2,
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = date('Y-m-d', strtotime('+10 days'));
    PublicHolidayLeaveRequestFabricator::fabricate(1, $publicHoliday);

    $result = civicrm_api3('LeaveRequest', 'get');
    $this->assertCount(2, $result['values']);
    $this->assertEquals($leaveRequest1->contact_id, $result['values'][$leaveRequest1->id]['contact_id']);
    $this->assertEquals($leaveRequest1->type_id, $result['values'][$leaveRequest1->id]['type_id']);
    $this->assertEquals($leaveRequest1->status_id, $result['values'][$leaveRequest1->id]['status_id']);

    $this->assertEquals($leaveRequest2->contact_id, $result['values'][$leaveRequest2->id]['contact_id']);
    $this->assertEquals($leaveRequest2->type_id, $result['values'][$leaveRequest2->id]['type_id']);
    $this->assertEquals($leaveRequest2->status_id, $result['values'][$leaveRequest2->id]['status_id']);

    $result = civicrm_api3('LeaveRequest', 'get', ['public_holiday' => false]);
    $this->assertCount(2, $result['values']);
    $this->assertEquals($leaveRequest1->contact_id, $result['values'][$leaveRequest1->id]['contact_id']);
    $this->assertEquals($leaveRequest1->type_id, $result['values'][$leaveRequest1->id]['type_id']);
    $this->assertEquals($leaveRequest1->status_id, $result['values'][$leaveRequest1->id]['status_id']);

    $this->assertEquals($leaveRequest2->contact_id, $result['values'][$leaveRequest2->id]['contact_id']);
    $this->assertEquals($leaveRequest2->type_id, $result['values'][$leaveRequest2->id]['type_id']);
    $this->assertEquals($leaveRequest2->status_id, $result['values'][$leaveRequest2->id]['status_id']);
  }

  public function testGetReturnsOnlyPublicHolidayLeaveRequestsIfThePublicHolidayIsTrue() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    // The get endpoint only returns leave requests overlapping contracts, so
    // we need them in place
    HRJobContractFabricator::fabricate(['contact_id' => 1], ['period_start_date' => '-1 day']);
    HRJobContractFabricator::fabricate(['contact_id' => 2], ['period_start_date' => '-1 day']);

    $absenceType = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => true]);

    $leaveRequest1 = LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricate([
      'contact_id' => 2,
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = date('Y-m-d', strtotime('+10 days'));
    PublicHolidayLeaveRequestFabricator::fabricate(1, $publicHoliday);

    $result = civicrm_api3('LeaveRequest', 'get', ['public_holiday' => true, 'sequential' => 1]);
    $this->assertCount(1, $result['values']);
    $this->assertNotEquals($leaveRequest1->contact_id, $result['values'][0]['id']);
    $this->assertNotEquals($leaveRequest2->contact_id, $result['values'][0]['id']);
    $this->assertEquals(1, $result['values'][0]['contact_id']);
    $this->assertEquals($absenceType->id, $result['values'][0]['type_id']);
    $this->assertEquals($publicHoliday->date, $result['values'][0]['from_date']);
  }

  public function testGetCanReturnALeaveRequestWhichOverlapsAContractWithoutEndDate() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate([
      'contact_id' => 1
    ],
    [
      'period_start_date' => '2016-01-01'
    ]);

    //This leave request is before the contract start date and will not be returned
    LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-12-30'),
      'to_date' => CRM_Utils_Date::processDate('2015-12-31'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    //This will be returned as it is after the contract start date
    $leaveRequest2 = LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2017-12-30'),
      'to_date' => CRM_Utils_Date::processDate('2017-12-31'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get');
    $this->assertCount(1, $result['values']);
    $this->assertNotEmpty($result['values'][$leaveRequest2->id]);
  }

  public function testGetCanReturnLeaveRequestsWhichOverlapAContractWithEndDate() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate([
      'contact_id' => 1
    ],
    [
      'period_start_date' => '2016-01-01',
      'period_end_date' => '2016-10-01'
    ]);

    //This leave request is before the contract start date and will not be returned
    LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-12-30'),
      'to_date' => CRM_Utils_Date::processDate('2015-12-31'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    // This will be returned
    $leaveRequest2 = LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-03'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    // This will be returned
    $leaveRequest3 = LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-09-07'),
      'to_date' => CRM_Utils_Date::processDate('2016-09-08'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    //This will not be returned as it is after the contract start date
    LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2017-12-30'),
      'to_date' => CRM_Utils_Date::processDate('2017-12-31'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get');
    $this->assertCount(2, $result['values']);
    $this->assertNotEmpty($result['values'][$leaveRequest2->id]);
    $this->assertNotEmpty($result['values'][$leaveRequest3->id]);
  }

  public function testGetCanReturnLeaveRequestsWithoutToDateWhichOverlapAContractWithoutEndDate() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate([
      'contact_id' => 1
    ],
    [
      'period_start_date' => '2016-01-01',
    ]);

    //This leave request is before the contract start date and will not be returned
    LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-12-30'),
      'status_id' => $leaveRequestStatuses['Cancelled']
    ], true);

    // This will be returned as it's after the contract start date
    $leaveRequest2 = LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2017-09-02'),
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    // This will be returned as it's after the contract start date as well
    $leaveRequest3 = LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2018-01-02'),
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get');
    $this->assertCount(2, $result['values']);
    $this->assertNotEmpty($result['values'][$leaveRequest2->id]);
    $this->assertNotEmpty($result['values'][$leaveRequest3->id]);
  }

  public function testGetCanReturnLeaveRequestsWithoutToDateWhichOverlapAContractWithEndDate() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate([
      'contact_id' => 1
    ],
    [
      'period_start_date' => '2016-01-01',
      'period_end_date' => '2016-10-01'
    ]);

    //This leave request is before the contract start date and will not be returned
    LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-12-30'),
      'status_id' => $leaveRequestStatuses['Cancelled']
    ], true);

    // This will be returned
    $leaveRequest2 = LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    // This will be returned
    $leaveRequest3 = LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    //This will not be returned as it is after the contract start date
    LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2017-12-30'),
      'status_id' => $leaveRequestStatuses['Rejected']
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get');
    $this->assertCount(2, $result['values']);
    $this->assertNotEmpty($result['values'][$leaveRequest2->id]);
    $this->assertNotEmpty($result['values'][$leaveRequest3->id]);
  }

  public function testGetFullIncludesTheBalanceChangeAndDatesForTheReturnedLeaveRequests() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => 1 ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    // This will be returned. The balance change will be -1
    $leaveRequest1 = LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    // This will be returned. The balance change will be -4
    $leaveRequest2 = LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-02-23'),
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    $result = civicrm_api3('LeaveRequest', 'getFull');
    $this->assertCount(2, $result['values']);

    $this->assertEquals(-1, $result['values'][$leaveRequest1->id]['balance_change']);
    $this->assertCount(1, $result['values'][$leaveRequest1->id]['dates']);
    $this->assertEquals('2016-03-02', $result['values'][$leaveRequest1->id]['dates'][0]['date']);

    $this->assertEquals(-4, $result['values'][$leaveRequest2->id]['balance_change']);
    $this->assertCount(4, $result['values'][$leaveRequest2->id]['dates']);
    $this->assertEquals('2016-02-20', $result['values'][$leaveRequest2->id]['dates'][0]['date']);
    $this->assertEquals('2016-02-21', $result['values'][$leaveRequest2->id]['dates'][1]['date']);
    $this->assertEquals('2016-02-22', $result['values'][$leaveRequest2->id]['dates'][2]['date']);
    $this->assertEquals('2016-02-23', $result['values'][$leaveRequest2->id]['dates'][3]['date']);
  }

  public function testGetDoesNotReturnALeaveRequestNotOverlappingAContractEvenIfItMatchesTheDatesParams() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate([
      'contact_id' => 1
    ],
    [
      'period_start_date' => '2016-01-01',
      'period_end_date' => '2016-10-01'
    ]);

    //This leave request matches the date params, but not the contract dates,
    //so it will not be returned
    LeaveRequestFabricator::fabricate([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-12-30'),
      'status_id' => $leaveRequestStatuses['Cancelled']
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get', ['from_date' => '2015-12-30']);
    $this->assertCount(0, $result['values']);
  }

  public function invalidGetBalanceChangeByAbsenceTypeStatusesOperators() {
    return [
      ['>'],
      ['>='],
      ['<='],
      ['<'],
      ['<>'],
      ['!='],
      ['BETWEEN'],
      ['NOT BETWEEN'],
      ['LIKE'],
      ['NOT LIKE'],
      ['NOT IN'],
      ['IS NULL'],
      ['IS NOT NULL'],
    ];
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id, from_date, from_type
   */
  public function testCalculateBalanceChangeShouldNotAllowNullParams() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', []);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id
   */
  public function testCalculateBalanceChangeShouldNotAllowParamsWithoutContactID() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'from_date' => "2016-11-05",
      'from_type' => $this->leaveRequestDayTypes['1/2 AM']['name'],
      'to_date' => "2016-11-10",
      'to_type' => $this->leaveRequestDayTypes['1/2 PM']['name'],
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: from_date
   */
  public function testCalculateBalanceChangeShouldNotAllowParamsWithoutFromDate() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => 1,
      'from_type' => $this->leaveRequestDayTypes['1/2 AM']['name'],
      'to_date' => "2016-11-10",
      'to_type' => $this->leaveRequestDayTypes['1/2 PM']['name'],
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: from_type
   */
  public function testCalculateBalanceChangeShouldNotAllowParamsWithoutFromType() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => 1,
      'from_date' => "2016-11-05",
      'to_date' => "2016-11-10",
      'to_type' => $this->leaveRequestDayTypes['1/2 PM']['name'],
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage from_date is not a valid date: 2016-19-05
   */
  public function testCalculateBalanceChangeShouldNotAllowInvalidDate() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => 1,
      'from_date' => "2016-19-05",
      'from_type' => $this->leaveRequestDayTypes['1/2 AM']['name'],
      'to_date' => "2016-11-10",
      'to_type' => $this->leaveRequestDayTypes['1/2 PM']['name']
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage to_date and to_type must be included together
   */
  public function testCalculateBalanceChangeShouldNotAllowToTypeParameterWhenToDateIsNotPresent() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => 1,
      'from_date' => "2016-11-05",
      'from_type' => $this->leaveRequestDayTypes['1/2 AM']['name'],
      'to_type' => $this->leaveRequestDayTypes['1/2 PM']['name'],
    ]);
  }

  public function testCalculateBalanceChangeWithAllRequiredParameters() {
    $contact = ContactFabricator::fabricate();
    $periodStartDate = date('Y-01-01');
    $title = 'Job Title';

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate,
      'title' => $title
    ]);
    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    //attach the work pattern to the contact
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id
    ]);

    $fromDate = date("2016-11-13");
    $toDate = date("2016-11-15");
    $fromType = $this->leaveRequestDayTypes['1/2 AM']['name'];
    $toType = $this->leaveRequestDayTypes['1/2 AM']['name'];

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
        'label' => 'Weekend'
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
        'label' => 'All Day'
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
        'label' => '1/2 AM'
      ]
    ];

    $expectedResultsBreakdown['amount'] *= -1;

    $result = civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => $contact['id'],
      'from_date' => $fromDate,
      'from_type' => $fromType,
      'to_date' => $toDate,
      'to_type' => $toType
    ]);
    $this->assertEquals($expectedResultsBreakdown, $result['values']);
  }
}
