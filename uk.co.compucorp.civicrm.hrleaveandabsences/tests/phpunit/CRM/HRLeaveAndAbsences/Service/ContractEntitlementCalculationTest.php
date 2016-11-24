<?php

use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_Service_ContractEntitlementCalculation as ContractEntitlementCalculation;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHoliday as PublicHolidayFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_EntitlementCalculationTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_ContractEntitlementCalculationTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_ContractHelpersTrait;

  public function setUp() {
    $this->createContract();
  }

  public function testProRataShouldBeZeroIfThereIsNoContractualEntitlement() {
    $this->contract['period_start_date'] = '2016-03-10';
    $this->contract['period_end_date'] = '2016-09-23';

    $type = AbsenceTypeFabricator::fabricate();

    $period = $this->getAbsencePeriodMock([
      'getNumberOfWorkingDays' => [
        'willReturn' => 261
      ],
      'getNumberOfWorkingDaysToWork' => [
        'withParams' => [
          $this->contract['period_start_date'],
          $this->contract['period_end_date']
        ],
        'willReturn' => 142
      ]
    ]);

    $calculation = new ContractEntitlementCalculation($period, $this->contract, $type);

    // 0 * (142/261) = 0
    $this->assertEquals(0, $calculation->getProRata());
  }

  public function testProRataIncludePublicHolidaysBetweenContractDatesIfContractSaysTheyShouldBeAdded() {
    $this->contract['period_start_date'] = '2016-03-10';
    $this->contract['period_end_date'] = '2016-09-23';

    $type = AbsenceTypeFabricator::fabricate();

    $period = $this->getAbsencePeriodMock([
      'getNumberOfWorkingDays' => [
        'willReturn' => 260
      ],
      'getNumberOfWorkingDaysToWork' => [
        'withParams' => [
          $this->contract['period_start_date'],
          $this->contract['period_end_date']
        ],
        'willReturn' => 141
      ]
    ]);

    $this->createJobLeaveEntitlement($type, 20, true);

    // This is between the contract dates, but will not be included
    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('2016-05-18'))
    ]);

    $calculation = new ContractEntitlementCalculation($period, $this->contract, $type);

    // (20 * (141/260)) + 1 = 11.84
    $this->assertEquals((20 * (141/260)) + 1, $calculation->getProRata());
  }

  public function testNumberOfWorkingDaysShouldBeTheNumberOfWorkingDaysInTheCalculationAbsencePeriod() {
    $period = $this->getAbsencePeriodMock([
      'getNumberOfWorkingDays' => [
        'willReturn' => 260
      ]
    ]);

    $calculation = new ContractEntitlementCalculation($period, [], new AbsenceType());

    $this->assertEquals(260, $calculation->getNumberOfWorkingDays());
  }

  public function testNumberOfWorkingDaysToWorkShouldBeTheNumberOfWorkingDaysBetweenTheContractDates() {
    $this->contract['period_start_date'] = '2016-03-10';
    $this->contract['period_end_date'] = '2016-09-23';

    $period = $this->getAbsencePeriodMock([
      'getNumberOfWorkingDaysToWork' => [
        'withParams' => [
          $this->contract['period_start_date'],
          $this->contract['period_end_date']
        ],
        'willReturn' => 150
      ]
    ]);

    $calculation = new ContractEntitlementCalculation($period, $this->contract, new AbsenceType());

    $this->assertEquals(150, $calculation->getNumberOfWorkingDaysToWork());
  }

  public function testContractualEntitlementShouldBeZeroIfThereIsNoContractualEntitlement() {
    $calculation = new ContractEntitlementCalculation(new AbsencePeriod(), $this->contract, new AbsenceType());

    $this->assertEquals(0, $calculation->getContractualEntitlement());
  }

  public function testContractualEntitlementShouldBeTheLeaveAmountInJobLeave() {
    $type = AbsenceTypeFabricator::fabricate();
    $this->createJobLeaveEntitlement($type, 17);

    $calculation = new ContractEntitlementCalculation(new AbsencePeriod(), $this->contract, $type);

    $this->assertEquals(17, $calculation->getContractualEntitlement());
  }

  public function testNumberOfPublicHolidaysInEntitlementShouldBeZeroIfThereIsNoContractualEntitlement() {
    $calculation = new ContractEntitlementCalculation(new AbsencePeriod(), $this->contract, new AbsenceType());

    $this->assertEquals(0, $calculation->getNumberOfPublicHolidaysInEntitlement());
  }

  public function testNumberOfPublicHolidaysInEntitlementShouldBeZeroIfTheContractDoesntAllowThemToBeAdded() {
    $this->contract['period_start_date'] = '2016-03-10';
    $this->contract['period_end_date'] = '2016-09-23';

    $type = AbsenceTypeFabricator::fabricate();
    $allowPublicHolidays = false;
    $this->createJobLeaveEntitlement($type, 17, $allowPublicHolidays);

    //Is between contract dates but will not be included
    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('2016-03-11'))
    ]);

    $calculation = new ContractEntitlementCalculation(new AbsencePeriod(), $this->contract, $type);

    $this->assertEquals(0, $calculation->getNumberOfPublicHolidaysInEntitlement());
  }

  public function testNumberOfPublicHolidaysInEntitlementShouldBeZeroIfTheContractAllowsThemToBeAddedButTheresNoneBetweenTheContractDates() {
    $this->contract['period_start_date'] = '2016-03-10';
    $this->contract['period_end_date'] = '2016-09-23';

    $type = AbsenceTypeFabricator::fabricate();
    $allowPublicHolidays = true;
    $this->createJobLeaveEntitlement($type, 17, $allowPublicHolidays);

    //Before the start date. Will not be included
    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('2016-03-09'))
    ]);

    //After the end date. Will not be included
    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('2016-09-24'))
    ]);

    $calculation = new ContractEntitlementCalculation(new AbsencePeriod(), $this->contract, $type);

    $this->assertEquals(0, $calculation->getNumberOfPublicHolidaysInEntitlement());
  }

  public function testNumberOfPublicHolidaysInEntitlementShouldBeTheNumberOfPublicHolidaysBetweenTheContractDates() {
    $this->contract['period_start_date'] = '2016-03-10';
    $this->contract['period_end_date'] = '2016-09-23';

    $type = AbsenceTypeFabricator::fabricate();
    $allowPublicHolidays = true;
    $this->createJobLeaveEntitlement($type, 17, $allowPublicHolidays);

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('2016-03-14'))
    ]);

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('2016-08-23'))
    ]);

    $calculation = new ContractEntitlementCalculation(new AbsencePeriod(), $this->contract, $type);

    $this->assertEquals(2, $calculation->getNumberOfPublicHolidaysInEntitlement());
  }

  public function testPublicHolidaysInEntitlementShouldBeEmptyIfThereIsNoContractualEntitlement() {
    $calculation = new ContractEntitlementCalculation(new AbsencePeriod(), $this->contract, new AbsenceType());

    $this->assertEmpty($calculation->getPublicHolidaysInEntitlement());
  }

  public function testPublicHolidaysInEntitlementShouldBeEmptyIfTheContractDoesntAllowThemToBeAdded() {
    $this->contract['period_start_date'] = '2016-03-10';
    $this->contract['period_end_date'] = '2016-09-23';

    $type = AbsenceTypeFabricator::fabricate();
    $allowPublicHolidays = false;
    $this->createJobLeaveEntitlement($type, 17, $allowPublicHolidays);

    //Is between contract dates but will not be included
    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('2016-03-11'))
    ]);

    $calculation = new ContractEntitlementCalculation(new AbsencePeriod(), $this->contract, $type);

    $this->assertEmpty($calculation->getPublicHolidaysInEntitlement());
  }

  public function testPublicHolidaysInEntitlementShouldBeEmptyIfTheContractAllowsThemToBeAddedButTheresNoneBetweenTheContractDates() {
    $this->contract['period_start_date'] = '2016-03-10';
    $this->contract['period_end_date'] = '2016-09-23';

    $type = AbsenceTypeFabricator::fabricate();
    $allowPublicHolidays = true;
    $this->createJobLeaveEntitlement($type, 17, $allowPublicHolidays);

    //Before the start date. Will not be included
    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('2016-03-09'))
    ]);

    //After the end date. Will not be included
    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('2016-09-24'))
    ]);

    $calculation = new ContractEntitlementCalculation(new AbsencePeriod(), $this->contract, $type);

    $this->assertEmpty($calculation->getPublicHolidaysInEntitlement());
  }

  public function testPublicHolidaysInEntitlementShouldReturnThePublicHolidaysBetweenTheContractDates() {
    $this->contract['period_start_date'] = '2016-03-10';
    $this->contract['period_end_date'] = '2016-09-23';

    $type = AbsenceTypeFabricator::fabricate();
    $allowPublicHolidays = true;
    $this->createJobLeaveEntitlement($type, 17, $allowPublicHolidays);

    $publicHoliday1 = PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('2016-03-14'))
    ]);

    $publicHoliday2 = PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('2016-08-23'))
    ]);

    $calculation = new ContractEntitlementCalculation(new AbsencePeriod(), $this->contract, $type);

    $publicHolidays = $calculation->getPublicHolidaysInEntitlement();
    $this->assertCount(2, $publicHolidays);
    $this->assertEquals($publicHoliday1->title, $publicHolidays[0]->title);
    $this->assertEquals($publicHoliday2->title, $publicHolidays[1]->title);
  }

  public function testGetContractStartDateReturnsTheContractPeriodStartDate() {
    $this->contract['period_start_date'] = '2016-01-01';

    $calculation = new ContractEntitlementCalculation(new AbsencePeriod(), $this->contract, new Absencetype());
    $this->assertEquals($this->contract['period_start_date'], $calculation->getContractStartDate());
  }

  public function testGetContractEndDateReturnsTheContractPeriodEndDate() {
    $this->contract['period_end_date'] = '2016-10-01';

    $calculation = new ContractEntitlementCalculation(new AbsencePeriod(), $this->contract, new Absencetype());
    $this->assertEquals($this->contract['period_end_date'], $calculation->getContractEndDate());
  }

  private function createJobLeaveEntitlement($type, $leaveAmount, $addPublicHolidays = false) {
    CRM_Hrjobcontract_BAO_HRJobLeave::create([
      'jobcontract_id' => $this->contract['id'],
      'leave_type' => $type->id,
      'leave_amount' => $leaveAmount,
      'add_public_holidays' => $addPublicHolidays ? '1' : '0'
    ]);
  }

  private function getAbsencePeriodMock($settings) {
    $methodsToMock = array_keys($settings);

    $period = $this->getMockBuilder(AbsencePeriod::class)
                   ->setMethods($methodsToMock)
                   ->getMock();

    foreach($settings as $methodName => $methodSettings) {
      if(array_key_exists('expects', $methodSettings)) {
        $expects = $methodSettings['expects'];
      } else {
        $expects = $this->once();
      }
      $method = $period->expects($expects)
            ->method($methodName);

      if(array_key_exists('willReturn', $methodSettings)) {
        $method->will($this->returnValue($methodSettings['willReturn']));
      }

      if(array_key_exists('withParams', $methodSettings)) {
        call_user_func_array(array($method, 'with'), $methodSettings['withParams']);
      }
    }

    return $period;
  }
}
