<?php

require_once __DIR__."/../LeaveBalanceChangeHelpersTrait.php";

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRLeaveAndAbsences_EntitlementCalculation as EntitlementCalculation;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlementTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlementTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface, TransactionalInterface {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;

  private $leaveRequestStatuses = [];

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->install('org.civicrm.hrjobcontract')
      ->apply();
  }

  public function setUp() {
    $this->leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    // In order to make tests simpler, we disable the foreign key checks,
    // as a way to allow the creation of leave request records related
    // to a non-existing leave period entitlement
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");
  }

  /**
   * @expectedException PEAR_Exception
   * @expectedExceptionMessage DB Error: already exists
   */
  public function testThereCannotBeMoreThanOneEntitlementForTheSameSetOfAbsenceTypeAbsencePeriodAndContract() {
    LeavePeriodEntitlement::create([
      'period_id' => 1,
      'type_id' => 1,
      'contract_id' => 1
    ]);

    LeavePeriodEntitlement::create([
      'period_id' => 1,
      'type_id' => 1,
      'contract_id' => 1
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
    $periodEntitlement = $this->createPeriodEntitlement();

    $this->createLeaveBalanceChange($periodEntitlement->id, 5);
    $this->assertEquals(5, $periodEntitlement->getBalance());

    // This leave request will deduct 3 days from the entitlement
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['Approved'],
      date('YmdHis'),
      date('YmdHis', strtotime('+2 day'))
    );

    // This would deduct 2 days, but it's waiting approval, so
    // it shouldn't be included on the balance
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['Waiting Approval'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    // This would deduct 1 day, but it's waiting for more information, so
    // it shouldn't be included on the balance
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['More Information Requested'],
      date('YmdHis')
    );

    $this->assertEquals(2, $periodEntitlement->getBalance());
  }

  public function testBalanceShouldNotIncludeCancelledAndRejectedLeaveRequests() {
    $periodEntitlement = $this->createPeriodEntitlement();

    $this->createLeaveBalanceChange($periodEntitlement->id, 6);
    $this->assertEquals(6, $periodEntitlement->getBalance());

    // This leave request will deduct 3 days from the entitlement
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['Approved'],
      date('YmdHis'),
      date('YmdHis', strtotime('+2 day'))
    );

    // This would deduct 2 days, but it's rejected, so
    // it shouldn't be included on the balance
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['Rejected'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    // This would deduct 2 days, but it's cancelled, so
    // it shouldn't be included on the balance
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['Cancelled'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    $this->assertEquals(3, $periodEntitlement->getBalance());
  }

  public function testBalanceShouldOnlyIncludeApprovedLeaveRequests() {
    $periodEntitlement = $this->createPeriodEntitlement();

    $this->createLeaveBalanceChange($periodEntitlement->id, 5);
    $this->assertEquals(5, $periodEntitlement->getBalance());

    // This leave request will deduct 2 days from the entitlement
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['Approved'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    // This will deduct 1 day
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['Admin Approved'],
      date('YmdHis')
    );

    // This will deduct 1 more day
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['Approved'],
      date('YmdHis')
    );

    // This would deduct 2 days, but it's cancelled, so
    // it shouldn't be included on the balance
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['Cancelled'],
      date('YmdHis'),
      date('YmdHis', strtotime('+1 day'))
    );

    $this->assertEquals(1, $periodEntitlement->getBalance());
  }

  public function testBalanceShouldIncludeBroughtForwardPublicHolidayAndLeave() {
    $periodEntitlement = $this->createPeriodEntitlement();

    $this->createLeaveBalanceChange($periodEntitlement->id, 6);
    $this->createBroughtForwardBalanceChange($periodEntitlement->id, 3);
    $this->createPublicHolidayBalanceChange($periodEntitlement->id, 8);
    $this->assertEquals(17, $periodEntitlement->getBalance());
  }

  public function testBalanceShouldIncludeExpiredBalanceChanges() {
    $periodEntitlement = $this->createPeriodEntitlement();

    $this->createExpiredBroughtForwardBalanceChange($periodEntitlement->id, 3, 0.5);
    // Note that this is only testing if the expired amount will be summed in
    // the total balance. In a real scenario, the balance would be 0, since
    // we would have taken the non-expired days as leave
    $this->assertEquals(2.5, $periodEntitlement->getBalance());
  }

  public function testGetContractEntitlementForPeriod() {
    LeavePeriodEntitlement::create([
      'period_id' => 1,
      'type_id' => 1,
      'contract_id' => 1,
    ]);

    LeavePeriodEntitlement::create([
      'period_id' => 2,
      'type_id' => 1,
      'contract_id' => 1
    ]);

    $periodEntitlement1 = LeavePeriodEntitlement::getPeriodEntitlementForContract(1, 1, 1);

    $this->assertEquals(1, $periodEntitlement1->period_id);
    $this->assertEquals(1, $periodEntitlement1->contract_id);
    $this->assertEquals(1, $periodEntitlement1->type_id);

    $periodEntitlement2 = LeavePeriodEntitlement::getPeriodEntitlementForContract(1, 2, 1);

    $this->assertEquals(2, $periodEntitlement2->period_id);
    $this->assertEquals(1, $periodEntitlement2->contract_id);
    $this->assertEquals(1, $periodEntitlement2->type_id);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage You must inform the Contract ID
   */
  public function testContractIdIsRequiredForGetContractEntitlementForPeriod() {
    LeavePeriodEntitlement::getPeriodEntitlementForContract(null, 10, 11);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage You must inform the AbsencePeriod ID
   */
  public function testAbsencePeriodIdIsRequiredForGetContractEntitlementForPeriod() {
    LeavePeriodEntitlement::getPeriodEntitlementForContract(10, null, 11);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage You must inform the AbsenceType ID
   */
  public function testAbsenceTypeIdIsRequiredForGetContractEntitlementForPeriod() {
    LeavePeriodEntitlement::getPeriodEntitlementForContract(10, 15, NULL);
  }

  public function testGetEntitlementShouldIncludeOnlyPositiveLeaveBroughtForwardAndPublicHolidays() {
    $periodEntitlement = $this->createPeriodEntitlement();

    $this->createLeaveBalanceChange($periodEntitlement->id, 6);
    $this->createBroughtForwardBalanceChange($periodEntitlement->id, 3);
    $this->createPublicHolidayBalanceChange($periodEntitlement->id, 8);

    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+2 days'))
    );

    $this->assertEquals(17, $periodEntitlement->getEntitlement());
  }

  public function testTheLeaveRequestBalanceShouldOnlyIncludeDaysDeductedByApprovedLeaveRequests() {
    $periodEntitlement = $this->createPeriodEntitlement();

    // Neither of these won't be included in the Leave Request balance
    $this->createLeaveBalanceChange($periodEntitlement->id, 6);
    $this->createBroughtForwardBalanceChange($periodEntitlement->id, 3);
    $this->createPublicHolidayBalanceChange($periodEntitlement->id, 8);

    // 3 days Leave Request
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['Approved'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+2 days'))
    );

    $this->assertEquals(-3, $periodEntitlement->getLeaveRequestBalance());

    // 6 day Leave Request
    $this->createLeaveRequestBalanceChange(
      $periodEntitlement->id,
      $this->leaveRequestStatuses['Approved'],
      date('Y-m-d', strtotime('+3 days')),
      date('Y-m-d', strtotime('+8 days'))
    );

    $this->assertEquals(-9, $periodEntitlement->getLeaveRequestBalance());
  }

  public function testCanSaveALeavePeriodEntitlementFromAnEntitlementCalculation() {
    $type = new AbsenceType();
    $type->id = 1;
    $period = new AbsencePeriod();
    $period->id = 1;
    $contract = ['id' => 1];

    $periodEntitlement = LeavePeriodEntitlement::getPeriodEntitlementForContract(
      $contract['id'],
      $period->id,
      $type->id
    );
    $this->assertNull($periodEntitlement);

    $broughtForward = 1;
    $proRata = 10;
    $publicHolidays = [ '2016-01-01', '2016-03-15', '2016-09-09' ];
    $calculation = $this->getEntitlementCalculationMock(
      $period,
      $contract,
      $type,
      $broughtForward,
      $proRata,
      $publicHolidays
    );

    LeavePeriodEntitlement::saveFromCalculation($calculation);

    $periodEntitlement = LeavePeriodEntitlement::getPeriodEntitlementForContract(
      $contract['id'],
      $period->id,
      $type->id
    );

    $this->assertNotNull($periodEntitlement);
    $this->assertEquals($period->id, $periodEntitlement->period_id);
    $this->assertEquals($type->id, $periodEntitlement->type_id);
    $this->assertEquals($contract['id'], $periodEntitlement->contract_id);

    // 10 + 1 + 3 (Pro Rata + Brought Forward + No. Public Holidays)
    $this->assertEquals(14, $periodEntitlement->getEntitlement());

    //The 3 days deducted because of the Public Holidays
    $this->assertEquals(-3, $periodEntitlement->getLeaveRequestBalance());

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id'));

    $breakDownBalanceChanges = LeaveBalanceChange::getBreakdownBalanceChangesForEntitlement($periodEntitlement->id);

    // Checks if only a single balance change of "Leave" type was created
    // and that its amount is equal to the Pro Rata
    $leaveBalanceChanges = array_filter($breakDownBalanceChanges, function($balanceChange) use ($balanceChangeTypes) {
      return $balanceChange->type_id == $balanceChangeTypes['Leave'];
    });
    $this->assertCount(1, $leaveBalanceChanges);
    $this->assertEquals($proRata, reset($leaveBalanceChanges)->amount);

    // Checks if only a single balance change of "Brought Forward" type was created
    // and that its amount is equal to the number of days brought forward
    $leaveBalanceChanges = array_filter($breakDownBalanceChanges, function($balanceChange) use ($balanceChangeTypes) {
      return $balanceChange->type_id == $balanceChangeTypes['Brought Forward'];
    });
    $this->assertCount(1, $leaveBalanceChanges);
    $this->assertEquals($broughtForward, reset($leaveBalanceChanges)->amount);

    // Checks if only a single balance change of "Public Holiday" type was created
    // and that its amount is equal to the number of public holidays added to the
    // entitlement
    $leaveBalanceChanges = array_filter($breakDownBalanceChanges, function($balanceChange) use ($balanceChangeTypes) {
      return $balanceChange->type_id == $balanceChangeTypes['Public Holiday'];
    });
    $this->assertCount(1, $leaveBalanceChanges);
    $this->assertEquals(count($publicHolidays), reset($leaveBalanceChanges)->amount);
  }

  public function testSaveFromCalculationWillReplaceExistingLeavePeriodEntitlement() {
    $type = new AbsenceType();
    $type->id = 1;
    $period = new AbsencePeriod();
    $period->id = 1;
    $contract = ['id' => 1];

    $periodEntitlement1 = LeavePeriodEntitlement::create([
      'contract_id' => $contract['id'],
      'period_id' => $period->id,
      'type_id' => $type->id
    ]);
    $this->assertNotEmpty($periodEntitlement1->id);

    $broughtForward = 1;
    $proRata = 10;
    $calculation = $this->getEntitlementCalculationMock(
      $period,
      $contract,
      $type,
      $broughtForward,
      $proRata
    );

    LeavePeriodEntitlement::saveFromCalculation($calculation);

    $periodEntitlement2 = LeavePeriodEntitlement::getPeriodEntitlementForContract(
      $contract['id'],
      $period->id,
      $type->id
    );

    $this->assertNotEquals($periodEntitlement1->id, $periodEntitlement2->id);
  }

  public function testSaveFromEntitlementCalculationCanSaveOverriddenValues() {
    // This mocks the logged in user so we can test
    // the LeavePeriodEntitlement creation with a comment
    global $user;
    $user = new stdClass();
    $user->uid = 1;

    $type = new AbsenceType();
    $type->id = 1;
    $period = new AbsencePeriod();
    $period->id = 1;
    $contract = ['id' => 1];

    $broughtForward = 1;
    $proRata = 10;
    $overridden = true;
    $calculation = $this->getEntitlementCalculationMock(
      $period,
      $contract,
      $type,
      $broughtForward,
      $proRata,
      [],
      $overridden
    );

    $overriddenEntitlement = 50;
    $comment = 'Lorem ipsum dolor sit amet...';
    LeavePeriodEntitlement::saveFromCalculation($calculation, $overriddenEntitlement, $comment);

    $periodEntitlement = LeavePeriodEntitlement::getPeriodEntitlementForContract(
      $contract['id'],
      $period->id,
      $type->id
    );

    $this->assertNotNull($periodEntitlement);
    $this->assertEquals($period->id, $periodEntitlement->period_id);
    $this->assertEquals($type->id, $periodEntitlement->type_id);
    $this->assertEquals($contract['id'], $periodEntitlement->contract_id);
    $this->assertEquals(1, $periodEntitlement->overridden);
    $this->assertEquals($overriddenEntitlement, $periodEntitlement->getEntitlement());

    $user = null;
  }

  private function createPeriodEntitlement() {
    return LeavePeriodEntitlement::create([
      'type_id'     => 1,
      'period_id'   => 1,
      'contract_id' => 1
    ]);
  }

  /**
   * Mock the calculation, as we only need to test
   * if the LeavePeriodEntitlement BAO can create an new LeavePeriodEntitlement
   * from a EntitlementCalculation instance
   *
   * @param $period
   * @param $contract
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
    $contract,
    $type,
    $broughtForward = 0,
    $proRata = 0,
    $publicHolidays = [],
    $overridden = false
  ) {
    $calculation = $this->getMockBuilder(EntitlementCalculation::class)
                        ->setConstructorArgs([$period, $contract, $type])
                        ->setMethods([
                          'getBroughtForward',
                          'getProRata',
                          'getBroughtForwardExpirationDate',
                          'getPublicHolidaysInEntitlement'
                        ])
                        ->getMock();

    $calculation->expects($overridden ? $this->never() : $this->once())
                ->method('getBroughtForward')
                ->will($this->returnValue($broughtForward));

    $calculation->expects($overridden ? $this->never() : $this->once())
                ->method('getProRata')
                ->will($this->returnValue($proRata));

    $calculation->expects($overridden ? $this->never() : $this->once())
                ->method('getBroughtForwardExpirationDate')
                ->will($this->returnValue('2016-01-01'));

    $publicHolidaysReturn = [];
    foreach($publicHolidays as $publicHoliday) {
      $instance = new PublicHoliday();
      $instance->date = $publicHoliday;
      $publicHolidaysReturn[] = $instance;
    }

    $calculation->expects($overridden ? $this->never() : $this->once())
                ->method('getPublicHolidaysInEntitlement')
                ->will($this->returnValue($publicHolidaysReturn));

    return $calculation;
  }
}
