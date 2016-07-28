<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_Hrjobcontract_BAO_HRJobContract as JobContract;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChangeTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChangeTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
                     ->installMe(__DIR__)
                     ->install('org.civicrm.hrjobcontract')
                     ->apply();
  }

  /**
   * @expectedException PEAR_Exception
   * @expectedExceptionMessage DB Error: already exists
   */
  public function testThereCannotBeMoreThanOneExpiredRecordForEachBalanceChange() {
    $entitlement = $this->createLeavePeriodEntitlement();

    $balanceChangeToExpire = LeaveBalanceChange::create([
      'entitlement_id' => $entitlement->id,
      'type_id' => 1,
      'amount' => 3,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01')
    ]);

    $this->assertNotEmpty($balanceChangeToExpire->id);

    $expiryBalanceChange = LeaveBalanceChange::create([
      'entitlement_id' => $entitlement->id,
      'type_id' => 1,
      'amount' => -3,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'expired_balance_id' => $balanceChangeToExpire->id
    ]);

    $this->assertNotEmpty($expiryBalanceChange->id);

    // A second expiry record should not be allowed to be created
    LeaveBalanceChange::create([
      'entitlement_id' => $entitlement->id,
      'type_id' => 1,
      'amount' => -3,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'expired_balance_id' => $balanceChangeToExpire->id
    ]);
  }


  private function createAbsenceType() {
    $type = AbsenceType::create([
      'title'                     => 'Type ' . microtime(),
      'color'                     => '#000000',
      'default_entitlement'       => 20,
      'allow_request_cancelation' => 1,
    ]);
    return $type;
  }

  private function createJobContract() {
    $contract = JobContract::create([
      'contact_id' => 2, //Existing contact from civicrm_data.mysql,
      'is_primary' => 1
    ]);
    return $contract;
  }

  private function createAbsencePeriod() {
    $period = AbsencePeriod::create([
      'title'      => 'Period ' . microtime(),
      'start_date' => date('YmdHis'),
      'end_date'   => date('YmdHis', strtotime('+1 day'))
    ]);
    return $period;
  }

  private function createLeavePeriodEntitlement() {
    $absenceType = $this->createAbsenceType();
    $absencePeriod = $this->createAbsencePeriod();
    $jobContract = $this->createJobContract();

    return LeavePeriodEntitlement::create([
      'type_id' => $absenceType->id,
      'period_id' => $absencePeriod->id,
      'contract_id' => $jobContract->id
    ]);
  }
}
