<?php

use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequest as LeaveRequestService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;


/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveRequestStatusMatrixHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;


  private $leaveBalanceChangeService;

  private $leaveContact;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
    $this->leaveBalanceChangeService = new LeaveBalanceChangeService();

    $this->leaveContact = 1;
    $this->registerCurrentLoggedInContactInSession($this->leaveContact);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $this->leaveRequestStatuses = $this->getLeaveRequestStatuses();
  }

  public function testCreateAlsoCreateTheLeaveRequestBalanceChanges() {
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);

    // a 7 days leave request, from monday to sunday
    $leaveRequest = $this->getleaveRequestService()->create([
      'type_id' => 1,
      'contact_id' => $this->leaveContact,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-01-04'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-10'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['all_day']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ], false);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used, and it this is a week long
    // leave request, the balance will be -5 (for the 5 working days)
    $this->assertEquals(-5, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is 5, we must have 7 balance changes, one for
    // each date
    $this->assertCount(7, $balanceChanges);
  }

  public function testCreateDoesNotDuplicateLeaveBalanceChangesOnUpdate() {
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);

    // a 7 days leave request, from friday to thursday
    $params = [
      'type_id' => 1,
      'contact_id' => $this->leaveContact,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-07'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['all_day']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];

    $leaveRequest = $this->getleaveRequestService()->create($params, false);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used, and it this is a week long
    // leave request, the balance will be 5 (for the 5 working days)
    $this->assertEquals(-5, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is 5, we must have 7 balance changes, one for
    // each date
    $this->assertCount(7, $balanceChanges);

    // Increase the Leave Request period by 4 days (2 weekend + 2 working days)
    $params['id'] = $leaveRequest->id;
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-11');
    $this->getleaveRequestService()->create($params, false);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // 5 from before + 2 (from the 2 new working days)
    $this->assertEquals(-7, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // 7 from before + 4 from the new period
    $this->assertCount(11, $balanceChanges);
  }

  public function testDeleteSoftDeletesTheLeaveRequest() {
    $leaveRequestDateTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $leaveRequestDateTypes['all_day'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-07'),
      'to_date_type' => $leaveRequestDateTypes['all_day'],
    ], TRUE);

    $this->getLeaveRequestServiceWhenCurrentUserIsAdmin()->delete($leaveRequest->id);

    $leaveRequestRecord = new LeaveRequest();
    $leaveRequestRecord->id = $leaveRequest->id;
    $leaveRequestRecord->find(true);
    $this->assertEquals(1, $leaveRequestRecord->is_deleted);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to create or update a leave request for this employee
   */
  public function testCreateThrowsAnExceptionWhenCurrentUserDoesNotHaveCreateAndUpdateLeaveRequestPermission() {
    //logged in user has no permissions, also a contactID different from that of the logged in user is passed
    $contactID = 2;
    $params  = $this->getDefaultParams(['contact_id' => $contactID]);
    $this->getleaveRequestService()->create($params, false);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You can't create a Leave Request with this status
   */
  public function testCreateThrowsAnExceptionWhenTransitionStatusIsNotValidForNewLeaveRequest() {
    $this->getLeaveRequestServiceWhenStatusTransitionIsNotAllowed()->create($this->getDefaultParams(), false);
  }

  public function testCreateThrowsAnExceptionWhenTransitionStatusIsNotValidWhenUpdatingLeaveRequestStatus() {
    $params = $this->getDefaultParams();
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $this->setExpectedException(
      'RuntimeException', "You can't change the Leave Request status from ".
      $this->getDefaultParams()['status_id']. " to {$this->leaveRequestStatuses['awaiting_approval']['id']}"
    );

    $params['id'] = $leaveRequest->id;
    $params['status_id'] = $this->leaveRequestStatuses['awaiting_approval']['id'];

    $this->getLeaveRequestServiceWhenStatusTransitionIsNotAllowed()->create($params, false);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to change the request dates
   */
  public function testCreateThrowsAnExceptionWhenLeaveApproverUpdatesDatesForLeaveRequest() {
    $contactID = 5;
    $params = $this->getDefaultParams(['contact_id' => $contactID]);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $params['from_date'] = CRM_Utils_Date::processDate('2016-01-10');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-15');
    $params['id'] = $leaveRequest->id;

    $this->getLeaveRequestServiceWhenCurrentUserIsLeaveManager()->create($params, false);
  }

  public function testCreateDoesNotThrowAnExceptionWhenAdminUpdatesDatesForLeaveRequest() {
    $params = $this->getDefaultParams(['status_id' => 2]);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $toDate = new DateTime($params['to_date']);
    $params['to_date'] = $toDate->modify('+10 days')->format('YmdHis');
    $params['id'] = $leaveRequest->id;

    $this->getLeaveRequestServiceWhenCurrentUserIsAdmin()->create($params, false);
    $this->assertNotNull($leaveRequest->id);
  }

  /**
   * @dataProvider openLeaveRequestStatusesDataProvider
   */
  public function testCreateDoesNotThrowAnExceptionWhenLeaveManagerUpdatesDatesForAnOpenSicknessRequest($status) {
    $params = $this->getDefaultParams([
      'contact_id' => 5,
      'status_id' => $status,
      'request_type' => LeaveRequest::REQUEST_TYPE_SICKNESS
    ]);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $toDate = new DateTime($params['to_date']);
    $params['to_date'] = $toDate->modify('+10 days')->format('YmdHis');
    $params['id'] = $leaveRequest->id;

    $this->getLeaveRequestServiceWhenCurrentUserIsLeaveManager()->create($params, false);
  }

  /**
   * @dataProvider openLeaveRequestStatusesDataProvider
   */
  public function testCreateDoesNotThrowAnExceptionWhenAdminUpdatesDatesForAnOpenSicknessRequest($status) {
    $params = $this->getDefaultParams([
      'contact_id' => 5,
      'status_id' => $status,
      'request_type' => LeaveRequest::REQUEST_TYPE_SICKNESS
    ]);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $toDate = new DateTime($params['to_date']);
    $params['to_date'] = $toDate->modify('+10 days')->format('YmdHis');
    $params['id'] = $leaveRequest->id;

    $this->getLeaveRequestServiceWhenCurrentUserIsAdmin()->create($params, false);
  }

  /**
   * @dataProvider closedLeaveRequestStatusesDataProvider
   *
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to change the request dates
   */
  public function testCreateThrowsAnExceptionWhenLeaveContactUpdatesDatesForAClosedSicknessRequest($status) {
    $params = $this->getDefaultParams([
      'status_id' => $status,
      'request_type' => LeaveRequest::REQUEST_TYPE_SICKNESS
    ]);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $toDate = new DateTime($params['to_date']);
    $params['to_date'] = $toDate->modify('+10 days')->format('YmdHis');
    $params['id'] = $leaveRequest->id;

    $this->getLeaveRequestService()->create($params, false);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to change the type of a request
   */
  public function testCreateThrowsAnExceptionWhenLeaveApproverUpdatesAbsenceTypeForLeaveRequest() {
    $contactID = 5;
    $params = $this->getDefaultParams(['contact_id' => $contactID]);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $params['id'] = $leaveRequest->id;
    $params['type_id'] = 2;

    $this->getLeaveRequestServiceWhenCurrentUserIsLeaveManager()->create($params, false);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to change the type of a request
   */
  public function testCreateThrowsAnExceptionWhenAdminUpdatesAbsenceTypeForLeaveRequest() {
    $contactID = 5;
    $params = $this->getDefaultParams(['contact_id' => $contactID]);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $params['id'] = $leaveRequest->id;
    $params['type_id'] = 2;

    $this->getLeaveRequestServiceWhenCurrentUserIsAdmin()->create($params, false);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to delete a leave request for this employee
   */
  public function testDeleteThrowsAnExceptionWhenLeaveApproverTriesToDeleteALeaveRequest() {
    $contactID = 5;
    $params = $this->getDefaultParams(['contact_id' => $contactID]);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);
    $this->getLeaveRequestServiceWhenCurrentUserIsLeaveManager()->delete($leaveRequest->id);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to delete a leave request for this employee
   */
  public function testDeleteThrowsAnExceptionWhenLeaveContactTriesToDeleteALeaveRequest() {
    $params = $this->getDefaultParams();
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);
    $this->getLeaveRequestService()->delete($leaveRequest->id);
  }

  private function getLeaveRequestService($isAdmin = false, $isManager = false, $allowStatusTransition = true, $mockBalanceChangeService = false) {
    $leaveManagerService = $this->createLeaveManagerServiceMock($isAdmin, $isManager);
    $leaveRequestStatusMatrixService = $this->createLeaveRequestStatusMatrixServiceMock($allowStatusTransition);
    $leaveRequestRightsService = new LeaveRequestRightsService($leaveManagerService);
    $leaveBalanceChangeService = $this->leaveBalanceChangeService;

    if($mockBalanceChangeService) {
      $leaveBalanceChangeService = $this->createLeaveBalanceChangeServiceMock();
    }

    return new LeaveRequestService(
      $leaveBalanceChangeService,
      $leaveRequestStatusMatrixService,
      $leaveRequestRightsService
    );
  }

  public function testLeaveRequestServiceCallsRecalculateExpiredBalanceChangesForLeaveRequestPastDatesMethodWhenALeaveRequestHasPastDates() {
    $params = $this->getDefaultParams([
      'from_date' => CRM_Utils_Date::processDate('-2 days'),
      'to_date' => CRM_Utils_Date::processDate('+1 day'),
      'status' => 1
    ]);

    $this->getLeaveRequestServiceWhenCurrentUserIsAdminWithBalanceChangeServiceMock()->create($params, false);
  }

  private function getLeaveRequestServiceWhenStatusTransitionIsNotAllowed() {
    return $this->getLeaveRequestService(false, false, false);
  }

  private function getLeaveRequestServiceWhenCurrentUserIsAdmin() {
    return $this->getLeaveRequestService(true, false);
  }

  private function getLeaveRequestServiceWhenCurrentUserIsLeaveManager() {
    return $this->getLeaveRequestService(false, true);
  }

  private function getLeaveRequestServiceWhenCurrentUserIsAdminWithBalanceChangeServiceMock() {
    return $this->getLeaveRequestService(true, false, true, true);
  }
  private function getDefaultParams($params = []) {
    $defaultParams =  [
      'type_id' => 1,
      'contact_id' => $this->leaveContact,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-04'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['all_day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-10'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['all_day']['value'],
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];
    return array_merge($defaultParams, $params);
  }

  public function testBalanceChangeIsUpdatedForAnExistingLeaveRequestWhenChangeBalanceParameterIsTrueAndDatesDidNotChange() {
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => '2016-01-01']
    );

    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);

    //Leave dates on Monday to Friday, all working days
    $leaveDates = [
      'from_date' => CRM_Utils_Date::processDate('2016-02-08'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-12')
    ];

    $params = $this->getDefaultParams($leaveDates);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    //Just to make sure that we have the expected balance change for the leave request
    $previousBalance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    $this->assertEquals(-5, $previousBalance);

    //Add a work pattern for the contact with effective date before the leave dates
    $workPattern1 = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $this->leaveContact,
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-02-01')
    ]);

    //If the leave request is to be updated with new balance change, the balance would have changed.
    $result = LeaveRequest::calculateBalanceChange($this->leaveContact,
      new DateTime($params['from_date']),
      $params['from_date_type'],
      new DateTime($params['to_date']),
      $params['to_date_type']
    );

    $newBalance = $result['amount'];

    //The balance for leave request has changed since the contact work pattern has
    //been changed for the date range that the leave was initially requested.
    $this->assertNotEquals($previousBalance, $newBalance);

    //update leave request and request a change in balance to the new balance
    $params['id'] = $leaveRequest->id;
    $params['change_balance'] = 1;

    $leaveRequest = $this->getleaveRequestService()->create(
      $params,
      false
    );

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    $this->assertEquals($newBalance, $balance);
  }

  public function testBalanceChangeIsNotUpdatedForAnExistingLeaveRequestWhenChangeBalanceParameterIsFalseAndDatesDidNotChange() {
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => '2016-01-01']
    );

    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);

    //Leave dates on Monday to Friday, all working days
    $leaveDates = [
      'from_date' => CRM_Utils_Date::processDate('2016-02-08'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-12')
    ];

    $params = $this->getDefaultParams($leaveDates);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    //Just to make sure that we have the expected balance change for the leave request
    $previousBalance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    $this->assertEquals(-5, $previousBalance);

    //Add a work pattern for the contact with effective date before the leave dates
    $workPattern1 = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $this->leaveContact,
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-02-01')
    ]);

    //If the leave request is to be updated with new balance change, the balance would have changed.
    $result = LeaveRequest::calculateBalanceChange($this->leaveContact,
      new DateTime($params['from_date']),
      $params['from_date_type'],
      new DateTime($params['to_date']),
      $params['to_date_type']
    );
    $newBalance = $result['amount'];

    //The balance for leave request has changed since the contact work pattern has
    //been changed for the date range that the leave was initially requested.
    $this->assertNotEquals($previousBalance, $newBalance);

    //update leave request and request that the previous balance be retained
    //even though the balance has changed
    $params['id'] = $leaveRequest->id;
    $params['change_balance'] = 0;

    $leaveRequest = $this->getleaveRequestService()->create(
      $params,
      false
    );

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    $this->assertEquals($previousBalance, $balance);
  }

  public function testBalanceChangeIsUpdatedForAnExistingLeaveRequestWhenChangeBalanceParameterIsTrueAndDatesChanged() {
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => '2016-01-01']
    );

    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);

    //Leave dates on Monday to Friday, all working days
    $leaveDates = [
      'from_date' => CRM_Utils_Date::processDate('2016-02-08'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-12')
    ];

    $params = $this->getDefaultParams($leaveDates);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    //Just to make sure that we have the expected balance change for the leave request
    $previousBalance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    $this->assertEquals(-5, $previousBalance);

    //Add a work pattern for the contact with effective date before the leave dates
    $workPattern1 = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $this->leaveContact,
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-02-01')
    ]);

    //If the leave request is to be updated with new balance change, the balance would have changed.
    $result = LeaveRequest::calculateBalanceChange($this->leaveContact,
      new DateTime($params['from_date']),
      $params['from_date_type'],
      new DateTime($params['to_date']),
      $params['to_date_type']
    );

    $newBalance = $result['amount'];

    //The balance for leave request has changed since the contact work pattern has
    //been changed for the date range that the leave was initially requested.
    $this->assertNotEquals($previousBalance, $newBalance);

    //update leave request date and request a change in balance to the new balance
    $params['id'] = $leaveRequest->id;
    $params['change_balance'] = 1;
    $params['to_date'] = CRM_Utils_Date::processDate('2016-02-15');

    //The expected balance change after changing the dates.
    $result = LeaveRequest::calculateBalanceChange($this->leaveContact,
      new DateTime($params['from_date']),
      $params['from_date_type'],
      new DateTime($params['to_date']),
      $params['to_date_type']
    );
    $balanceAfterDateChange = $result['amount'];

    $leaveRequest = $this->getLeaveRequestServiceWhenCurrentUserIsAdmin()->create(
      $params,
      false
    );

    //The leave request balance has been updated to pick from the current work pattern
    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    $this->assertEquals($balanceAfterDateChange, $balance);
  }

  public function testBalanceChangeIsUpdatedForAnExistingLeaveRequestWhenChangeBalanceParameterIsFalseAndDatesChanged() {
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => '2016-01-01']
    );

    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);

    //Leave dates on Monday to Friday, all working days
    $leaveDates = [
      'from_date' => CRM_Utils_Date::processDate('2016-02-08'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-12')
    ];

    $params = $this->getDefaultParams($leaveDates);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params, true);

    //Just to make sure that we have the expected balance change for the leave request
    $previousBalance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    $this->assertEquals(-5, $previousBalance);

    //Add a work pattern for the contact with effective date before the leave dates
    $workPattern1 = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $this->leaveContact,
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-02-01')
    ]);

    //If the leave request is to be updated with new balance change, the balance would have changed.
    $result = LeaveRequest::calculateBalanceChange($this->leaveContact,
      new DateTime($params['from_date']),
      $params['from_date_type'],
      new DateTime($params['to_date']),
      $params['to_date_type']
    );

    $newBalance = $result['amount'];

    //The balance for leave request has changed since the contact work pattern has
    //been changed for the date range that the leave was initially requested.
    $this->assertNotEquals($previousBalance, $newBalance);

    //update leave request date and request the old balance to be retained
    $params['id'] = $leaveRequest->id;
    $params['change_balance'] = 0;
    $params['to_date'] = CRM_Utils_Date::processDate('2016-02-15');

    //The expected balance change after changing the dates.
    $result = LeaveRequest::calculateBalanceChange($this->leaveContact,
      new DateTime($params['from_date']),
      $params['from_date_type'],
      new DateTime($params['to_date']),
      $params['to_date_type']
    );
    $balanceAfterDateChange = $result['amount'];

    $leaveRequest = $this->getLeaveRequestServiceWhenCurrentUserIsAdmin()->create(
      $params,
      false
    );

    //The leave request balance has been updated to pick from the current work pattern
    //even though the old balance was asked to be retained.
    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    $this->assertEquals($balanceAfterDateChange, $balance);
  }

  public function testGetBreakdownIncludeOnlyTheLeaveBalanceChangesOfTheLeaveRequestDates() {
    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-02'),
    ], true);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-03'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-03'),
    ], true);

    $expectedBreakdown = $this->getExpectedBreakdownForLeaveRequest($leaveRequest1);
    $breakdown = $this->getLeaveRequestService()->getBreakdown($leaveRequest1->id);
    $this->assertEquals($expectedBreakdown, $breakdown);

    $expectedBreakdown = $this->getExpectedBreakdownForLeaveRequest($leaveRequest2);
    $breakdown = $this->getLeaveRequestService()->getBreakdown($leaveRequest2->id);
    $this->assertEquals($expectedBreakdown, $breakdown);
  }

  private function getExpectedBreakdownForLeaveRequest(LeaveRequest $leaveRequest) {
    $leaveRequestDayTypes = LeaveRequest::buildOptions('from_date_type');

    $dates = $leaveRequest->getDates();
    $expectedBreakdown = [];
    foreach($dates as $date) {
      $expectedBreakdown[] = [
        'id' => $date->id,
        'date' => date('Y-m-d', strtotime($date->date)),
        'type' => $date->type,
        'label' => $leaveRequestDayTypes[$date->type],
        'amount' => -1
      ];
    }

    return $expectedBreakdown;
  }
}
