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

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact['id'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+5 days'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact['id'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+8 days'),
      'to_date' => CRM_Utils_Date::processDate('+9 days'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
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

    LeaveRequestFabricator::fabricateWithoutValidation([
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

  public function testGetBalanceChangeByAbsenceTypeCanBeFilteredForExpiredOnly() {
    $contact = ContactFabricator::fabricate();

    $absenceType1 = AbsenceTypeFabricator::fabricate();
    $absenceType2 = AbsenceTypeFabricator::fabricate();

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType1->id,
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType2->id,
    ]);

    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $this->createExpiredBroughtForwardBalanceChange(
      $periodEntitlement1->id,
      5,
      4
    );

    $this->createExpiredTOILRequestBalanceChange(
      $periodEntitlement2->type_id,
      $periodEntitlement2->contact_id,
      $leaveRequestStatuses['Cancelled'],
      CRM_Utils_Date::processDate('-5 days'),
      CRM_Utils_Date::processDate('-5 days'),
      3,
      CRM_Utils_Date::processDate('-1 day'),
      2
    );

    $result = civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => $contact['id'],
      'period_id' => $absencePeriod->id,
      'expired' => true
    ]);
    $expectedResult = [$absenceType1->id => -4, $absenceType2->id => -2];
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

    $absenceType = AbsenceTypeFabricator::fabricate([
      'must_take_public_holiday_as_leave' => true,
      'allow_accruals_request' => true,
    ]);

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveRequestStatuses['Approved']
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveRequestStatuses['Waiting Approval']
    ], true);

    $toilRequest = TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+5 day'),
      'to_date' => CRM_Utils_Date::processDate('+6 days'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => 2,
      'duration' => 300,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    $result = civicrm_api3('LeaveRequest', 'get', ['sequential' => 1]);
    $this->assertCount(3, $result['values']);
    $this->assertEquals($leaveRequest1->id, $result['values'][0]['id']);
    $this->assertEquals($leaveRequest2->id, $result['values'][1]['id']);
    $this->assertEquals($toilRequest->leave_request_id, $result['values'][2]['id']);
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

    HRJobContractFabricator::fabricate([
      'contact_id' => 1
    ],
    [
      'period_start_date' => '2016-01-01',
      'period_end_date' => '2016-10-01'
    ]);

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

    HRJobContractFabricator::fabricate([
      'contact_id' => 1
    ],
    [
      'period_start_date' => '2016-01-01',
    ]);

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

    HRJobContractFabricator::fabricate([
      'contact_id' => 1
    ],
    [
      'period_start_date' => '2016-01-01',
      'period_end_date' => '2016-10-01'
    ]);

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
    ]);

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

    HRJobContractFabricator::fabricate([
      'contact_id' => 1
    ],
    [
      'period_start_date' => '2016-01-01',
      'period_end_date' => '2016-10-01'
    ]);

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

  public function testLeaveRequestIsValidShouldReturnErrorWhenStartDateIsEmpty() {
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date_type' => 1
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_empty_from_date']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenContactIDIsEmpty() {
    $fromDate = new DateTime('+4 days');
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'contact_id' => ['leave_request_empty_contact_id']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenTypeIDIsEmpty() {
    $fromDate = new DateTime('+4 days');
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'status_id' => 1,
      'contact_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'type_id' => ['leave_request_empty_type_id']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenStatusIDIsEmpty() {
    $fromDate = new DateTime('+4 days');
    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => 1
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'status_id' => ['leave_request_empty_status_id']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
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
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'to_date_type' => ['leave_request_empty_to_date_type']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
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
      'to_date_type' => 1
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_from_date_greater_than_end_date']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
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
      'to_date_type' => 1
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_overlaps_another_leave_request']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenBalanceChangeGreaterThanPeriodEntitlementBalanceChangeAndAllowOveruseFalse() {
    $contact = ContactFabricator::fabricate();
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
      'contact_id' => $contact['id'],
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 3);
    $periodStartDate = date('2016-01-01');
    $title = 'Job Title';

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate,
      'title' => $title
    ]);
    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id
    ]);

    $fromDate = new DateTime("2016-11-14");
    $toDate = new DateTime("2016-11-17");
    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $toType = $this->leaveRequestDayTypes['All Day']['id'];

    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => $absenceType->id,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'type_id' => ['leave_request_balance_change_greater_than_remaining_balance']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenLeaveRequestHasNoWorkingDay() {
    $contact = ContactFabricator::fabricate();
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => $contact['id'],
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 3);
    $periodStartDate = date('2016-01-01');
    $title = 'Job Title';

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate,
      'title' => $title
    ]);
    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id
    ]);

    //both days are on weekends
    $fromDate = new DateTime("2016-11-12");
    $toDate = new DateTime("2016-11-13");
    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $toType = $this->leaveRequestDayTypes['All Day']['id'];

    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => $absenceType->id,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_doesnt_have_working_day']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenTheDatesAreNotContainedInValidAbsencePeriod() {
    $contact = ContactFabricator::fabricate();
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    //the dates are outside of the absence period dates
    $fromDate = new DateTime("2015-11-12");
    $toDate = new DateTime("2015-11-13");
    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $toType = $this->leaveRequestDayTypes['All Day']['id'];

    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_not_within_absence_period']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testLeaveRequestIsValidShouldReturnErrorWhenTheDatesOverlapMoreThanOneContract() {
    $contact = ContactFabricator::fabricate();
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => $contact['id'],
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 30);
    $periodStartDate1 = date('2016-01-01');
    $periodEndDate1 = date('2016-06-30');

    $periodStartDate2 = date('2016-07-01');
    $periodEndDate2 = date('2016-12-31');

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate1,
      'period_end_date' => $periodEndDate1
    ]);

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate2,
      'period_end_date' => $periodEndDate2
    ]);

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id
    ]);

    //The from date and to date overlaps the two job contracts
    $fromDate = new DateTime("2016-06-25");
    $toDate = new DateTime("2016-07-13");
    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $toType = $this->leaveRequestDayTypes['All Day']['id'];

    $result = civicrm_api3('LeaveRequest', 'isvalid', [
      'type_id' => $absenceType->id,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_overlapping_multiple_contracts']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
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
      'to_date_type' => 1
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 0,
      'values' => []
    ];
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testCreateAlsoCreatesTheBalanceChangesForTheLeaveRequest() {
    $contact = ContactFabricator::fabricate();

    $startDate = new DateTime();
    $endDate = new DateTime('+5 days');

    $absenceType = AbsenceTypeFabricator::fabricate();

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => $startDate->format('YmdHis'),
      'end_date' => $endDate->format('YmdHis'),
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => $startDate->format('Y-m-d')]
    );

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'period_id' => $period->id,
      'type_id' => $absenceType->id,
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 20);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $result = civicrm_api3('LeaveRequest', 'create', [
      'contact_id' => $contact['id'],
      'type_id' => $absenceType->id,
      'from_date' => $startDate->format('Y-m-d'),
      'from_date_type' => $fromType = $this->leaveRequestDayTypes['All Day']['id'],
      'to_date' => $endDate->format('Y-m-d'),
      'to_date_type' => $fromType = $this->leaveRequestDayTypes['All Day']['id'],
      'status_id' => 1,
      'sequential' => 1,
    ]);

    $leaveRequest = LeaveRequest::findById($result['values'][0]['id']);
    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $this->assertCount(6, $balanceChanges);
  }

  public function testDeleteAlsoDeletesLeaveRequestAndItsBalanceChangesFor() {
    $contact = ContactFabricator::fabricate();

    $startDate = new DateTime();
    $endDate = new DateTime('+5 days');

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact['id'],
      'type_id' => 1,
      'from_date' => $startDate->format('Ymd'),
      'from_date_type' => $fromType = $this->leaveRequestDayTypes['All Day']['id'],
      'to_date' => $endDate->format('Ymd'),
      'to_date_type' => $fromType = $this->leaveRequestDayTypes['All Day']['id'],
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

  public function testGetShouldOnlyReturnTheLeaveRequestsOfStaffMembersManagedByTheContactOnTheManagedByParam() {
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
    $this->setContactAsLeaveApproverOf($manager1, $staffMember1);
    $this->setContactAsLeaveApproverOf($manager2, $staffMember2);
    $this->setContactAsLeaveApproverOf($manager2, $staffMember3);

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

    $result = civicrm_api3('LeaveRequest', 'get');

    // Without managed_by, all leave requests should be returned
    $this->assertEquals(5, $result['count']);
    $this->assertNotEmpty($result['values'][$leaveRequest1->id]);
    $this->assertNotEmpty($result['values'][$leaveRequest2->id]);
    $this->assertNotEmpty($result['values'][$leaveRequest3->id]);
    $this->assertNotEmpty($result['values'][$leaveRequest4->id]);
    $this->assertNotEmpty($result['values'][$leaveRequest5->id]);

    // On the Leave Requests of contacts managed by manager 1 (staff member 1) will
    // be returned
    $result = civicrm_api3('LeaveRequest', 'get', ['managed_by' => $manager1['id']]);

    // Without managed_by, all leave requests should be returned
    $this->assertEquals(2, $result['count']);
    $this->assertNotEmpty($result['values'][$leaveRequest1->id]);
    $this->assertNotEmpty($result['values'][$leaveRequest2->id]);

    // On the Leave Requests of contacts managed by manager 2 (staff members 2 and 3) will
    // be returned
    $result = civicrm_api3('LeaveRequest', 'get', ['managed_by' => $manager2['id']]);

    // Without managed_by, all leave requests should be returned
    $this->assertEquals(2, $result['count']);
    $this->assertNotEmpty($result['values'][$leaveRequest3->id]);
    $this->assertNotEmpty($result['values'][$leaveRequest4->id]);
  }

  public function testGetShouldOnlyReturnTheLeaveRequestsOfStaffMembersManagedByManagersWithAnActiveLeaveApproverRelationship() {
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

    // Without managed_by, all leave requests should be returned
    $this->assertEquals(1, $result['count']);
    $this->assertNotEmpty($result['values'][$leaveRequest->id]);

    $result = civicrm_api3('LeaveRequest', 'get', ['managed_by' => $manager['id']]);

    // Even though the relationship was active during the Leave Request date,
    // it's not active today, so nothing will be returned
    $this->assertEquals(0, $result['count']);
  }

  public function testGetShouldOnlyReturnTheLeaveRequestsOfStaffMembersManagedByManagersWithAnEnabledLeaveApproverRelationship() {
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

    // Without managed_by, all leave requests should be returned
    $this->assertEquals(1, $result['count']);
    $this->assertNotEmpty($result['values'][$leaveRequest->id]);

    $result = civicrm_api3('LeaveRequest', 'get', ['managed_by' => $manager['id']]);

    $this->assertEquals(0, $result['count']);
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
}
