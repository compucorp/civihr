<?php

require_once __DIR__.'/../../CRM/HRLeaveAndAbsences/LeaveBalanceChangeHelpersTrait.php';

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class api_v3_LeaveBalanceChangeTest
 *
 * @group headless
 */
class api_v3_LeaveBalanceChangeTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface,
  TransactionalInterface {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  public function setUp() {
    // In order to make tests simpler, we disable the foreign key checks,
    // as a way to allow the creation of brought forward records related
    // to a non-existing entitlement
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");
  }

  public function testCreateExpirationRecords() {
    $result = civicrm_api3('LeaveBalanceChange', 'createexpirationrecords');
    $this->assertEquals(0, $result);

    $this->createBroughtForwardBalanceChange(1, 2, date('YmdHis', strtotime('-1 day')));
    $this->createBroughtForwardBalanceChange(2, 5, date('YmdHis'));
    $this->createBroughtForwardBalanceChange(3, 3.5, date('YmdHis', strtotime('-2 days')));

    // Should create two records: one for the entitlement 1 and another one
    // for entitlement 3. The brought forward for entitlement 2 has not expired
    $result = civicrm_api3('LeaveBalanceChange', 'createexpirationrecords');
    $this->assertEquals(2, $result);
  }
}
