<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_TOILRequest as TOILRequestFabricator;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

/**
 * Class api_v3_LeaveRequestTest
 *
 * @group headless
 */
class api_v3_LeaveRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_TOILRequestHelpersTrait;
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
    $this->leaveRequestDayTypes = $this->getLeaveRequestDayTypes();
    $this->absenceType = AbsenceTypeFabricator::fabricate();
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

  public function testGetBalanceChangeByAbsenceTypeShouldIncludeBalanceForAllAbsenceTypes() {
    $absenceType1 = AbsenceTypeFabricator::fabricate();
    $absenceType2 = AbsenceTypeFabricator::fabricate();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType1->id
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType2->id
    ]);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement1->contact_id,
      'type_id' => $periodEntitlement1->type_id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+5 days'),
      'status_id' => 1
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement1->contact_id,
      'type_id' => $periodEntitlement1->type_id,
      'from_date' => CRM_Utils_Date::processDate('+8 days'),
      'to_date' => CRM_Utils_Date::processDate('+9 days'),
      'status_id' => 1
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement2->contact_id,
      'type_id' => $periodEntitlement2->type_id,
      'from_date' => CRM_Utils_Date::processDate('+20 days'),
      'to_date' => CRM_Utils_Date::processDate('+35 days'),
      'status_id' => 1
    ], true);

    $result = civicrm_api3('LeaveRequest', 'getBalanceChangeByAbsenceType', [
      'contact_id' => $periodEntitlement1->contact_id,
      'period_id' => $absencePeriod->id
    ]);

    $expectedResult = [
      $absenceType1->id => -7,
      $absenceType2->id => -16,
    ];

    $this->assertEquals($expectedResult, $result['values']);
  }

  public function testGetBalanceChangeByAbsenceTypeShouldReturn0ForAnAbsenceTypeWithNoLeaveRequests() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => $this->absenceType->id
    ]);

    $result = civicrm_api3('LeaveRequest', 'getBalanceChangeByAbsenceType', [
      'contact_id' => $periodEntitlement->contact_id,
      'period_id' => $absencePeriod->id
    ]);

    $expectedResult = [ $periodEntitlement->type_id => 0];

    $this->assertEquals($expectedResult, $result['values']);
  }

  public function testGetBalanceChangeByAbsenceTypeCanBeFilteredByStatuses() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => 1
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+5 days'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('+8 days'),
      'to_date' => CRM_Utils_Date::processDate('+9 days'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => CRM_Utils_Date::processDate('+20 days'),
      'to_date' => CRM_Utils_Date::processDate('+35 days'),
      'status_id' => $leaveRequestStatuses['Rejected']
    ], true);

    $result = civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => $periodEntitlement->contact_id,
      'period_id' => $absencePeriod->id,
      'statuses' => ['IN' => [$leaveRequestStatuses['Approved'], $leaveRequestStatuses['Rejected']]]
    ]);
    $expectedResult = [$periodEntitlement->type_id => -21];
    $this->assertEquals($expectedResult, $result['values']);

    $result = civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => $periodEntitlement->contact_id,
      'period_id' => $absencePeriod->id,
      'statuses' => ['IN' => [$leaveRequestStatuses['Waiting Approval'], $leaveRequestStatuses['Rejected']]]
    ]);
    $expectedResult = [$periodEntitlement->type_id => -18];
    $this->assertEquals($expectedResult, $result['values']);
  }

  public function testGetBalanceChangeByAbsenceTypeCanBeFilteredForPublicHolidays() {
    $absenceType = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => true]);

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType->id,
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = date('Y-m-d', strtotime('+40 days'));

    PublicHolidayLeaveRequestFabricator::fabricate($periodEntitlement->contact_id, $publicHoliday);

    // Passing the public_holiday param, it will sum the balance only for the
    // public holidays
    $publicHolidaysOnly = true;
    $result = civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => $periodEntitlement->contact_id,
      'period_id' => $absencePeriod->id,
      'public_holiday' => $publicHolidaysOnly
    ]);
    $expectedResult = [$absenceType->id => -1];
    $this->assertEquals($expectedResult, $result['values']);

    // Without passing the public_holiday param, it will sum the balance
    // for everything, except the public holidays
    $result = civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => $periodEntitlement->contact_id,
      'period_id' => $absencePeriod->id,
    ]);
    $expectedResult = [$absenceType->id => -2];
    $this->assertEquals($expectedResult, $result['values']);
  }

  public function testGetBalanceChangeByAbsenceTypeDoesNotIncludeExpiredTOILWhenExpiredOnlyIsFalse() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days'),
    ]);

    $entitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $period->id,
      'type_id' => $this->absenceType->id,
    ]);

    $this->createLeaveRequestBalanceChange(
      $this->absenceType->id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-5 days'),
      CRM_Utils_Date::processDate('-4 days')
    );

    $result = civicrm_api3('LeaveRequest', 'getBalanceChangeByAbsenceType', [
      'contact_id' => $entitlement->contact_id,
      'period_id' => $period->id,
      'expired' => false
    ]);

    $expectedResult = [$this->absenceType->id => -2];
    $this->assertEquals($expectedResult, $result['values']);

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-5 days'),
      CRM_Utils_Date::processDate('-5 days'),
      3,
      CRM_Utils_Date::processDate('-1 days'),
      1
    );

    $result = civicrm_api3('LeaveRequest', 'getBalanceChangeByAbsenceType', [
      'contact_id' => $entitlement->contact_id,
      'period_id' => $period->id,
      'expired' => false
    ]);

    // -2 (From the leave request) + 3 (Accrued from TOIL)
    // The -1 from the expired TOIL will not be counted
    $expectedResult = [$this->absenceType->id => 1];
    $this->assertEquals($expectedResult, $result['values']);
  }

  public function testGetBalanceChangeByAbsenceTypeIncludesOnlyExpiredTOILAndBroughtForwardWhenExpiredOnlyIsTrue() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days'),
    ]);

    $entitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $period->id,
      'type_id' => $this->absenceType->id,
    ]);

    $this->createLeaveRequestBalanceChange(
      $this->absenceType->id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-5 days'),
      CRM_Utils_Date::processDate('-4 days')
    );

    $result = civicrm_api3('LeaveRequest', 'getBalanceChangeByAbsenceType', [
      'contact_id' => $entitlement->contact_id,
      'period_id' => $period->id,
      'expired' => true
    ]);

    // Nothing expired so far, so the balance will be 0
    $expectedResult = [$this->absenceType->id => 0];
    $this->assertEquals($expectedResult, $result['values']);

    $this->createExpiredBroughtForwardBalanceChange($entitlement->id, 10, 3);

    $result = civicrm_api3('LeaveRequest', 'getBalanceChangeByAbsenceType', [
      'contact_id' => $entitlement->contact_id,
      'period_id' => $period->id,
      'expired' => true
    ]);

    // Now we have 3 Brought Forward days expired
    $expectedResult = [$this->absenceType->id => -3];
    $this->assertEquals($expectedResult, $result['values']);

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-5 days'),
      CRM_Utils_Date::processDate('-5 days'),
      3,
      CRM_Utils_Date::processDate('-1 days'),
      1
    );

    $result = civicrm_api3('LeaveRequest', 'getBalanceChangeByAbsenceType', [
      'contact_id' => $entitlement->contact_id,
      'period_id' => $period->id,
      'expired' => true
    ]);

    // The TOIL Request has 1 day expired and only that will be included
    // -3 + -1
    $expectedResult = [$this->absenceType->id => -4];
    $this->assertEquals($expectedResult, $result['values']);
  }

  public function testGetBalanceChangeByAbsenceTypeCanReturnOnlyExpiredDaysForTOILRequestsWithSpecificStatuses() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days'),
    ]);

    $entitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $period->id,
      'type_id' => $this->absenceType->id,
    ]);

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Approved'],
      CRM_Utils_Date::processDate('-5 days'),
      CRM_Utils_Date::processDate('-5 days'),
      3,
      CRM_Utils_Date::processDate('-1 day'),
      1
    );

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Cancelled'],
      CRM_Utils_Date::processDate('-3 days'),
      CRM_Utils_Date::processDate('-3 days'),
      3,
      CRM_Utils_Date::processDate('-1 day'),
      3
    );

    $this->createExpiredTOILRequestBalanceChange(
      $entitlement->type_id,
      $entitlement->contact_id,
      $leaveRequestStatuses['Waiting Approval'],
      CRM_Utils_Date::processDate('-1 days'),
      CRM_Utils_Date::processDate('-1 days'),
      5,
      CRM_Utils_Date::processDate('-1 day'),
      2
    );

    $result = civicrm_api3('LeaveRequest', 'getBalanceChangeByAbsenceType', [
      'contact_id' => $entitlement->contact_id,
      'period_id' => $period->id,
      'statuses' => ['IN' => [$leaveRequestStatuses['Approved'], $leaveRequestStatuses['Cancelled']]],
      'expired' => true
    ]);

    $expectedResult = [$this->absenceType->id => -4];
    $this->assertEquals($expectedResult, $result['values']);

    $result = civicrm_api3('LeaveRequest', 'getBalanceChangeByAbsenceType', [
      'contact_id' => $entitlement->contact_id,
      'period_id' => $period->id,
      'statuses' => ['IN' => [$leaveRequestStatuses['Cancelled'], $leaveRequestStatuses['Waiting Approval']]],
      'expired' => true
    ]);

    $expectedResult = [$this->absenceType->id => -5];
    $this->assertEquals($expectedResult, $result['values']);

    $result = civicrm_api3('LeaveRequest', 'getBalanceChangeByAbsenceType', [
      'contact_id' => $entitlement->contact_id,
      'period_id' => $period->id,
      'statuses' => ['IN' => [$leaveRequestStatuses['Approved'], $leaveRequestStatuses['Waiting Approval']]],
      'expired' => true
    ]);

    $expectedResult = [$this->absenceType->id => -3];
    $this->assertEquals($expectedResult, $result['values']);
  }

  public function testGetDoesntReturnPublicHolidayLeaveRequestsIfThePublicHolidayParamIsNotPresentOrIsFalse() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    // The get endpoint only returns leave requests overlapping contracts, so
    // we need them in place
    HRJobContractFabricator::fabricate(['contact_id' => 1], ['period_start_date' => '-1 day']);
    HRJobContractFabricator::fabricate(['contact_id' => 2], ['period_start_date' => '-1 day']);

    $absenceType = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => true]);

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
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

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
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

  public function testGetIncludesToilLeaveRequests() {
    HRJobContractFabricator::fabricate(['contact_id' => 1], ['period_start_date' => '-1 day']);
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => $this->absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => $this->absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    $toilRequest = TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+5 day'),
      'to_date' => CRM_Utils_Date::processDate('+6 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 2,
      'duration' => 300,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get', ['sequential' => 1]);
    $this->assertCount(3, $result['values']);
    $this->assertEquals($leaveRequest1->id, $result['values'][0]['id']);
    $this->assertEquals($leaveRequest2->id, $result['values'][1]['id']);
    $this->assertEquals($toilRequest->leave_request_id, $result['values'][2]['id']);
  }

  public function testGetCanReturnALeaveRequestWhichOverlapsAContractWithoutEndDate() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate(['contact_id' => 1], ['period_start_date' => '2016-01-01']);

    //This leave request is before the contract start date and will not be returned
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-12-30'),
      'to_date' => CRM_Utils_Date::processDate('2015-12-31'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    //This will be returned as it is after the contract start date
    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
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

    HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    //This leave request is before the contract start date and will not be returned
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-12-30'),
      'to_date' => CRM_Utils_Date::processDate('2015-12-31'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    // This will be returned
    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-03'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    // This will be returned
    $leaveRequest3 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-09-07'),
      'to_date' => CRM_Utils_Date::processDate('2016-09-08'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    //This will not be returned as it is after the contract start date
    LeaveRequestFabricator::fabricateWithoutValidation([
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

    HRJobContractFabricator::fabricate(['contact_id' => 1], ['period_start_date' => '2016-01-01']);

    //This leave request is before the contract start date and will not be returned
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-12-30'),
      'to_date' => CRM_Utils_Date::processDate('2015-12-30'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Cancelled']
    ], true);

    // This will be returned as it's after the contract start date
    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2017-09-02'),
      'to_date' => CRM_Utils_Date::processDate('2017-09-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    // This will be returned as it's after the contract start date as well
    $leaveRequest3 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2018-01-02'),
      'to_date' => CRM_Utils_Date::processDate('2018-01-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get');
    $this->assertCount(2, $result['values']);
    $this->assertNotEmpty($result['values'][$leaveRequest2->id]);
    $this->assertNotEmpty($result['values'][$leaveRequest3->id]);
  }

  public function testGetCanReturnLeaveRequestsWithoutToDateWhichOverlapAContractWithEndDate() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    //This leave request is before the contract start date and will not be returned
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-12-30'),
      'to_date' => CRM_Utils_Date::processDate('2015-12-30'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Cancelled']
    ], true);

    // This will be returned
    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    // This will be returned
    $leaveRequest3 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    //This will not be returned as it is after the contract start date
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2017-12-30'),
      'to_date' => CRM_Utils_Date::processDate('2017-12-30'),
      'from_date_type' => 1,
      'to_date_type' => 1,
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
    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    // This will be returned. The balance change will be -4
    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-02-23'),
      'from_date_type' => 1,
      'to_date_type' => 1,
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

  public function testGetFullShouldNotIncludeTheBalanceChangeIfTheReturnOptionIsNotEmptyAndDoesntIncludeIt() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => 1 ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-02-20'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    $result = civicrm_api3('LeaveRequest', 'getFull', [
      'sequential' => 1,
      'return' => ['id', 'dates']]
    );

    $expectedValues = [
      [
        'id' => $leaveRequest1->id,
        'dates' => $this->createLeaveRequestDatesArray($leaveRequest1)
      ],
      [
        'id' => $leaveRequest2->id,
        'dates' => $this->createLeaveRequestDatesArray($leaveRequest2)
      ]
    ];

    $this->assertEquals($expectedValues, $result['values']);
  }

  public function testGetFullShouldNotIncludeTheDatesIfTheReturnOptionIsNotEmptyAndDoesntIncludeIt() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => 1 ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-02-20'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    $result = civicrm_api3('LeaveRequest', 'getFull', [
      'sequential' => 1,
      'return' => ['id', 'balance_change']]
    );

    $expectedValues = [
      [
        'id' => $leaveRequest1->id,
        'balance_change' => -1
      ],
      [
        'id' => $leaveRequest2->id,
        'balance_change' => -1
      ]
    ];

    $this->assertEquals($expectedValues, $result['values']);
  }

  public function testGetFullIncludesBalanceChangesAndDatesForToilLeaveRequests() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => 1 ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-02-20'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    $toilRequest = TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-21'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-02-21'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 8,
      'duration' => 300,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ], true);

    $result = civicrm_api3('LeaveRequest', 'getFull', [
        'sequential' => 1,
        'return' => ['id', 'balance_change', 'dates']]
    );

    $toilLeaveRequestBao = LeaveRequest::findById($toilRequest->leave_request_id);
    $expectedValues = [
      [
        'id' => $leaveRequest1->id,
        'balance_change' => -1,
        'dates' => $this->createLeaveRequestDatesArray($leaveRequest1)
      ],
      [
        'id' => $leaveRequest2->id,
        'balance_change' => -1,
        'dates' => $this->createLeaveRequestDatesArray($leaveRequest2)
      ],
      [
        'id' => $toilRequest->leave_request_id,
        'balance_change' => 8,
        'dates' => $this->createLeaveRequestDatesArray($toilLeaveRequestBao)
      ]
    ];

    $this->assertEquals($expectedValues, $result['values']);
  }

  public function testGetFullShouldNotIncludeTheBalanceChangeAndDatesIfTheReturnOptionIsNotEmptyAndDoesntIncludeThem() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => 1 ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-02-20'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved']
    ], true);

    $result = civicrm_api3('LeaveRequest', 'getFull', [
        'sequential' => 1,
        'return' => ['id', 'type_id']]
    );

    $expectedValues = [
      [
        'id' => $leaveRequest1->id,
        'type_id' => 1
      ],
      [
        'id' => $leaveRequest2->id,
        'type_id' => 1
      ]
    ];

    $this->assertEquals($expectedValues, $result['values']);
  }

  public function testGetDoesNotReturnALeaveRequestNotOverlappingAContractEvenIfItMatchesTheDatesParams() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    //This leave request matches the date params, but not the contract dates,
    //so it will not be returned
    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-12-30'),
      'to_date' =>  CRM_Utils_Date::processDate('2015-12-30'),
      'from_date_type' => 1,
      'to_date_type' => 1,
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
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id, from_date, from_date_type
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
      'from_date' => '2016-11-05',
      'from_date_type' => $this->leaveRequestDayTypes['1/2 AM']['value'],
      'to_date' => '2016-11-10',
      'to_date_type' => $this->leaveRequestDayTypes['1/2 PM']['value'],
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: from_date
   */
  public function testCalculateBalanceChangeShouldNotAllowParamsWithoutFromDate() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => 1,
      'from_date_type' => $this->leaveRequestDayTypes['1/2 AM']['value'],
      'to_date' => '2016-11-10',
      'to_date_type' => $this->leaveRequestDayTypes['1/2 PM']['value'],
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: from_date_type
   */
  public function testCalculateBalanceChangeShouldNotAllowParamsWithoutFromType() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => 1,
      'from_date' => '2016-11-05',
      'to_date' => '2016-11-10',
      'to_date_type' => $this->leaveRequestDayTypes['1/2 PM']['value'],
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: to_date
   */
  public function testCalculateBalanceChangeShouldNotAllowParamsWithoutToDate() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => 1,
      'from_date' => '2016-11-05',
      'from_date_type' => $this->leaveRequestDayTypes['1/2 PM']['value'],
      'to_date_type' => $this->leaveRequestDayTypes['1/2 PM']['value'],
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: to_date_type
   */
  public function testCalculateBalanceChangeShouldNotAllowParamsWithoutToDateType() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => 1,
      'from_date' => '2016-11-05',
      'from_date_type' => $this->leaveRequestDayTypes['1/2 PM']['value'],
      'to_date' => '2016-11-05',
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage from_date is not a valid date: 2016-19-05
   */
  public function testCalculateBalanceChangeShouldNotAllowInvalidDate() {
    civicrm_api3('LeaveRequest', 'calculateBalanceChange', [
      'contact_id' => 1,
      'from_date' => '2016-19-05',
      'from_date_type' => $this->leaveRequestDayTypes['1/2 AM']['value'],
      'to_date' => '2016-11-10',
      'to_date_type' => $this->leaveRequestDayTypes['1/2 PM']['value']
    ]);
  }

  public function testCalculateBalanceChangeWithAllRequiredParameters() {
    $periodStartDate = date('Y-01-01');

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => 1],
      ['period_start_date' => $periodStartDate]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();

    //attach the work pattern to the contact
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contract['contact_id'],
      'pattern_id' => $workPattern->id
    ]);

    $fromDate = date('2016-11-13');
    $toDate = date('2016-11-15');
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
      'contact_id' => $contract['contact_id'],
      'from_date' => $fromDate,
      'from_date_type' => $fromType,
      'to_date' => $toDate,
      'to_date_type' => $toType
    ]);
    $this->assertEquals($expectedResultsBreakdown, $result['values']);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenStartDateIsEmpty() {
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_empty_from_date']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenContactIDIsEmpty() {
    $fromDate = new DateTime('+4 days');
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'contact_id' => ['leave_request_empty_contact_id']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenTypeIDIsEmpty() {
    $fromDate = new DateTime('+4 days');
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'status_id' => 1,
      'contact_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'type_id' => ['leave_request_empty_type_id']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenStatusIDIsEmpty() {
    $fromDate = new DateTime('+4 days');
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'status_id' => ['leave_request_empty_status_id']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenToDateIsNotEmptyAndToDateTypeIsEmpty() {
    $toDate= new DateTime('+4 days');
    $fromDate = new DateTime();
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'to_date_type' => ['leave_request_empty_to_date_type']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenRequestTypeIsEmpty() {
    $toDate= new DateTime('+4 days');
    $fromDate = new DateTime();
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'request_type' => ['leave_request_empty_request_type']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenRequestTypeIsToilAndToilDurationIsEmpty() {
    $toDate= new DateTime('+4 days');
    $fromDate = new DateTime();
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'toil_duration' => ['leave_request_empty_toil_duration']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenRequestTypeIsToilAndToilToAccrueIsEmpty() {
    $toDate= new DateTime('+4 days');
    $fromDate = new DateTime();
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL,
      'toil_duration' => 1
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'toil_to_accrue' => ['leave_request_empty_toil_to_accrue']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenRequestTypeIsNotToilAndToilDurationIsNotEmpty() {
    $toDate= new DateTime('+4 days');
    $fromDate = new DateTime();
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_SICKNESS,
      'toil_duration' => 1
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'toil_duration' => ['leave_request_non_empty_toil_duration']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenRequestTypeIsNotToilAndToilToAccrueIsNotEmpty() {
    $toDate= new DateTime('+4 days');
    $fromDate = new DateTime();
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE,
      'toil_to_accrue' => 1
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'toil_to_accrue' => ['leave_request_non_empty_toil_to_accrue']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenRequestTypeIsNotToilAndToilExpiryDateIsNotEmpty() {
    $toDate= new DateTime('+4 days');
    $fromDate = new DateTime();
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE,
      'toil_expiry_date' => $toDate->format('Y-m-d')
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'toil_expiry_date' => ['leave_request_non_empty_toil_expiry_date']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenRequestTypeIsSicknessAndSicknessReasonIsEmpty() {
    $toDate= new DateTime('+4 days');
    $fromDate = new DateTime();
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_SICKNESS,
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'sickness_reason' => ['leave_request_empty_sickness_reason']
      ]
    ];

    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenRequestTypeIsNotSicknessAndSicknessReasonIsNotEmpty() {
    $toDate= new DateTime('+4 days');
    $fromDate = new DateTime();
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE,
      'sickness_reason' => 1,
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'sickness_reason' => ['leave_request_non_empty_sickness_reason']
      ]
    ];

    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenRequestTypeIsNotSicknessAndSicknessRequiredDocumentsIsNotEmpty() {
    $toDate= new DateTime('+4 days');
    $fromDate = new DateTime();
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE,
      'sickness_required_documents' => '1',
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'sickness_required_documents' => ['leave_request_non_empty_sickness_required_documents']
      ]
    ];

    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenEndDateIsGreaterThanStartDate() {
    $fromDate = new DateTime('+4 days');
    $toDate = new DateTime();
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_from_date_greater_than_end_date']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenThereAreOverlappingLeaveRequests() {
    $contactID = 1;
    $fromDate1 = new DateTime('2016-11-02');
    $toDate1 = new DateTime('2016-11-04');

    $fromDate2 = new DateTime('2016-11-05');
    $toDate2 = new DateTime('2016-11-10');

    AbsencePeriodFabricator::fabricate([
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

    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_overlaps_another_leave_request']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenBalanceChangeGreaterThanPeriodEntitlementBalanceChangeAndAllowOveruseFalse() {
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

    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => $absenceType->id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'type_id' => ['leave_request_balance_change_greater_than_remaining_balance']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenLeaveRequestHasNoWorkingDay() {
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
      [
        'period_start_date' => $periodStartDate
      ]
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

    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => $this->absenceType->id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_doesnt_have_working_day']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenTheDatesAreNotContainedInValidAbsencePeriod() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    //the dates are outside of the absence period dates
    $fromDate = new DateTime('2015-11-12');
    $toDate = new DateTime('2015-11-13');
    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
    $toType = $this->leaveRequestDayTypes['All Day']['value'];

    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_not_within_absence_period']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenTheDatesOverlapMoreThanOneContract() {
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

    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => $periodEntitlement->type_id,
      'contact_id' => $periodEntitlement->contact_id,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_overlapping_multiple_contracts']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenTheAbsenceTypeIsNotActive() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'is_active' => 0
    ]);

    $fromDate = new DateTime();
    $toDate = new DateTime('+4 days');

    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'type_id' => ['leave_request_absence_type_not_active']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenLeaveDaysIsGreaterThanAbsenceTypeMaxConsecutiveLeaveDays() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'max_consecutive_leave_days' => 2
    ]);

    $fromDate = new DateTime();
    $toDate = new DateTime('+4 days');
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'type_id' => ['leave_request_days_greater_than_max_consecutive_days']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenUserCancelsOwnLeaveRequestAndAbsenceTypeDoesNotAllowIt() {
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

    //cancel leave request
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
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

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'type_id' => ['leave_request_absence_type_disallows_cancellation']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenUserCancelsOwnLeaveRequestAndAbsenceTypeAllowsItInAdvanceOfStartDateAndLeaveRequestFromDateIsLessThanToday() {
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

    //cancel leave request
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
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

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'type_id' => ['leave_request_past_days_cannot_be_cancelled']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldNotReturnErrorWhenValidationsPass() {
    $contactID = 1;

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

    $fromDate = new DateTime('2016-11-05');
    $toDate = new DateTime('2016-11-11');
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => $this->absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 0,
      'values' => []
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testCreateAlsoCreatesTheBalanceChangesForTheLeaveRequest() {
    $contactID = 1;
    $this->registerCurrentLoggedInContactInSession($contactID);
    $startDate = new DateTime();
    $endDate = new DateTime('+5 days');

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => $startDate->format('YmdHis'),
      'end_date' => $endDate->format('YmdHis'),
    ]);

    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => $startDate->format('Y-m-d')]
    );

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contract['contact_id'],
      'period_id' => $period->id,
      'type_id' => $this->absenceType->id,
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 20);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $result = civicrm_api3('LeaveRequest', 'create', [
      'contact_id' => $periodEntitlement->contact_id,
      'type_id' => $periodEntitlement->type_id,
      'from_date' => $startDate->format('Y-m-d'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $endDate->format('Y-m-d'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'status_id' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE,
      'sequential' => 1,
    ]);

    $leaveRequest = LeaveRequest::findById($result['values'][0]['id']);
    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $this->assertCount(6, $balanceChanges);
  }

  public function testDeleteAlsoDeletesLeaveRequestAndItsBalanceChangesFor() {
    $startDate = new DateTime();
    $endDate = new DateTime('+5 days');

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => $startDate->format('Ymd'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $endDate->format('Ymd'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'status_id' => 1,
      'sequential' => 1,
    ], true);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $this->assertCount(6, $balanceChanges);

    civicrm_api3('LeaveRequest', 'delete', ['id' => $leaveRequest->id]);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $this->assertCount(0, $balanceChanges);

    try {
      $leaveRequest = LeaveRequest::findById($leaveRequest->id);
    } catch(Exception $e) {
      return;
    }

    $this->fail("Expected to not find the LeaveRequest with {$leaveRequest->id}, but it was found");
  }

  public function testGetAndGetFullShouldOnlyReturnTheLeaveRequestsOfStaffMembersManagedByTheContactOnTheManagedByParam() {
    $this->setLeaveApproverRelationshipTypes([
      'has Leaves Approved By',
      'has Leaves Managed By',
    ]);

    $manager1 = ContactFabricator::fabricate();
    $manager2 = ContactFabricator::fabricate();

    $staffMember1 = ContactFabricator::fabricate();
    $staffMember2 = ContactFabricator::fabricate();
    $staffMember3 = ContactFabricator::fabricate();
    $staffMember4 = ContactFabricator::fabricate();

    // We need the contracts because LeaveRequest.get only returns Leave Requests
    // overlapping contracts
    HRJobContractFabricator::fabricate(
      ['contact_id' => $staffMember1['id']],
      ['period_start_date' => '2016-01-01']
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $staffMember2['id']],
      ['period_start_date' => '2015-10-01']
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $staffMember3['id']],
      ['period_start_date' => '2014-08-23']
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $staffMember4['id']],
      ['period_start_date' => '2016-03-13']
    );

    // Set Leave Approvers for staffMembers 1, 2 and 3. Staff Member 4 won't have
    // a Leave Approver
    $this->setContactAsLeaveApproverOf($manager1, $staffMember1, null, null, true, 'has Leaves Approved By');
    $this->setContactAsLeaveApproverOf($manager2, $staffMember2, null, null, true, 'has Leaves Approved By');
    $this->setContactAsLeaveApproverOf($manager2, $staffMember3, null, null, true, 'has Leaves Managed By');

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $staffMember1['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $staffMember1['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ], true);

    $leaveRequest3 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $staffMember2['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-05'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-05'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ], true);

    $leaveRequest4 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $staffMember3['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-13'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-13'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ], true);

    $leaveRequest5 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $staffMember4['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-10-23'),
      'to_date' => CRM_Utils_Date::processDate('2016-10-23'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ], true);

    // On the Leave Requests of contacts managed by manager 1 (staff member 1) will
    // be returned
    $result = civicrm_api3('LeaveRequest', 'get', ['managed_by' => $manager1['id']]);
    $resultGetFull = civicrm_api3('LeaveRequest', 'getFull', ['managed_by' => $manager1['id']]);

    // Without managed_by, all leave requests should be returned
    $this->assertEquals(2, $result['count']);
    $this->assertNotEmpty($result['values'][$leaveRequest1->id]);
    $this->assertNotEmpty($result['values'][$leaveRequest2->id]);
    $this->assertEquals(2, $resultGetFull['count']);
    $this->assertNotEmpty($resultGetFull['values'][$leaveRequest1->id]);
    $this->assertNotEmpty($resultGetFull['values'][$leaveRequest2->id]);

    // On the Leave Requests of contacts managed by manager 2 (staff members 2 and 3) will
    // be returned
    $result = civicrm_api3('LeaveRequest', 'get', ['managed_by' => $manager2['id']]);
    $resultGetFull = civicrm_api3('LeaveRequest', 'getFull', ['managed_by' => $manager2['id']]);

    // Without managed_by, all leave requests should be returned
    $this->assertEquals(2, $result['count']);
    $this->assertNotEmpty($result['values'][$leaveRequest3->id]);
    $this->assertNotEmpty($result['values'][$leaveRequest4->id]);
    $this->assertEquals(2, $resultGetFull['count']);
    $this->assertNotEmpty($resultGetFull['values'][$leaveRequest3->id]);
    $this->assertNotEmpty($resultGetFull['values'][$leaveRequest4->id]);
  }

  public function testGetAndGetFullShouldOnlyReturnTheLeaveRequestsOfStaffMembersManagedByManagersWithAnActiveLeaveApproverRelationship() {
    $manager = ContactFabricator::fabricate();
    $staffMember = ContactFabricator::fabricate();

    // We need the contract because LeaveRequest.get only returns Leave Requests
    // overlapping contracts
    HRJobContractFabricator::fabricate(
      ['contact_id' => $staffMember['id']],
      ['period_start_date' => '2015-01-01']
    );

    // The relationship between the manager and the staff member was active
    // only until 2016-12-28
    $this->setContactAsLeaveApproverOf($manager, $staffMember, '2016-01-01', '2016-12-28');

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $staffMember['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get');
    $resultGetFull = civicrm_api3('LeaveRequest', 'getFull');

    // Without managed_by, all leave requests should be returned
    $this->assertEquals(1, $result['count']);
    $this->assertNotEmpty($result['values'][$leaveRequest->id]);
    $this->assertEquals(1, $resultGetFull['count']);
    $this->assertNotEmpty($resultGetFull['values'][$leaveRequest->id]);

    $result = civicrm_api3('LeaveRequest', 'get', ['managed_by' => $manager['id']]);
    $resultGetFull = civicrm_api3('LeaveRequest', 'getFull', ['managed_by' => $manager['id']]);

    // Even though the relationship was active during the Leave Request date,
    // it's not active today, so nothing will be returned
    $this->assertEquals(0, $result['count']);
    $this->assertEquals(0, $resultGetFull['count']);
  }

  public function testGetAndGetFullShouldOnlyReturnTheLeaveRequestsOfStaffMembersManagedByManagersWithAnEnabledLeaveApproverRelationship() {
    $manager = ContactFabricator::fabricate();
    $staffMember = ContactFabricator::fabricate();

    // We need the contract because LeaveRequest.get only returns Leave Requests
    // overlapping contracts
    HRJobContractFabricator::fabricate(
      ['contact_id' => $staffMember['id']],
      ['period_start_date' => '2010-01-01']
    );

    // Considering only the dates, the relationship would be active today,
    // but the is_active flag will be set to false, making it disabled
    $startDate = new DateTime('today');
    $endDate = new DateTime('+ 10 days');
    $enabled = false;
    $this->setContactAsLeaveApproverOf($manager, $staffMember, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'), $enabled);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $staffMember['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get');
    $resultGetFull = civicrm_api3('LeaveRequest', 'getFull');

    // Without managed_by, all leave requests should be returned
    $this->assertEquals(1, $result['count']);
    $this->assertNotEmpty($result['values'][$leaveRequest->id]);
    $this->assertEquals(1, $resultGetFull['count']);
    $this->assertNotEmpty($resultGetFull['values'][$leaveRequest->id]);

    $result = civicrm_api3('LeaveRequest', 'get', ['managed_by' => $manager['id']]);
    $resultGetFull = civicrm_api3('LeaveRequest', 'getFull', ['managed_by' => $manager['id']]);

    $this->assertEquals(0, $result['count']);
    $this->assertEquals(0, $resultGetFull['count']);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: leave_request_id
   */
  public function testIsManagedByShouldThrowAnExceptionIfLeaveRequestIDIsMissing() {
    civicrm_api3('LeaveRequest', 'isManagedBy', ['contact_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id
   */
  public function testIsManagedByShouldThrowAnExceptionIfContactIDIsMissing() {
    civicrm_api3('LeaveRequest', 'isManagedBy', ['leave_request_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: leave_request_id, contact_id
   */
  public function testIsManagedByShouldThrowAnExceptionIfBothTheContactIDAndLeaveRequestIDAreMissing() {
    civicrm_api3('LeaveRequest', 'isManagedBy');
  }

  public function testIsManagedByShouldReturnTrueIfTheContactOfTheGivenLeaveRequestIsManagedByTheGivenContactID() {
    $manager = ContactFabricator::fabricate();
    $staffMember = ContactFabricator::fabricate();

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $staffMember['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $this->setContactAsLeaveApproverOf($manager, $staffMember);

    $result = civicrm_api3('LeaveRequest', 'isManagedBy', [
      'leave_request_id' => $leaveRequest->id,
      'contact_id' => $manager['id']
    ]);

    $expected = [
      'is_error' => 0,
      'count' => 1,
      'version' => 3,
      'values' => true,
    ];

    $this->assertEquals($expected, $result);
  }

  public function testIsManagedByShouldReturnFalseIfTheContactOfTheGivenLeaveRequestIsNotManagedByTheGivenContactID() {
    $manager = ContactFabricator::fabricate();
    $staffMember = ContactFabricator::fabricate();

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $staffMember['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $result = civicrm_api3('LeaveRequest', 'isManagedBy', [
      'leave_request_id' => $leaveRequest->id,
      'contact_id' => $manager['id']
    ]);

    $expected = [
      'is_error' => 0,
      'count' => 1,
      'version' => 3,
      'values' => false,
    ];

    $this->assertEquals($expected, $result);
  }

  private function createLeaveRequestDatesArray(LeaveRequest $leaveRequest) {
    $dates = [];
    foreach ($leaveRequest->getDates() as $date) {
      $dates[] = [
        'id'   => $date->id,
        'date' => $date->date
      ];
    }

    return $dates;
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: leave_request_id
   */
  public function testAddCommentShouldThrowAnExceptionIfLeaveRequestIDIsMissing() {
    civicrm_api3('LeaveRequest', 'addcomment', [
      'text' => 'Random Commenter',
      'contact_id' => 1
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id
   */
  public function testAddCommentShouldThrowAnExceptionIfContactIDIsMissing() {
    civicrm_api3('LeaveRequest', 'addcomment', [
      'leave_request_id' => 1,
      'text' => 'Random Commenter',
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: text
   */
  public function testAddCommentShouldThrowAnExceptionIfTextIsMissing() {
    civicrm_api3('LeaveRequest', 'addcomment', [
      'leave_request_id' => 1,
      'contact_id' => 1,
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: leave_request_id
   */
  public function testGetCommentShouldThrowAnExceptionIfLeaveRequestIDIsMissing() {
    civicrm_api3('LeaveRequest', 'addcomment', []);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: comment_id
   */
  public function testDeleteCommentShouldThrowAnExceptionIfCommentIDIsMissing() {
    civicrm_api3('LeaveRequest', 'deletecomment', []);
  }

  public function testGetAndGetFullReturnsOnlyDataLinkedToLoggedInUserWhenUserIsNotALeaveApproverOrAdmin() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    $this->registerCurrentLoggedInContactInSession($contact1['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API'];

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact1['id'] ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact2['id'] ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact1['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact2['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-02-23'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($contact1['id'], $result['values'][0]['contact_id']);

    $result = civicrm_api3('LeaveRequest', 'getfull', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($contact1['id'], $result['values'][0]['contact_id']);
  }

  public function testGetAndGetFullReturnsOnlyDataLinkedToContactsThatLoggedInUserManagesWhenLoggedInUserIsALeaveApprover() {
    $manager = ContactFabricator::fabricate();
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    $this->registerCurrentLoggedInContactInSession($manager['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API'];

    $this->setContactAsLeaveApproverOf($manager, $contact2);

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact2['id'] ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact1['id'] ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact1['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact2['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-02-23'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($contact2['id'], $result['values'][0]['contact_id']);

    $result = civicrm_api3('LeaveRequest', 'getfull', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($contact2['id'], $result['values'][0]['contact_id']);
  }

  public function testGetAndGetFullReturnsOnlyDataLinkedToContactsThatLoggedInUserManagesWhenLoggedInUserIsALeaveApproverWithOneOfTheAvailableRelationships() {
    $this->setLeaveApproverRelationshipTypes([
      'approves leaves for',
      'manages things for',
    ]);

    $manager1 = ContactFabricator::fabricate();
    $manager2 = ContactFabricator::fabricate();
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();
    $contact3 = ContactFabricator::fabricate();

    $this->setContactAsLeaveApproverOf($manager1, $contact2, null, null, true, 'manages things for');
    $this->setContactAsLeaveApproverOf($manager2, $contact1, null, null, true, 'approves leaves for');
    $this->setContactAsLeaveApproverOf($manager2, $contact3, null, null, true, 'manages leaves for');

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact2['id'] ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact1['id'] ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact3['id'] ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    $leaveRequestContact1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact1['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact2['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-02-23'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    $leaveRequestContact3 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact3['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API'];

    // Manager1 only manages contact2 (though the 'manages things for' relationship),
    // so only contact2 leave requests will be returned
    $this->registerCurrentLoggedInContactInSession($manager1['id']);
    $result = civicrm_api3('LeaveRequest', 'get', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($contact2['id'], $result['values'][0]['contact_id']);

    $result = civicrm_api3('LeaveRequest', 'getfull', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($contact2['id'], $result['values'][0]['contact_id']);

    // Manager2 manages contact1 (through the 'approves leaves for' relationship),
    // and contact3 (through the 'manage things for' relationship), so leave
    // requests from both should be returned
    $this->registerCurrentLoggedInContactInSession($manager2['id']);
    $result = civicrm_api3('LeaveRequest', 'get', ['check_permissions' => true]);
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($contact1['id'], $result['values'][$leaveRequestContact1->id]['contact_id']);
    $this->assertEquals($contact3['id'], $result['values'][$leaveRequestContact3->id]['contact_id']);

    $result = civicrm_api3('LeaveRequest', 'getfull', ['check_permissions' => true]);
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($contact1['id'], $result['values'][$leaveRequestContact1->id]['contact_id']);
    $this->assertEquals($contact3['id'], $result['values'][$leaveRequestContact3->id]['contact_id']);
    $this->assertEquals($contact3['id'], $result['values'][$leaveRequestContact3->id]['contact_id']);
  }

  public function testGetAndGetFullReturnsAllDataWhenLoggedInUserHasViewAllContactsPermission() {
    $adminID = 1;
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    $this->registerCurrentLoggedInContactInSession($adminID);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API', 'view all contacts'];

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact1['id'] ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact2['id'] ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact1['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact2['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-02-23'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($contact1['id'], $result['values'][0]['contact_id']);
    $this->assertEquals($contact2['id'], $result['values'][1]['contact_id']);

    $result = civicrm_api3('LeaveRequest', 'getfull', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($contact1['id'], $result['values'][0]['contact_id']);
    $this->assertEquals($contact2['id'], $result['values'][1]['contact_id']);
  }

  public function testGetAndGetFullReturnsAllDataWhenLoggedInUserHasEditAllContactsPermission() {
    $adminID = 1;
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    $this->registerCurrentLoggedInContactInSession($adminID);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API', 'edit all contacts'];

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact1['id'] ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact2['id'] ],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-10-01'
      ]
    );

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact1['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-02'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact2['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-02-20'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-02-23'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'status_id' => 1
    ], true);

    $result = civicrm_api3('LeaveRequest', 'get', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($contact1['id'], $result['values'][0]['contact_id']);
    $this->assertEquals($contact2['id'], $result['values'][1]['contact_id']);

    $result = civicrm_api3('LeaveRequest', 'getfull', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($contact1['id'], $result['values'][0]['contact_id']);
    $this->assertEquals($contact2['id'], $result['values'][1]['contact_id']);
  }
}
