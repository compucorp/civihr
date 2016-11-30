<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;

/**
 * Class api_v3_LeaveRequestTest
 *
 * @group headless
 */
class api_v3_LeaveRequestTest extends BaseHeadlessTest {

  public function setUp() {
    // In order to make tests simpler, we disable the foreign key checks,
    // as a way to allow the creation of leave request records related
    // to a non-existing leave period entitlement
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

    // We delete everything two avoid problems with the default absence types
    // created during the extension installation
    $tableName = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");
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
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id, from_date, from_type, to_date, to_type
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
      'from_type' => "1/2 AM",
      'to_date' => "2016-11-10",
      'to_type' => "1/2 PM",
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: from_date
   */
  public function testCalculateBalanceChangeShouldNotAllowParamsWithoutFromDate() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => 1,
      'from_type' => "1/2 AM",
      'to_date' => "2016-11-10",
      'to_type' => "1/2 PM",
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: to_date
   */
  public function testCalculateBalanceChangeShouldNotAllowParamsWithoutToDate() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => 1,
      'from_date' => "2016-11-05",
      'from_type' => "1/2 AM",
      'to_type' => "1/2 PM",
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
      'to_type' => "1/2 PM",
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: to_type
   */
  public function testCalculateBalanceChangeShouldNotAllowParamsWithoutToType() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => 1,
      'from_date' => "2016-11-05",
      'from_type' => "1/2 AM",
      'to_date' => "2016-11-10",
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
      'from_type' => "1/2 AM",
      'to_date' => "2016-11-10",
      'to_type' => "1/2 PM",
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

    $contactId = $contact['id'];
    $fromDate = date("Y-11-05");
    $toDate = date("Y-11-08");
    $fromType = '1/2 AM';
    $toType = '1/2 AM';

    //Leave Request Start Date
    $startDate = new DateTime($fromDate);
    //Leave Request End Date
    $endDate = new DateTime($toDate);

    $endDateUnmodified = new DateTime($toDate);
    // add one day to end date to include it in DatePeriod
    $endDate->modify('+1 day');
    $interval   = new DateInterval('P1D');
    $datePeriod = new DatePeriod($startDate, $interval, $endDate);
    $expectedResultsBreakdown = [
      'amount' => 0,
      'breakdown' => []
    ];
    $fromDateIsHalfDay = in_array($fromType, ['1/2 AM', '1/2 PM']);
    $toDateIsHalfDay = in_array($toType, ['1/2 AM', '1/2 PM']);

    //mimick how LeaveRequest::calculateBalanceChange will handle this to get an expected result
    foreach ($datePeriod as $date) {
      if(in_array($date->format('N'), [6, 7])){
        $amount = 0;
        $type = 'Weekend';
      }
      else{
        $amount = -1 * 1.0;
        $type = 'All day';
      }

      if($fromDateIsHalfDay && $date->format('Y-m-d') == $startDate->format('Y-m-d') && $amount != 0) {
        $amount = -1 * 0.5;
        $type =  $fromType;
      }

      if($toDateIsHalfDay && $date->format('Y-m-d') == $endDateUnmodified->format('Y-m-d') && $amount != 0) {
        $amount = -1 * 0.5;
        $type =  $fromType;
      }

      $result = [
        'date' => $date->format('Y-m-d'),
        'amount' => abs($amount),
        'type' => $type
      ];
      $expectedResultsBreakdown['amount'] += $amount;
      $expectedResultsBreakdown['breakdown'][] = $result;

    }
    $result = civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => $contactId,
      'from_date' => $fromDate,
      'from_type' => $fromType,
      'to_date' => $toDate,
      'to_type' => $toType
    ]);
    $this->assertEquals($expectedResultsBreakdown, $result['values']);
  }

}
