<?php

use CRM_HRLeaveAndAbsences_Service_EntitlementCalculation as EntitlementCalculation;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlementTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlementTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_ContractHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeavePeriodEntitlementHelpersTrait;

  private $leaveRequestStatuses = [];

  public function setUp() {
    $this->setGlobalUser();

    $this->leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    // In order to make tests simpler, we disable the foreign key checks,
    // as a way to allow the creation of leave request records related
    // to a non-existing leave period entitlement
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

    $this->createContract();
  }

  public function tearDown() {
    $this->unsetGlobalUser();
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");
  }

  /**
   * @expectedException PEAR_Exception
   * @expectedExceptionMessage DB Error: already exists
   */
  public function testThereCannotBeMoreThanOneEntitlementForTheSameSetOfAbsenceTypeAbsencePeriodAndContact() {
    LeavePeriodEntitlement::create([
      'period_id' => 1,
      'type_id' => 1,
      'contact_id' => 1
    ]);

    LeavePeriodEntitlement::create([
      'period_id' => 1,
      'type_id' => 1,
      'contact_id' => 1
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementException
   * @expectedExceptionMessage The author of the comment cannot be null
   */
  public function testCommentsShouldHaveAuthor() {
    LeavePeriodEntitlement::create([
      'comment' => 'Lorem ipsum dolor sit....',
      'comment_updated_at' => date('YmdHis')
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementException
   * @expectedExceptionMessage The date of the comment cannot be null
   */
  public function testCommentsShouldHaveDate() {
    LeavePeriodEntitlement::create([
      'comment' => 'Lorem ipsum dolor sit....',
      'comment_author_id' => 2
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementException
   * @expectedExceptionMessage The date of the comment should be null if the comment is empty
   */
  public function testEmptyCommentsShouldNotHaveDate() {
    LeavePeriodEntitlement::create([
      'comment_date' => date('YmdHis')
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementException
   * @expectedExceptionMessage The author of the comment should be null if the comment is empty
   */
  public function testEmptyCommentsShouldNotHaveAuthor() {
    LeavePeriodEntitlement::create([
      'comment_author_id' => 2
    ]);
  }

  public function testBalanceShouldNotIncludeOpenLeaveRequests() {
    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('-10 days')]
    );

    $this->createLeaveBalanceChange($periodEntitlement->id, 5);
    $this->assertEquals(5, $periodEntitlement->getBalance());

    // This leave request will deduct 3 days from the entitlement
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['approved'],
      date('YmdHis'),
      date('YmdHis', strtotime('+2 day'))
    );

    // This would deduct 2 days, but it's Awaiting approval, so
    // it shouldn't be included on the balance
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['awaiting_approval'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    // This would deduct 1 day, but it's waiting for more information, so
    // it shouldn't be included on the balance
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['more_information_required'],
      date('YmdHis')
    );

    $this->assertEquals(2, $periodEntitlement->getBalance());
  }

  public function testBalanceShouldNotIncludeCancelledAndRejectedLeaveRequests() {
    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('-10 days')]
    );

    $this->createLeaveBalanceChange($periodEntitlement->id, 6);
    $this->assertEquals(6, $periodEntitlement->getBalance());

    // This leave request will deduct 3 days from the entitlement
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['approved'],
      date('YmdHis'),
      date('YmdHis', strtotime('+2 day'))
    );

    // This would deduct 2 days, but it's rejected, so
    // it shouldn't be included on the balance
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['rejected'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    // This would deduct 2 days, but it's cancelled, so
    // it shouldn't be included on the balance
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['cancelled'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    $this->assertEquals(3, $periodEntitlement->getBalance());
  }

  public function testBalanceShouldOnlyIncludeApprovedLeaveRequests() {
    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('-10 days')]
    );

    $this->createLeaveBalanceChange($periodEntitlement->id, 5);
    $this->assertEquals(5, $periodEntitlement->getBalance());

    // This leave request will deduct 2 days from the entitlement
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['approved'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    // This will deduct 1 day
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['admin_approved'],
      date('YmdHis')
    );

    // This will deduct 1 more day
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['approved'],
      date('YmdHis')
    );

    // This would deduct 2 days, but it's cancelled, so
    // it shouldn't be included on the balance
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['cancelled'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    $this->assertEquals(1, $periodEntitlement->getBalance());
  }

  public function testBalanceShouldIncludeBroughtForwardPublicHolidayAndLeave() {
    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();

    $this->createLeaveBalanceChange($periodEntitlement->id, 6);
    $this->createBroughtForwardBalanceChange($periodEntitlement->id, 3);
    $this->createPublicHolidayBalanceChange($periodEntitlement->id, 8);
    $this->assertEquals(17, $periodEntitlement->getBalance());
  }

  public function testBalanceShouldIncludeExpiredBalanceChanges() {
    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();

    $this->createExpiredBroughtForwardBalanceChange($periodEntitlement->id, 3, 0.5);
    // Note that this is only testing if the expired amount will be summed in
    // the total balance. In a real scenario, the balance would be 0, since
    // we would have taken the non-expired days as leave
    $this->assertEquals(2.5, $periodEntitlement->getBalance());
  }

  public function testGetContactEntitlementForPeriod() {
    LeavePeriodEntitlement::create([
      'period_id' => 1,
      'type_id' => 1,
      'contact_id' => 1,
    ]);

    LeavePeriodEntitlement::create([
      'period_id' => 2,
      'type_id' => 1,
      'contact_id' => 1
    ]);

    $periodEntitlement1 = LeavePeriodEntitlement::getPeriodEntitlementForContact(1, 1, 1);

    $this->assertEquals(1, $periodEntitlement1->period_id);
    $this->assertEquals(1, $periodEntitlement1->contact_id);
    $this->assertEquals(1, $periodEntitlement1->type_id);

    $periodEntitlement2 = LeavePeriodEntitlement::getPeriodEntitlementForContact(1, 2, 1);

    $this->assertEquals(2, $periodEntitlement2->period_id);
    $this->assertEquals(1, $periodEntitlement2->contact_id);
    $this->assertEquals(1, $periodEntitlement2->type_id);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage You must inform the Contact ID
   */
  public function testContactIdIsRequiredForGetContactEntitlementForPeriod() {
    LeavePeriodEntitlement::getPeriodEntitlementForContact(null, 10, 11);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage You must inform the AbsencePeriod ID
   */
  public function testAbsencePeriodIdIsRequiredForGetContractEntitlementForPeriod() {
    LeavePeriodEntitlement::getPeriodEntitlementForContact(10, null, 11);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage You must inform the AbsenceType ID
   */
  public function testAbsenceTypeIdIsRequiredForGetContractEntitlementForPeriod() {
    LeavePeriodEntitlement::getPeriodEntitlementForContact(10, 15, NULL);
  }

  public function testGetEntitlementShouldIncludeOnlyPositiveLeaveBroughtForwardAndPublicHolidays() {
    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 6);
    $this->createBroughtForwardBalanceChange($periodEntitlement->id, 3);
    $this->createPublicHolidayBalanceChange($periodEntitlement->id, 8);

    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+2 days'))
    );

    $this->assertEquals(17, $periodEntitlement->getEntitlement());
  }

  public function testTheLeaveRequestBalanceShouldOnlyIncludeDaysDeductedByApprovedLeaveRequests() {
    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests(
      new DateTime(),
      new DateTime('+8 days')
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    // None of these will be included in the Leave Request balance
    $this->createLeaveBalanceChange($periodEntitlement->id, 6);
    $this->createBroughtForwardBalanceChange($periodEntitlement->id, 3);
    $this->createPublicHolidayBalanceChange($periodEntitlement->id, 8);

    // 3 days Leave Request
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+2 days'))
    );

    $this->assertEquals(-3, $periodEntitlement->getLeaveRequestBalance());

    // 6 day Leave Request
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['approved'],
      date('Y-m-d', strtotime('+3 days')),
      date('Y-m-d', strtotime('+8 days'))
    );

    $this->assertEquals(-9, $periodEntitlement->getLeaveRequestBalance());
  }

  public function testCanSaveALeavePeriodEntitlementFromAnEntitlementCalculation() {

    $type = $this->createAbsenceType();
    $period = $this->createAbsencePeriod('2016-01-01', '2016-12-31');
    $this->setContractDates('2016-01-01', '2016-12-31');

    $periodEntitlement = LeavePeriodEntitlement::getPeriodEntitlementForContact(
      $this->contract['contact_id'],
      $period->id,
      $type->id
    );
    $this->assertNull($periodEntitlement);

    $broughtForward = 1;
    $proRata = 10;
    $publicHolidays = [ '2016-01-01', '2016-03-15', '2016-09-09' ];
    $calculation = $this->getEntitlementCalculationMock(
      $period,
      ['id' => $this->contract['contact_id']],
      $type,
      $broughtForward,
      $proRata,
      $publicHolidays
    );

    LeavePeriodEntitlement::saveFromCalculation($calculation);

    $periodEntitlement = LeavePeriodEntitlement::getPeriodEntitlementForContact(
      $this->contract['contact_id'],
      $period->id,
      $type->id
    );

    $this->assertNotNull($periodEntitlement);
    $this->assertEquals($period->id, $periodEntitlement->period_id);
    $this->assertEquals($type->id, $periodEntitlement->type_id);
    $this->assertEquals($this->contract['contact_id'], $periodEntitlement->contact_id);

    // 10 + 1 + 3 (Pro Rata + Brought Forward + No. Public Holidays)
    $this->assertEquals(14, $periodEntitlement->getEntitlement());

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id', 'validate'));

    $breakDownBalanceChanges = LeaveBalanceChange::getBreakdownBalanceChangesForEntitlement($periodEntitlement->id);

    // Checks if only a single balance change of "Leave" type was created
    // and that its amount is equal to the Pro Rata
    $leaveBalanceChanges = array_filter($breakDownBalanceChanges, function($balanceChange) use ($balanceChangeTypes) {
      return $balanceChange->type_id == $balanceChangeTypes['leave'];
    });
    $this->assertCount(1, $leaveBalanceChanges);
    $this->assertEquals($proRata, reset($leaveBalanceChanges)->amount);

    // Checks if only a single balance change of "Brought Forward" type was created
    // and that its amount is equal to the number of days brought forward
    $leaveBalanceChanges = array_filter($breakDownBalanceChanges, function($balanceChange) use ($balanceChangeTypes) {
      return $balanceChange->type_id == $balanceChangeTypes['brought_forward'];
    });
    $this->assertCount(1, $leaveBalanceChanges);
    $this->assertEquals($broughtForward, reset($leaveBalanceChanges)->amount);

    // Checks if only a single balance change of "Public Holiday" type was created
    // and that its amount is equal to the number of public holidays added to the
    // entitlement
    $leaveBalanceChanges = array_filter($breakDownBalanceChanges, function($balanceChange) use ($balanceChangeTypes) {
      return $balanceChange->type_id == $balanceChangeTypes['public_holiday'];
    });
    $this->assertCount(1, $leaveBalanceChanges);
    $this->assertEquals(count($publicHolidays), reset($leaveBalanceChanges)->amount);
  }

  public function testSaveFromCalculationWillReplaceExistingLeavePeriodEntitlement() {
    $type = $this->createAbsenceType();
    $period = $this->createAbsencePeriod('2016-01-01', '2016-12-31');
    $this->setContractDates('2016-01-01', '2016-12-31');

    $periodEntitlement1 = LeavePeriodEntitlement::create([
      'contact_id' => $this->contract['contact_id'],
      'period_id' => $period->id,
      'type_id' => $type->id
    ]);
    $this->assertNotEmpty($periodEntitlement1->id);

    $broughtForward = 1;
    $proRata = 10;
    $calculation = $this->getEntitlementCalculationMock(
      $period,
      ['id' => $this->contract['contact_id']],
      $type,
      $broughtForward,
      $proRata
    );

    LeavePeriodEntitlement::saveFromCalculation($calculation);

    $periodEntitlement2 = LeavePeriodEntitlement::getPeriodEntitlementForContact(
      $this->contract['contact_id'],
      $period->id,
      $type->id
    );

    $this->assertNotEquals($periodEntitlement1->id, $periodEntitlement2->id);
  }

  public function testSaveFromEntitlementCalculationCanSaveOverriddenValuesGreaterThanProposedEntitlement() {
    // This mocks the logged in user so we can test
    // the LeavePeriodEntitlement creation with a comment
    global $user;
    $user = new stdClass();
    $user->uid = 1;

    $type = new AbsenceType();
    $type->id = 1;
    $period = new AbsencePeriod();
    $period->id = 1;
    $contact = ['id' => 1];

    $broughtForward = 1;
    $proRata = 10;
    $overridden = true;
    $calculation = $this->getEntitlementCalculationMock(
      $period,
      $contact,
      $type,
      $broughtForward,
      $proRata,
      [],
      $overridden
    );

    $overriddenEntitlement = 50;
    $comment = 'Lorem ipsum dolor sit amet...';
    LeavePeriodEntitlement::saveFromCalculation($calculation, $overriddenEntitlement, $comment);

    $periodEntitlement = LeavePeriodEntitlement::getPeriodEntitlementForContact(
      $contact['id'],
      $period->id,
      $type->id
    );

    $this->assertNotNull($periodEntitlement);
    $this->assertEquals($period->id, $periodEntitlement->period_id);
    $this->assertEquals($type->id, $periodEntitlement->type_id);
    $this->assertEquals($contact['id'], $periodEntitlement->contact_id);
    $this->assertEquals(1, $periodEntitlement->overridden);
    $this->assertEquals($overriddenEntitlement, $periodEntitlement->getEntitlement());
  }

  public function testSaveFromEntitlementCalculationCanSaveOverriddenValuesLessThanTheProposedEntitlement() {
    // This mocks the logged in user so we can test
    // the LeavePeriodEntitlement creation with a comment
    global $user;
    $user = new stdClass();
    $user->uid = 1;

    $type = new AbsenceType();
    $type->id = 1;
    $period = new AbsencePeriod();
    $period->id = 1;
    $contact = ['id' => 1];

    $broughtForward = 1;
    $proRata = 10;
    $overridden = true;
    $calculation = $this->getEntitlementCalculationMock(
      $period,
      $contact,
      $type,
      $broughtForward,
      $proRata,
      [],
      $overridden
    );

    $overriddenEntitlement = 5;
    $comment = 'Lorem ipsum dolor sit amet...';
    LeavePeriodEntitlement::saveFromCalculation($calculation, $overriddenEntitlement, $comment);

    $periodEntitlement = LeavePeriodEntitlement::getPeriodEntitlementForContact(
      $contact['id'],
      $period->id,
      $type->id
    );

    $this->assertNotNull($periodEntitlement);
    $this->assertEquals($period->id, $periodEntitlement->period_id);
    $this->assertEquals($type->id, $periodEntitlement->type_id);
    $this->assertEquals($contact['id'], $periodEntitlement->contact_id);
    $this->assertEquals(1, $periodEntitlement->overridden);
    $this->assertEquals($overriddenEntitlement, $periodEntitlement->getEntitlement());

    $user = null;
  }

  public function testGetStartAndEndDatesShouldReturnAbsencePeriodDateIfContractStartDateIsLessThanThePeriodStartDate() {
    $this->setContractDates('2015-12-31', null);
    $absencePeriod = $this->createAbsencePeriod('2016-01-01', '2016-12-31');
    $absenceType = $this->createAbsenceType();

    $periodEntitlement = LeavePeriodEntitlement::create([
      'contact_id' => $this->contract['contact_id'],
      'type_id'     => $absenceType->id,
      'period_id'   => $absencePeriod->id
    ]);

    $dates = $periodEntitlement->getStartAndEndDates();
    $this->assertEquals('2016-01-01', $dates[0]['start_date']);
    $this->assertEquals('2016-12-31', $dates[0]['end_date']);
  }

  public function testGetStartAndEndDatesShouldReturnContractDateIfContractStartDateIsGreaterThanThePeriodStartDate() {
    $this->setContractDates('2016-03-17', null);
    $absencePeriod = $this->createAbsencePeriod('2016-01-01', '2016-12-31');
    $absenceType = $this->createAbsenceType();

    $periodEntitlement = LeavePeriodEntitlement::create([
      'contact_id' => $this->contract['contact_id'],
      'type_id'     => $absenceType->id,
      'period_id'   => $absencePeriod->id
    ]);

    $dates = $periodEntitlement->getStartAndEndDates();
    $this->assertEquals('2016-03-17', $dates[0]['start_date']);
    $this->assertEquals('2016-12-31', $dates[0]['end_date']);
  }

  public function testGetStartAndEndDatesShouldReturnAbsencePeriodDateIfContractEndDateIsGreaterThanThePeriodEndDate() {
    $this->setContractDates('2015-03-17', '2017-01-01');
    $absencePeriod = $this->createAbsencePeriod('2016-01-01', '2016-12-31');
    $absenceType = $this->createAbsenceType();

    $periodEntitlement = LeavePeriodEntitlement::create([
      'contact_id' => $this->contract['contact_id'],
      'type_id'     => $absenceType->id,
      'period_id'   => $absencePeriod->id
    ]);

    $dates = $periodEntitlement->getStartAndEndDates();
    $this->assertEquals('2016-01-01', $dates[0]['start_date']);
    $this->assertEquals('2016-12-31', $dates[0]['end_date']);
  }

  public function testGetStartAndEndDatesShouldReturnContractDateIfContractEndDateIsLessThanThePeriodEndDate() {
    $this->setContractDates('2016-03-17', '2016-05-23');
    $absencePeriod = $this->createAbsencePeriod('2016-01-01', '2016-12-31');
    $absenceType = $this->createAbsenceType();

    $periodEntitlement = LeavePeriodEntitlement::create([
      'contact_id' => $this->contract['contact_id'],
      'type_id'     => $absenceType->id,
      'period_id'   => $absencePeriod->id
    ]);

    $dates = $periodEntitlement->getStartAndEndDates();
    $this->assertEquals('2016-03-17', $dates[0]['start_date']);
    $this->assertEquals('2016-05-23', $dates[0]['end_date']);
  }

  private function createAbsencePeriod($startDate, $endDate) {
    return AbsencePeriod::create([
      'title' => microtime(),
      'start_date' => date('YmdHis', strtotime($startDate)),
      'end_date' => date('YmdHis', strtotime($endDate)),
    ]);
  }

  private function createAbsenceType() {
    return AbsenceType::create([
      'title' => 'Type ' . microtime(),
      'color' => '#000000',
      'default_entitlement' => 20,
      'allow_request_cancelation' => 1,
    ]);
  }

  /**
   * Mock the calculation, as we only need to test
   * if the LeavePeriodEntitlement BAO can create an new LeavePeriodEntitlement
   * from a EntitlementCalculation instance
   *
   * @param $period
   * @param $contact
   * @param $type
   * @param int $broughtForward
   * @param int $proRata
   * @param array $publicHolidays
   * @param bool $overridden
   *
   * @return mixed The EntitlementCalculation mock
   * The EntitlementCalculation mock
   */
  private function getEntitlementCalculationMock(
    $period,
    $contact,
    $type,
    $broughtForward = 0,
    $proRata = 0,
    $publicHolidays = [],
    $overridden = false
  ) {
    $calculation = $this->getMockBuilder(EntitlementCalculation::class)
                        ->setConstructorArgs([$period, $contact, $type])
                        ->setMethods([
                          'getBroughtForward',
                          'getProRata',
                          'getBroughtForwardExpirationDate',
                          'getPublicHolidaysInEntitlement',
                          'getProposedEntitlement'
                        ])
                        ->getMock();

    $calculation->expects($this->once())
                ->method('getBroughtForward')
                ->will($this->returnValue($broughtForward));

    $calculation->expects($this->once())
                ->method('getProRata')
                ->will($this->returnValue($proRata));

    $calculation->expects($this->once())
                ->method('getBroughtForwardExpirationDate')
                ->will($this->returnValue('2016-01-01'));

    $proposedEntitlement = $proRata + $broughtForward;
    $calculation->expects($overridden ? $this->once() : $this->never())
                ->method('getProposedEntitlement')
                ->will($this->returnValue($proposedEntitlement));

    $publicHolidaysReturn = [];
    foreach($publicHolidays as $publicHoliday) {
      $instance = new PublicHoliday();
      $instance->date = $publicHoliday;
      $publicHolidaysReturn[] = $instance;
    }

    $calculation->expects($this->once())
                ->method('getPublicHolidaysInEntitlement')
                ->will($this->returnValue($publicHolidaysReturn));

    return $calculation;
  }

  public function testFutureBalanceShouldIncludeOpenAndApprovedLeaveRequests() {
    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => CRM_Utils_Date::processDate('-10 days')]
    );

    $this->createLeaveBalanceChange($periodEntitlement->id, 10);

    // This leave request will deduct 3 days from the entitlement
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['approved'],
      date('YmdHis'),
      date('YmdHis', strtotime('+2 day'))
    );

    // This will deduct 2 days
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['awaiting_approval'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    // This will deduct 1 day
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['more_information_required'],
      date('YmdHis')
    );

    $this->assertEquals(4, $periodEntitlement->getFutureBalance());
  }

  public function testGetPeriodEntitlementsForContact() {
    $contactId = 1;
    $periodId = 1;
    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $periodId
    ]);
    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $periodId,
      'type_id' => 2
    ]);


    $entitlements = LeavePeriodEntitlement::getPeriodEntitlementsForContact($contactId, $periodId);
    $this->assertCount(2, $entitlements);
    $this->assertInstanceOf(LeavePeriodEntitlement::class, $entitlements[0]);
    $this->assertInstanceOf(LeavePeriodEntitlement::class, $entitlements[1]);
    $this->assertEquals($periodEntitlement1->id, $entitlements[0]->id);
    $this->assertEquals($periodEntitlement2->id, $entitlements[1]->id);
  }

  public function testGetPeriodEntitlementsForContactWithTypeID() {
    $contactId = 1;
    $periodId = 1;
    $typeID = 2;
    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $periodId
    ]);
    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $periodId,
      'type_id' => $typeID
    ]);

    $entitlements = LeavePeriodEntitlement::getPeriodEntitlementsForContact($contactId, $periodId, $typeID);
    $this->assertCount(1, $entitlements);
    $this->assertInstanceOf(LeavePeriodEntitlement::class, $entitlements[0]);
    $this->assertEquals($periodEntitlement2->id, $entitlements[0]->id);
  }

  public function testGetPeriodEntitlementsForContactWhenWrongContactIsPassed() {
    $contactId = 1;
    $periodId = 1;
    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $periodId
    ]);
    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $periodId,
      'type_id' => 2
    ]);
    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 2,
      'period_id' => $periodId,
      'type_id' => 2
    ]);

    $entitlements = LeavePeriodEntitlement::getPeriodEntitlementsForContact($contactId, $periodId);
    $this->assertCount(2, $entitlements);
    $this->assertInstanceOf(LeavePeriodEntitlement::class, $entitlements[0]);
    $this->assertInstanceOf(LeavePeriodEntitlement::class, $entitlements[1]);
    $this->assertEquals($periodEntitlement1->id, $entitlements[0]->id);
    $this->assertEquals($periodEntitlement2->id, $entitlements[1]->id);
  }

  public function testGetLeavePeriodEntitlementRemainder() {
    $absencePeriod = AbsencePeriodFabricator::fabricate();
    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate(['period_id' => $absencePeriod->id]);
    $this->createLeaveBalanceChange($periodEntitlement->id, 10);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => $absencePeriod->start_date]
    );

    // This leave request will deduct 2 days from the entitlement
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['approved'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    $params = ['entitlement_id' => $periodEntitlement->id];
    $result = LeavePeriodEntitlement::getRemainder($params);
    $this->assertEquals(1, count($result));
    $this->assertEquals(8, $result[0]['remainder']['current']);
    $this->assertArrayNotHasKey('future', $result[0]['remainder']);
  }

  public function testGetLeavePeriodEntitlementRemainderWithMultipleRecords() {
    $absencePeriod = AbsencePeriodFabricator::fabricate();
    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate(['period_id' => $absencePeriod->id]);

    //create two more LeavePeriodEntitlement within same period with same contactid
    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate(['period_id' => $absencePeriod->id, 'type_id' => 2]);
    $periodEntitlement3 = LeavePeriodEntitlementFabricator::fabricate(['period_id' => $absencePeriod->id, 'type_id' => 3]);

    $this->createLeaveBalanceChange($periodEntitlement1->id, 10);
    $this->createLeaveBalanceChange($periodEntitlement2->id, 4);
    $this->createLeaveBalanceChange($periodEntitlement3->id, 3);

    $params = ['contact_id' => $periodEntitlement1->contact_id, 'period_id' => $absencePeriod->id];
    $result = LeavePeriodEntitlement::getRemainder($params);
    $this->assertCount(3, $result);
    $this->assertEquals(10, $result[0]['remainder']['current']);
    $this->assertEquals(4, $result[1]['remainder']['current']);
    $this->assertEquals(3, $result[2]['remainder']['current']);
  }

  public function testGetLeavePeriodEntitlementRemainderWithIncludeFuture() {
    $absencePeriod = AbsencePeriodFabricator::fabricate();
    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate(['period_id' => $absencePeriod->id]);
    $this->createLeaveBalanceChange($periodEntitlement->id, 10);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $periodEntitlement->contact_id],
      ['period_start_date' => $absencePeriod->start_date]
    );

    // This leave request will deduct 2 days from the entitlement
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->type_id,
      $periodEntitlement->contact_id,
      $this->leaveRequestStatuses['awaiting_approval'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    $params = ['entitlement_id' => $periodEntitlement->id, 'include_future' => true];
    $result = LeavePeriodEntitlement::getRemainder($params);
    $this->assertCount(1, $result);
    $this->assertEquals(10, $result[0]['remainder']['current']);
    $this->assertEquals(8, $result[0]['remainder']['future']);
  }

  public function testGetLeavePeriodEntitlementRemainderWithContactAndPeriodId() {
    $absencePeriod = AbsencePeriodFabricator::fabricate();
    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate(['period_id' => $absencePeriod->id]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 10);

    $params = ['contact_id' => $periodEntitlement->contact_id, 'period_id' => $periodEntitlement->period_id];
    $result = LeavePeriodEntitlement::getRemainder($params);
    $this->assertCount(1, $result);
    $this->assertEquals(10, $result[0]['remainder']['current']);
  }

  public function testGetBreakdownBalanceChangesShouldIncludeOnlyNonExpiredBalancesWhenFalseIsPassed() {
    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate();
    $this->createLeaveBalanceChange($periodEntitlement->id, 10);
    $this->createExpiredBroughtForwardBalanceChange($periodEntitlement->id, 9, 5);
    $this->createExpiredBroughtForwardBalanceChange($periodEntitlement->id, 8, 3);
    $breakdowns = $periodEntitlement->getBreakdownBalanceChanges(false);

    //there should only be three leave balance changes for this period entitlement that are not expired
    $this->assertCount(3, $breakdowns);

    //validate that the content of first breakdown array is same as what is expected
    $this->assertInstanceOf(LeaveBalanceChange::class, $breakdowns[0]);
    $this->assertEquals($periodEntitlement->id, $breakdowns[0]->source_id);
    $this->assertEquals(10, $breakdowns[0]->amount);
    $this->assertEquals('entitlement', $breakdowns[0]->source_type);

    //validate that the content of second breakdown array is same as what is expected
    $this->assertInstanceOf(LeaveBalanceChange::class, $breakdowns[1]);
    $this->assertEquals($periodEntitlement->id, $breakdowns[1]->source_id);
    $this->assertEquals(9, $breakdowns[1]->amount);
    $this->assertEquals('entitlement', $breakdowns[1]->source_type);

    //validate that the content of third breakdown array is same as what is expected
    $this->assertInstanceOf(LeaveBalanceChange::class, $breakdowns[2]);
    $this->assertEquals($periodEntitlement->id, $breakdowns[2]->source_id);
    $this->assertEquals(8, $breakdowns[2]->amount);
    $this->assertEquals('entitlement', $breakdowns[2]->source_type);
  }

  public function testGetBreakdownBalanceChangesShouldIncludeOnlyExpiredBalancesWhenTrueIsPassed() {
    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate();
    $this->createLeaveBalanceChange($periodEntitlement->id, 10);
    $this->createExpiredBroughtForwardBalanceChange($periodEntitlement->id, 9, 5);
    $this->createExpiredBroughtForwardBalanceChange($periodEntitlement->id, 8, 3);
    $breakdowns = $periodEntitlement->getBreakdownBalanceChanges(true);

    //there should only be two leave balance changes for this period entitlement that are expired
    $this->assertCount(2, $breakdowns);


    $this->assertInstanceOf(LeaveBalanceChange::class, $breakdowns[0]);
    $this->assertEquals($periodEntitlement->id, $breakdowns[0]->source_id);
    $this->assertEquals(-5, $breakdowns[0]->amount);
    $this->assertEquals('entitlement', $breakdowns[0]->source_type);

    $this->assertInstanceOf(LeaveBalanceChange::class, $breakdowns[1]);
    $this->assertEquals($periodEntitlement->id, $breakdowns[1]->source_id);
    $this->assertEquals(-3, $breakdowns[1]->amount);
    $this->assertEquals('entitlement', $breakdowns[1]->source_type);
  }

  public function testGetBreakdown() {
    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate();

    $this->createExpiredBroughtForwardBalanceChange($periodEntitlement->id, 8, 3, null);
    $this->createLeaveBalanceChange($periodEntitlement->id, 10);

    $result = LeavePeriodEntitlement::getBreakdown([
      'entitlement_id' => $periodEntitlement->id
    ]);

    $expectedResult = [
      [
        'id' => $periodEntitlement->id,
        'breakdown' => [
          [
            'amount' => '8.00',
            'expiry_date' => null,
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('brought_forward'),
              'value' => 'brought_forward',
              'label' => 'Brought Forward'
            ]
          ],
          [
            'amount' => '10.00',
            'expiry_date' => null,
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('leave'),
              'value' => 'leave',
              'label' => 'Leave'
            ]
          ]
        ]
      ]
    ];

    $this->assertEquals($expectedResult, $result);
  }

  public function testGetBreakdownWithExpiredSetToTrue() {
    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate();

    $expiredByNoOfDays = 2;
    $this->createExpiredBroughtForwardBalanceChange($periodEntitlement->id, 8, 3, $expiredByNoOfDays);
    $this->createLeaveBalanceChange($periodEntitlement->id, 10);

    $result = LeavePeriodEntitlement::getBreakdown([
      'entitlement_id' => $periodEntitlement->id,
      'expired'        => true
    ]);

    $expectedResult = [
      [
        'id' => $periodEntitlement->id,
        'breakdown' => [
          [
            'amount' => '-3.00', //only the expired amount will be returned
            'expiry_date' => date('Y-m-d', strtotime("-{$expiredByNoOfDays} day")),
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('brought_forward'),
              'value' => 'brought_forward',
              'label' => 'Brought Forward'
            ]
          ],
        ],
      ],
    ];

    $this->assertEquals($expectedResult, $result);
  }

  public function testGetBreakdownWithContactAndPeriodId() {
    $contactId = 1;

    $absencePeriod = AbsencePeriodFabricator::fabricate();

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $absencePeriod->id
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $absencePeriod->id,
      'type_id' => 2
    ]);

    $this->createBroughtForwardBalanceChange($periodEntitlement1->id, 8);
    $this->createLeaveBalanceChange($periodEntitlement1->id, 10);

    $this->createBroughtForwardBalanceChange($periodEntitlement2->id, 5);
    $this->createLeaveBalanceChange($periodEntitlement2->id, 2);

    $result = LeavePeriodEntitlement::getBreakdown([
      'contact_id' => $contactId,
      'period_id'  => $absencePeriod->id
    ]);

    $expectedResult = [
      [
        'id' => $periodEntitlement1->id,
        'breakdown' => [
          [
            'amount' => '8',
            'expiry_date' => null,
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('brought_forward'),
              'value' => 'brought_forward',
              'label' => 'Brought Forward'
            ]
          ],
          [
            'amount' => '10.00',
            'expiry_date' => null,
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('leave'),
              'value' => 'leave',
              'label' => 'Leave'
            ]
          ]
        ],
      ],
      [
        'id' => $periodEntitlement2->id,
        'breakdown' => [
          [
            'amount' => '5',
            'expiry_date' => null,
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('brought_forward'),
              'value' => 'brought_forward',
              'label' => 'Brought Forward'
            ]
          ],
          [
            'amount' => '2.00',
            'expiry_date' => null,
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('leave'),
              'value' => 'leave',
              'label' => 'Leave'
            ]
          ]
        ],
      ]
    ];

    $this->assertEquals($expectedResult, $result);
  }

  public function testGetBreakdownWithContactAndPeriodIdAndExpiredSetToTrue() {
    $contactId = 1;

    $absencePeriod = AbsencePeriodFabricator::fabricate();

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $absencePeriod->id
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $absencePeriod->id,
      'type_id' => 2
    ]);

    $expiredByNoOfDays = 2;
    $this->createExpiredBroughtForwardBalanceChange($periodEntitlement1->id, 9, 5, $expiredByNoOfDays);
    $this->createLeaveBalanceChange($periodEntitlement1->id, 10);

    $this->createExpiredBroughtForwardBalanceChange($periodEntitlement2->id, 5, 3, $expiredByNoOfDays);
    $this->createLeaveBalanceChange($periodEntitlement2->id, 2);

    $expectedResult = [
      [
        'id' => "$periodEntitlement1->id",
        'breakdown' => [
          [
            'amount' => '-5',
            'expiry_date' => date('Y-m-d', strtotime("-{$expiredByNoOfDays} day")),
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('brought_forward'),
              'value' => 'brought_forward',
              'label' => 'Brought Forward'
            ]
          ],
        ],
      ],
      [
        'id' => "$periodEntitlement2->id",
        'breakdown' => [
          [
            'amount' => '-3',
            'expiry_date' => date('Y-m-d', strtotime("-{$expiredByNoOfDays} day")),
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('brought_forward'),
              'value' => 'brought_forward',
              'label' => 'Brought Forward'
            ]
          ],
        ],
      ]
    ];

    $result = LeavePeriodEntitlement::getBreakdown([
      'contact_id' => $contactId,
      'period_id'  => $absencePeriod->id,
      'expired'    => true
    ]);

    $this->assertEquals($expectedResult, $result);
  }

  public function testGetBreakdownWillGroupTogetherBalanceChangesOfTheSameType() {
    $contactId = 1;
    $absencePeriod = AbsencePeriodFabricator::fabricate();

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $absencePeriod->id
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $absencePeriod->id,
      'type_id' => 2
    ]);

    $this->createLeaveBalanceChange($periodEntitlement1->id, 5);
    $this->createLeaveBalanceChange($periodEntitlement1->id, 10);

    $this->createPublicHolidayBalanceChange($periodEntitlement2->id, 4);
    $this->createPublicHolidayBalanceChange($periodEntitlement2->id, 5);
    $this->createLeaveBalanceChange($periodEntitlement2->id, 13);

    $expectedResult = [
      [
        'id' => $periodEntitlement1->id,
        'breakdown' => [
          [
            'amount' => 15, // 10 + 5
            'expiry_date' => null,
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('leave'),
              'value' => 'leave',
              'label' => 'Leave'
            ]
          ]
        ],
      ],
      [
        'id' => $periodEntitlement2->id,
        'breakdown' => [
          [
            'amount' => 9, // 4 + 5
            'expiry_date' => null,
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('public_holiday'),
              'value' => 'public_holiday',
              'label' => 'Public Holiday'
            ]
          ],
          [
            'amount' => 13,
            'expiry_date' => null,
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('leave'),
              'value' => 'leave',
              'label' => 'Leave'
            ]
          ],
        ],
      ]
    ];

    $result = LeavePeriodEntitlement::getBreakdown([
      'contact_id' => $contactId,
      'period_id'  => $absencePeriod->id
    ]);

    $this->assertEquals($expectedResult, $result);
  }

  public function testGetBreakdownWillGroupTogetherOverriddenBalanceChangeWithLeave() {
    $contactId = 1;
    $absencePeriod = AbsencePeriodFabricator::fabricate();

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactId,
      'period_id' => $absencePeriod->id
    ]);

    $this->createOverriddenBalanceChange($periodEntitlement->id, 5);
    $this->createLeaveBalanceChange($periodEntitlement->id, 10);

    $expectedResult = [
      [
        'id' => "$periodEntitlement->id",
        'breakdown' => [
          [
            'amount' => 15, // 10 from leave + 5 from overridden
            'expiry_date' => null,
            'type' => [
              'id' => $this->getBalanceChangeTypeValue('leave'),
              'value' => 'leave',
              'label' => 'Leave'
            ]
          ]
        ],
      ]
    ];

    $result = LeavePeriodEntitlement::getBreakdown([
      'contact_id' => $contactId,
      'period_id'  => $absencePeriod->id
    ]);

    $this->assertEquals($expectedResult, $result);
  }

  public function testGetForLeaveRequestReturnsTheLeavePeriodEntitlementForAGivenLeaveRequest() {
    $absencePeriod1 = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('-1 day'),
    ]);

    $absencePeriod2 = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('today'),
      'end_date' => CRM_Utils_Date::processDate('+10 days'),
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod1->id,
      'type_id' =>  1
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod2->id,
      'type_id' =>  1
    ]);

    $periodEntitlement3 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => 1,
      'period_id' => $absencePeriod2->id,
      'type_id' =>  2
    ]);

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement1->contact_id,
      'type_id' => $periodEntitlement1->type_id,
      'from_date' => CRM_Utils_Date::processDate('-5 days'),
      'to_date' => CRM_Utils_Date::processDate('-3 days'),
    ]);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement2->contact_id,
      'type_id' => $periodEntitlement2->type_id,
      'from_date' => CRM_Utils_Date::processDate('+2 days'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
    ]);

    $leaveRequest3 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $periodEntitlement3->contact_id,
      'type_id' => $periodEntitlement3->type_id,
      'from_date' => CRM_Utils_Date::processDate('+9 days'),
      'to_date' => CRM_Utils_Date::processDate('+10 days'),
    ]);

    $this->assertEquals($periodEntitlement1->id, LeavePeriodEntitlement::getForLeaveRequest($leaveRequest1)->id);
    $this->assertEquals($periodEntitlement2->id, LeavePeriodEntitlement::getForLeaveRequest($leaveRequest2)->id);
    $this->assertEquals($periodEntitlement3->id, LeavePeriodEntitlement::getForLeaveRequest($leaveRequest3)->id);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage It was not possible to find an AbsencePeriod containing the given LeaveRequest
   */
  public function testGetForLeaveRequestShouldThrowAnExceptionIfThereIsNoAbsencePeriodContainingTheGivenLeaveRequestDates() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+9 days'),
      'to_date' => CRM_Utils_Date::processDate('+10 days'),
    ]);

    LeavePeriodEntitlement::getForLeaveRequest($leaveRequest);
  }

  /**
   * Some tests on this class use the HRJobDetails API which uses the
   * HRJobContractRevision API that depends on the the global $user.
   *
   * This API expects the global $user to be available, so we create it user
   * here, with a null uid, which is enough to run the test.
   */
  private function setGlobalUser() {
    global $user;

    $user = new stdClass();
    $user->uid = null;
  }

  /**
   * This basically resets what is done by the setGlobalUser() method
   */
  private function unsetGlobalUser() {
    global $user;

    $user = null;
  }

}
