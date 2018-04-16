<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChangeExpiryLog as LeaveBalanceChangeExpiryLog;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChangeExpiryLogTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChangeExpiryLogTest extends BaseHeadlessTest {

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveBalanceChangeExpiryLogException
   * @expectedExceptionMessage The balance_change_id field should not be empty
   */
  public function testCreateLeaveBalanceChangeExpiryLogThrowsExceptionWhenBalanceChangeIdIsAbsent() {
    LeaveBalanceChangeExpiryLog::create([
      'source_id' => 1,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'balance_type_id' => 1,
      'amount' => 3,
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveBalanceChangeExpiryLogException
   * @expectedExceptionMessage The source_id field should not be empty
   */
  public function testCreateLeaveBalanceChangeExpiryLogThrowsExceptionWhenSourceIdIsAbsent() {
    LeaveBalanceChangeExpiryLog::create([
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'balance_type_id' => 1,
      'amount' => 3,
      'balance_change_id' => 1,
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveBalanceChangeExpiryLogException
   * @expectedExceptionMessage The source_type field should not be empty
   */
  public function testCreateLeaveBalanceChangeExpiryLogThrowsExceptionWhenSourceTypeIsAbsent() {
    LeaveBalanceChangeExpiryLog::create([
      'source_id' => 1,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'balance_type_id' => 1,
      'amount' => 3,
      'balance_change_id' => 1,
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveBalanceChangeExpiryLogException
   * @expectedExceptionMessage The expiry_date field should not be empty
   */
  public function testCreateLeaveBalanceChangeExpiryLogThrowsExceptionWhenExpiryDateIsAbsent() {
    LeaveBalanceChangeExpiryLog::create([
      'source_id' => 1,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'balance_type_id' => 1,
      'amount' => 3,
      'balance_change_id' => 1,
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveBalanceChangeExpiryLogException
   * @expectedExceptionMessage The amount field should not be empty
   */
  public function testCreateLeaveBalanceChangeExpiryLogThrowsExceptionWhenAmountIsAbsent() {
    LeaveBalanceChangeExpiryLog::create([
      'source_id' => 1,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'balance_type_id' => 1,
      'balance_change_id' => 1,
    ]);
  }

  public function testCreateLeaveBalanceChangeExpiryLogDoesNotThrowExceptionWhenAmountIsZero() {
    $expiryLog = LeaveBalanceChangeExpiryLog::create([
      'balance_change_id' => 1,
      'source_id' => 1,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'balance_type_id' => 1,
      'amount' => 0,
    ]);

    $this->assertNotNull($expiryLog->id);
  }

  public function testUpdatesNotAllowedForTheLeaveBalanceChangeExpiryLogEntity() {
    $params = [
      'balance_change_id' => 1,
      'source_id' => 1,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'expiry_date' => CRM_Utils_Date::processDate('2017-02-01'),
      'balance_type_id' => 1,
      'amount' => 3,
      'leave_date' => CRM_Utils_Date::processDate('2017-01-01'),
      'leave_request_id' => 1,
    ];

    $expiryLog = LeaveBalanceChangeExpiryLog::create($params);
    $this->setExpectedException(
      CRM_HRLeaveAndAbsences_Exception_InvalidLeaveBalanceChangeExpiryLogException::class,
      'Updates not allowed for the LeaveBalanceChange Expiry Log entity'
    );

    $params['id'] = $expiryLog->id;
    LeaveBalanceChangeExpiryLog::create($params);
  }

  public function testTheCreatedDateCanNotBeManipulated() {
    $params = [
      'balance_change_id' => 1,
      'source_id' => 1,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'balance_type_id' => 1,
      'amount' => 3,
      'created_date' => CRM_Utils_Date::processDate('2016-01-01')
    ];

    $dateNow = new DateTime();
    $expiryLog = LeaveBalanceChangeExpiryLog::create($params);
    $this->assertEquals($dateNow, new DateTime($expiryLog->created_date), 10);
  }
}
