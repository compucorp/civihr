<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_Entitlement as Entitlement;
use CRM_Hrjobcontract_BAO_HRJobContract as JobContract;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_EntitlementCalculation as EntitlementCalculation;

/**
 * Class CRM_HRLeaveAndAbsences_EntitlementCalculationTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_EntitlementCalculationTest extends PHPUnit_Framework_TestCase implements
 HeadlessInterface, TransactionalInterface {

  private $contract;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->install('org.civicrm.hrjobcontract')
      ->apply();
  }

  public function setUp()
  {
    $this->createContract();
  }

  public function testBroughtForwardShouldBeZeroIfAbsenceTypeDoesntAllowCarryForward()
  {
    $period = new AbsencePeriod();
    $contract = new JobContract();
    $type = new AbsenceType();

    $type->allow_carry_forward = false;
    $calculation = new EntitlementCalculation($period, $contract, $type);

    $this->assertEquals(0, $calculation->getBroughtForward());
  }

  public function testBroughtForwardShouldBeZeroIfTheresNoPreviousPeriod()
  {
    $type = $this->createAbsenceType();

    $period = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $calculation = new EntitlementCalculation($period, $this->contract, $type);
    $this->assertEquals(0, $calculation->getBroughtForward());
  }

  public function testBroughtForwardShouldBeZeroIfTheresNoCalculatedEntitlementForPreviousPeriod()
  {
    $type = $this->createAbsenceType();

    AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2015-12-31'),
    ]);

    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    $this->assertEquals(0, $calculation->getBroughtForward());
  }

  public function testBroughtForwardShouldBeZeroIfExpirationDurationHasExpired()
  {
    $type = $this->createAbsenceType([
      'max_number_of_days_to_carry_forward' => 50,
      'carry_forward_expiration_unit'       => AbsenceType::EXPIRATION_UNIT_DAYS,
      'carry_forward_expiration_duration'   => 5
    ]);

    $previousPeriod = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis', strtotime('-7 days')),
      'end_date' => date('YmdHis', strtotime('-6 days')),
    ]);

    // The absence type expires in 5 days, so we set the period start date to
    // 5 days ago to expire the brought forward days
    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis', strtotime('-5 days')),
      'end_date' => date('YmdHis', strtotime('now')),
    ]);
    //Load the period from the database to get the dates
    //back in Y-m-d format.
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    $this->createEntitlement($previousPeriod, $type);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    $this->assertEquals(0, $calculation->getBroughtForward());
  }

  public function testBroughtForwardShouldNotBeMoreThanTheMaxNumberOfDaysAllowedToBeCarriedForward()
  {
    $type = $this->createAbsenceType([
      'max_number_of_days_to_carry_forward' => 5
    ]);

    $previousPeriod = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis', strtotime('-2 days')),
      'end_date' => date('YmdHis', strtotime('-1 day')),
    ]);

    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day')),
    ]);
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    $this->createEntitlement($previousPeriod, $type);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);

    // As the number of leaves taken is 0 at this point,
    // all of the proposed_entitlement from the previous period
    // entitlement will be carried to the current period, but that
    // is more than the max number of days allowed by the absence type,
    // so the carried amount should be reduced
    $this->assertEquals(5, $calculation->getBroughtForward());
  }

  public function testContractualEntitlementShouldBeZeroIfTheresNoLeaveAmountForAnAbsenceType()
  {
    // Just create the absence type but don't create any contractual entitlement
    $type = $this->createAbsenceType();
    $currentPeriod = new AbsencePeriod();
    $currentPeriod->start_date = date('Y-m-d');
    $currentPeriod->end_date = date('Y-m-d', strtotime('+1 day'));

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    $this->assertEquals(0, $calculation->getContractualEntitlement());
  }

  public function testContractualEntitlementShouldBeEqualToLeaveAmountIfTheresNoPublicHolidayInPeriod()
  {
    $type = $this->createAbsenceType();

    $leaveAmount = 10;
    $this->createJobLeaveEntitlement($type, $leaveAmount);

    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day')),
    ]);
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);

    $this->assertEquals($leaveAmount, $calculation->getContractualEntitlement());
  }

  public function testContractualEntitlementShouldBeEqualToLeaveAmountPlusPublicHolidaysIfThereArePublicHolidayInPeriod()
  {
    $type = $this->createAbsenceType();

    $leaveAmount = 10;
    $addPublicHolidays = true;
    $this->createJobLeaveEntitlement($type, $leaveAmount, $addPublicHolidays);

    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+4 days')),
    ]);
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Holiday 1',
      'date' => date('YmdHis', strtotime('+1 day'))
    ]);
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Holiday 2',
      'date' => date('YmdHis', strtotime('+3 days'))
    ]);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);

    $this->assertEquals(12, $calculation->getContractualEntitlement());
  }

  public function testContractualEntitlementShouldNotIncludePublicHolidaysIfTheJobLeaveDoesntAllowIt()
  {
    $type = $this->createAbsenceType();

    $leaveAmount = 10;
    $addPublicHolidays = false;
    $this->createJobLeaveEntitlement($type, $leaveAmount, $addPublicHolidays);

    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+4 days')),
    ]);
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Holiday 1',
      'date' => date('YmdHis', strtotime('+1 day'))
    ]);
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Holiday 2',
      'date' => date('YmdHis', strtotime('+3 days'))
    ]);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);

    $this->assertEquals($leaveAmount, $calculation->getContractualEntitlement());
  }

  private function findAbsencePeriodByID($id) {
    $currentPeriod     = new AbsencePeriod();
    $currentPeriod->id = $id;
    $currentPeriod->find(TRUE);
    return $currentPeriod;
  }

  private function createContract() {
    $this->contract = JobContract::create([
      'contact_id' => 2, //Existing contact from civicrm_data.mysql,
      'is_primary' => 1
    ]);
  }

  private function createEntitlement($period, $type, $proposedEntitlement = 20) {
    Entitlement::create([
      'period_id'            => $period->id,
      'contract_id'          => $this->contract->id,
      'type_id'              => $type->id,
      'proposed_entitlement' => $proposedEntitlement,
    ]);
  }

  private function createAbsenceType($params = []) {
    $basicRequiredFields = [
      'title'                     => 'Type ' . microtime(),
      'color'                     => '#000000',
      'default_entitlement'       => 20,
      'allow_request_cancelation' => 1,
      'allow_carry_forward'       => 1,
    ];

    $params = array_merge($basicRequiredFields, $params);
    return AbsenceType::create($params);
  }

  private function createJobLeaveEntitlement($type, $leaveAmount, $addPublicHolidays = false) {
    CRM_Hrjobcontract_BAO_HRJobLeave::create([
      'jobcontract_id' => $this->contract->id,
      'leave_type' => $type->id,
      'leave_amount' => $leaveAmount,
      'add_public_holidays' => $addPublicHolidays ? '1' : '0'
    ]);
  }
}
