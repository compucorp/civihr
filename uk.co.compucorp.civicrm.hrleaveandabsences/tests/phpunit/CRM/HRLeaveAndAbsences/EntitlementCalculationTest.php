<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_Entitlement as Entitlement;
use CRM_Hrjobcontract_BAO_HRJobContract as JobContract;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_BroughtForward as BroughtForward;
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
    $jobContractUpgrader = CRM_Hrjobcontract_Upgrader::instance();
    $jobContractUpgrader->install();
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

    $this->createEntitlement([
      'period_id' => $previousPeriod->id,
      'type_id' => $type->id
    ]);

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

    $this->createEntitlement([
      'period_id' => $previousPeriod->id,
      'type_id' => $type->id
    ]);
    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);

    // As the number of leaves taken is 0 at this point,
    // all of the proposed_entitlement from the previous period
    // entitlement will be carried to the current period, but that
    // is more than the max number of days allowed by the absence type,
    // so the carried amount should be reduced
    $this->assertEquals(
      $type->max_number_of_days_to_carry_forward,
      $calculation->getBroughtForward()
    );
  }

  public function testBroughtForwardShouldNotIncludeExpiredBroughtForwardInPreviousPeriod()
  {
    $type = $this->createAbsenceType([
      'max_number_of_days_to_carry_forward' => 20
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

    $entitlement = $this->createEntitlement([
      'period_id' => $previousPeriod->id,
      'type_id' => $type->id,
      'pro_rata' => 10,
      'proposed_entitlement' => 15,
    ]);

    BroughtForward::create([
      'balance' => 5,
      'expiration_date' =>  $previousPeriod->start_date,
      'entitlement_id' => $entitlement->id
    ]);

    // Negative brought forward means it has expired
    BroughtForward::create([
      'balance' => -2,
      'expiration_date' =>  $previousPeriod->start_date,
      'entitlement_id' => $entitlement->id
    ]);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);

    // The previous period balance is calculated as:
    // - Pro Rata: +10
    // - Brought Forward: +5
    // - Expired Brought Forward: -2
    // - Total: + 10 + 5 - 2: 13
    // As the max number of days allowed to be carried forward is more than that,
    // all the 13 days will be brought forward
    $this->assertEquals(13, $calculation->getBroughtForward());
  }

  public function testProRataShouldBeZeroIfTheContractDoesntHaveStartAndEndDates()
  {
    $type = new AbsenceType();

    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day')),
    ]);
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    $this->assertEquals(0, $calculation->getProRata());
  }

  public function testProRataShouldBeRoundedToTheNearestHalfDay()
  {
    $type = $this->createAbsenceType();

    // 261 working days
    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis', strtotime('2016-01-01')),
      'end_date' => date('YmdHis', strtotime('2016-12-31')),
    ]);
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    // 21 days to work
    $this->setContractDates(
      date('YmdHis', strtotime('2016-01-01')),
      date('YmdHis', strtotime('2016-01-31'))
    );
    $this->createJobLeaveEntitlement($type, 10);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    // (21/261) * 10 = 0.80 = 1 (rounded)
    $this->assertEquals(1, $calculation->getProRata());

    // 32 days to work
    $this->setContractDates(
      date('YmdHis', strtotime('2016-01-01')),
      date('YmdHis', strtotime('2016-02-15'))
    );

    //Instantiates the calculation again to get the updated contract dates
    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    // (32/261) * 10 = 1.22 = 1.5 rounded
    $this->assertEquals(1.5, $calculation->getProRata());

    // 66 days to work
    $this->setContractDates(
      date('YmdHis', strtotime('2016-01-01')),
      date('YmdHis', strtotime('2016-04-01'))
    );

    //Instantiates the calculation again to get the updated contract dates
    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    // (66/261) * 10 = 2.52 = 3 rounded
    $this->assertEquals(3, $calculation->getProRata());
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

    PublicHoliday::create([
      'title' => 'Holiday 1',
      'date' => date('YmdHis', strtotime('+1 day'))
    ]);
    PublicHoliday::create([
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

    PublicHoliday::create([
      'title' => 'Holiday 1',
      'date' => date('YmdHis', strtotime('+1 day'))
    ]);
    PublicHoliday::create([
      'title' => 'Holiday 2',
      'date' => date('YmdHis', strtotime('+3 days'))
    ]);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);

    $this->assertEquals($leaveAmount, $calculation->getContractualEntitlement());
  }

  public function testTheProposedEntitlementForAContractWithoutStartAndEndDatesShouldBeZero()
  {
    $type = $this->createAbsenceType();
    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 days'))
    ]);
    // We need to load the period from the database to get the dates in the
    // expected format: Y-m-d
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    $this->assertEquals(0, $calculation->getProposedEntitlement());
  }

  public function testTheProposedEntitlementForAPeriodWithPreviouslyOverriddenEntitlementShouldBeTheTheOverriddenValue()
  {
    $type = $this->createAbsenceType();
    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 days'))
    ]);
    $this->createEntitlement([
      'period_id' => $currentPeriod->id,
      'type_id' => $type->id,
      'proposed_entitlement' => 25.3,
      'overridden' => true
    ]);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    $this->assertEquals(25.3, $calculation->getProposedEntitlement());
  }

  public function testTheProposedEntitlementShouldBeProRataPlusNumberOfDaysBroughtForward()
  {
    // To simplify the code, we use an Absence where the carried
    // forward never expires
    $type = $this->createAbsenceType([
      'max_number_of_days_to_carry_forward' => 30,
    ]);

    // Set the contractual entitlement as 20 days
    $this->createJobLeaveEntitlement($type, 20);

    // Period with 261 working days
    $period2 = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis', strtotime('2015-01-01')),
      'end_date' => date('YmdHis', strtotime('2015-12-31')),
    ]);

    // Set the previous period entitlement as 10 days
    $entitlementPeriod2 = $this->createEntitlement([
      'period_id' => $period2->id,
      'type_id' => $type->id,
      'proposed_entitlement' => 22, // Pro-rata + 2 BF days
      'pro_rata' => 20
    ]);

    // 2 days Brought Forward from Period 1
    BroughtForward::create([
      'balance' => 2,
      'entitlement_id' => $entitlementPeriod2->id
    ]);

    // 1 of the 2 days Brought Forward from Period 1 has expired
    BroughtForward::create([
      'balance' => -1,
      'entitlement_id' => $entitlementPeriod2->id
    ]);

    // 261 working days
    $period3 = AbsencePeriod::create([
      'title' => 'Period 3',
      'start_date' => date('YmdHis', strtotime('2016-01-01')),
      'end_date' => date('YmdHis', strtotime('2016-12-31')),
    ]);
    $period3 = $this->findAbsencePeriodByID($period3->id);

    // 66 days to work
    $this->setContractDates(
      date('YmdHis', strtotime('2016-01-01')),
      date('YmdHis', strtotime('2016-04-01'))
    );

    $calculation = new EntitlementCalculation($period3, $this->contract, $type);
    // (days to work / working days) * Contractual Entitlement
    // (66/261) * 20 = 5.05 = 5.5 rounded
    $this->assertEquals(5.5, $calculation->getProRata());

    // The Period 2 balance is calculated as:
    // - Pro Rata: +20
    // - Brought Forward from Period 1: +2
    // - Expired Brought Forward from Period 1: -1
    // - Leaves Taken: 0 (we don't have leave requests yet)
    // - Total: + 20 + 2 - 1: 21
    // As this is less than the max allowed to be carried forward, the whole
    // value will be brought forward to period 3
    $this->assertEquals(21, $calculation->getBroughtForward());

    // Proposed Entitlement in Period 2: 22
    // Expired Brought Forward in Period 2: 1
    // Leaves Taken in Period 2: 0 (we don't have leave requests yet)
    // Brought Forward from Period 2: 21 (Proposed entitlement - expired BF - no. leaves request)
    // Number of Working days: 261
    // Number of Days to work: 66
    // Contractual Entitlement: 20
    // Pro Rata: (Number of days to work / Number working days) * Contractual Entitlement
    // Pro Rata: (66/261) * 20 = 5.05 = 5.5 rounded
    // Proposed entitlement: Pro Rata + Number of days brought from previous period
    // Proposed entitlement: 5.5 + 21
    $this->assertEquals(26.5, $calculation->getProposedEntitlement());
  }

  public function testPreviousPeriodProposedEntitlementShouldBeZeroIfThereIsNoPreviousPeriod()
  {
    $type = $this->createAbsenceType();

    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis', strtotime('2016-01-01')),
      'end_date' => date('YmdHis', strtotime('2016-12-31')),
    ]);
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    $this->assertEquals(0, $calculation->getPreviousPeriodProposedEntitlement());
  }

  public function testPreviousPeriodProposedEntitlementShouldBeZeroIfThereIsNoEntitlementForThePreviousPeriod()
  {
    $type = $this->createAbsenceType();

    $previousPeriod = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis', strtotime('2015-01-01')),
      'end_date' => date('YmdHis', strtotime('2015-12-31')),
    ]);

    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis', strtotime('2016-01-01')),
      'end_date' => date('YmdHis', strtotime('2016-12-31')),
    ]);
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    $this->assertEquals(0, $calculation->getPreviousPeriodProposedEntitlement());
  }

  public function testPreviousPeriodProposedEntitlementShouldReturnTheProposedEntitlement()
  {
    $type = $this->createAbsenceType();

    $previousPeriod = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis', strtotime('2015-01-01')),
      'end_date' => date('YmdHis', strtotime('2015-12-31')),
    ]);

    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis', strtotime('2016-01-01')),
      'end_date' => date('YmdHis', strtotime('2016-12-31')),
    ]);
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    $this->createEntitlement([
      'period_id' => $previousPeriod->id,
      'type_id' => $type->id,
      'proposed_entitlement' => 10
    ]);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    $this->assertEquals(10, $calculation->getPreviousPeriodProposedEntitlement());
  }

  /**
   * This is the only possible test for now as leave requests are not yet implemented
   */
  public function testNumberOfLeavesTakenOnThePreviousPeriodShouldBeZeroIfThereIsNoPreviousPeriod()
  {
    $type = new AbsenceType();
    $period = new AbsencePeriod();

    $calculation = new EntitlementCalculation($period, $this->contract, $type);
    $this->assertEquals(0, $calculation->getNumberOfLeavesTakenOnThePreviousPeriod());
  }

  public function testNumberOfDaysRemainingInThePreviousPeriodShouldBeZeroIfThereIsNoPreviousPeriod()
  {
    $type = $this->createAbsenceType();
    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis', strtotime('2016-01-01')),
      'end_date' => date('YmdHis', strtotime('2016-12-31')),
    ]);
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    $this->assertEquals(0, $calculation->getNumberOfDaysRemainingInThePreviousPeriod());
  }

  public function testNumberOfDaysRemainingInThePreviousPeriodShouldBeEqualsToProposedEntitlementMinusLeavesTaken()
  {
    $type = $this->createAbsenceType();

    $previousPeriod = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis', strtotime('2015-01-01')),
      'end_date' => date('YmdHis', strtotime('2015-12-31')),
    ]);

    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis', strtotime('2016-01-01')),
      'end_date' => date('YmdHis', strtotime('2016-12-31')),
    ]);
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    $this->createEntitlement([
      'period_id' => $previousPeriod->id,
      'type_id' => $type->id,
      'proposed_entitlement' => 10
    ]);

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);
    $this->assertEquals(10, $calculation->getNumberOfDaysRemainingInThePreviousPeriod());
  }

  public function testGetAbsenceTypeShouldReturnTheAbsenceTypeUsedToCreateTheCalculation()
  {
    $type = new AbsenceType();
    $type->title = 'Absence Type 1';

    $period = new AbsencePeriod();

    $calculation = new EntitlementCalculation($period, [], $type);
    $this->assertEquals($type, $calculation->getAbsenceType());
  }

  public function testGetContractShouldReturnTheContractUsedToCreateTheCalculation()
  {
    $type = new AbsenceType();
    $period = new AbsencePeriod();

    $calculation = new EntitlementCalculation($period, $this->contract, $type);
    $this->assertEquals($this->contract, $calculation->getContract());
  }

  public function testIsCurrentPeriodEntitlementOverriddenShouldBeFalseIfThereIsNoPreviouslyCalculatedEntitlement()
  {
    $type = $this->createAbsenceType();
    $period = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day'))
    ]);

    $calculation = new EntitlementCalculation($period, $this->contract, $type);
    $this->assertFalse($calculation->isCurrentPeriodEntitlementOverridden());
  }

  public function testIsCurrentPeriodEntitlementOverriddenShouldBeFalseIfThePreviouslyCalculatedEntitlementIsNotOverridden()
  {
    $type = $this->createAbsenceType();
    $period = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day'))
    ]);
    $this->createEntitlement([
      'period_id' => $period->id,
      'type_id' => $type->id,
      'proposed_entitlement' => 10
    ]);

    $calculation = new EntitlementCalculation($period, $this->contract, $type);
    $this->assertFalse($calculation->isCurrentPeriodEntitlementOverridden());
  }

  public function testIsCurrentPeriodEntitlementOverriddenShouldBeTrueIfThePreviouslyCalculatedEntitlementIsOverridden()
  {
    $type = $this->createAbsenceType();
    $period = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day'))
    ]);
    $this->createEntitlement([
      'period_id' => $period->id,
      'type_id' => $type->id,
      'proposed_entitlement' => 10,
      'overridden' => true
    ]);

    $calculation = new EntitlementCalculation($period, $this->contract, $type);
    $this->assertTrue($calculation->isCurrentPeriodEntitlementOverridden());
  }

  public function testGetCurrentPeriodEntitlementCommentReturnsAnEmptyStringIfThereIsNoPreviouslyCalculatedEntitlement()
  {
    $type = $this->createAbsenceType();
    $period = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day'))
    ]);

    $calculation = new EntitlementCalculation($period, $this->contract, $type);
    $this->assertEmpty($calculation->getCurrentPeriodEntitlementComment());
  }

  public function testGetCurrentPeriodEntitlementCommentReturnsAnEmptyStringIfThereThePreviouslyCalculatedEntitlementHasNoComment()
  {
    $type = $this->createAbsenceType();
    $period = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day'))
    ]);
    $this->createEntitlement([
      'period_id' => $period->id,
      'type_id' => $type->id,
      'proposed_entitlement' => 10
    ]);

    $calculation = new EntitlementCalculation($period, $this->contract, $type);
    $this->assertEmpty($calculation->getCurrentPeriodEntitlementComment());
  }

  public function testGetCurrentPeriodEntitlementCommentReturnsTheCommentIfThereThePreviouslyCalculatedEntitlementHasOne()
  {
    $type = $this->createAbsenceType();
    $period = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day'))
    ]);

    $comment = 'Lorem ipsum...';
    $this->createEntitlement([
      'period_id' => $period->id,
      'type_id' => $type->id,
      'proposed_entitlement' => 10,
      'comment' => $comment
    ]);

    $calculation = new EntitlementCalculation($period, $this->contract, $type);
    $this->assertEquals($comment, $calculation->getCurrentPeriodEntitlementComment());
  }

  public function testCalculationCanReturnItsStringRepresentation()
  {
    // To simplify the code, we use an Absence where the carried
    // forward never expires
    $type = $this->createAbsenceType([
      'max_number_of_days_to_carry_forward' => 5,
    ]);

    $previousPeriod = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis', strtotime('2015-01-01')),
      'end_date' => date('YmdHis', strtotime('2015-12-31')),
    ]);

    // Set the previous period entitlement as 20 days
    $this->createEntitlement([
      'period_id' => $previousPeriod->id,
      'type_id' => $type->id,
      'proposed_entitlement' => 20
    ]);

    // 261 working days - 2 public holidays = 259 working days
    $currentPeriod = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis', strtotime('2016-01-01')),
      'end_date' => date('YmdHis', strtotime('2016-12-31')),
    ]);
    $currentPeriod = $this->findAbsencePeriodByID($currentPeriod->id);

    PublicHoliday::create([
      'title' => 'Holiday 1',
      'date' => date('YmdHis', strtotime('2016-05-02'))
    ]);
    PublicHoliday::create([
      'title' => 'Holiday 2',
      'date' => date('YmdHis', strtotime('2016-05-30'))
    ]);
    // We need to clear the cache to avoid returning a number of public holidays
    // counted on previous tests
    PublicHoliday::clearNumberOfPublicHolidaysForPeriodCache();

    // Set the contractual entitlement as 10 days
    $this->createJobLeaveEntitlement($type, 20, true);

    // 64 days to work
    $this->setContractDates(
      date('YmdHis', strtotime('2016-01-01')),
      date('YmdHis', strtotime('2016-03-30'))
    );

    $calculation = new EntitlementCalculation($currentPeriod, $this->contract, $type);

    //Contractual Entitlement = 20
    //Number of Public Holidays = 2
    //Number of days to work = 64
    //Number of working days = 259
    //Pro rata = 5.5
    //Brought forward = 5
    //Proposed Entitlement = 10.5
    $expected = '((20 + 2) * (64 / 259)) = (5.5) + (5) = 10.5 days';
    $calculationDetails = sprintf('%s', $calculation);
    $this->assertEquals($expected, $calculationDetails);
  }

  private function findAbsencePeriodByID($id) {
    $currentPeriod     = new AbsencePeriod();
    $currentPeriod->id = $id;
    $currentPeriod->find(TRUE);
    return $currentPeriod;
  }

  private function createContract() {
    $result = civicrm_api3('HRJobContract', 'create', [
      'contact_id' => 2, //Existing contact from civicrm_data.mysql,
      'is_primary' => 1,
      'sequential' => 1
    ]);
    $this->contract = $result['values'][0];
  }

  private function createEntitlement($params) {
    $defaultParams = [
      'contract_id'          => $this->contract['id'],
      'overridden'           => '0',
      'proposed_entitlement' => 20
    ];

    if(!empty($params['comment']) && empty($params['comment_author_id'])) {
      $params['comment_author_id'] = $this->contract['contact_id'];
    }

    if(!empty($params['comment']) && empty($params['comment_updated_at'])) {
      $params['comment_updated_at'] = date('YmdHis');
    }

    $params = array_merge($defaultParams, $params);

    if(empty($params['pro_rata'])) {
      $params['pro_rata'] = $params['proposed_entitlement'];
    }

    return Entitlement::create($params);
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
      'jobcontract_id' => $this->contract['id'],
      'leave_type' => $type->id,
      'leave_amount' => $leaveAmount,
      'add_public_holidays' => $addPublicHolidays ? '1' : '0'
    ]);
  }

  private function setContractDates($startDate, $endDate) {
    CRM_Hrjobcontract_BAO_HRJobDetails::create([
      'jobcontract_id' => $this->contract['id'],
      'period_start_date' => $startDate,
      'period_end_date' => $endDate,
    ]);
  }
}
