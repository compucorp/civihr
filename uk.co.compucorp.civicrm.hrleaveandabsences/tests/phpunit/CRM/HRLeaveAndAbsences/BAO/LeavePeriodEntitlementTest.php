<?php

require_once __DIR__."/../LeaveBalanceChangeHelpersTrait.php";

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;

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

  private function createPeriodEntitlement() {
    return LeavePeriodEntitlement::create([
      'type_id'     => 1,
      'period_id'   => 1,
      'contract_id' => 1
    ]);
  }
}
