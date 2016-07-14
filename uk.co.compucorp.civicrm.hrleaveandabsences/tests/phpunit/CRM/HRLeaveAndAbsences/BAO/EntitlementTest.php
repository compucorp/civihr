<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRLeaveAndAbsences_BAO_Entitlement as Entitlement;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_BroughtForward as BroughtForward;
use CRM_Hrjobcontract_BAO_HRJobContract as JobContract;
use CRM_HRLeaveAndAbsences_EntitlementCalculation as EntitlementCalculation;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_EntitlementTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_EntitlementTest extends PHPUnit_Framework_TestCase implements
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
  public function testThereCannotBeMoreThanOneEntitlementForTheSameSetOfAbsenceTypeAbsencePeriodAndContract() {
    $type = $this->createAbsenceType();

    $period = $this->createAbsencePeriod();

    $contract = $this->createJobContract();

    Entitlement::create([
      'period_id' => $period->id,
      'type_id' => $type->id,
      'contract_id' => $contract->id,
      'proposed_entitlement' => 20,
      'brought_forward_days' => 0,
      'pro_rata' => 0
    ]);

    Entitlement::create([
      'period_id' => $period->id,
      'type_id' => $type->id,
      'contract_id' => $contract->id,
      'proposed_entitlement' => 30,
      'brought_forward_days' => 4,
      'pro_rata' => 0
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException
   * @expectedExceptionMessage The author of the comment cannot be null
   */
  public function testCommentsShouldHaveAuthor()
  {
    Entitlement::create([
      'comment' => 'Lorem ipsum dolor sit....',
      'comment_updated_at' => date('YmdHis')
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException
   * @expectedExceptionMessage The date of the comment cannot be null
   */
  public function testCommentsShouldHaveDate()
  {
    Entitlement::create([
      'comment' => 'Lorem ipsum dolor sit....',
      'comment_author_id' => 2
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException
   * @expectedExceptionMessage The date of the comment should be null if the comment is empty
   */
  public function testEmptyCommentsShouldNotHaveDate()
  {
    Entitlement::create([
      'comment_updated_at' => date('YmdHis')
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException
   * @expectedExceptionMessage The author of the comment should be null if the comment is empty
   */
  public function testEmptyCommentsShouldNotHaveAuthor()
  {
    Entitlement::create([
      'comment_author_id' => 2
    ]);
  }

  public function testGetContractEntitlementForPeriod()
  {
    $type = $this->createAbsenceType();

    $period1 = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day'))
    ]);

    $period2 = AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis', strtotime('+2 days')),
      'end_date' => date('YmdHis', strtotime('+3 days'))
    ]);

    $contract = $this->createJobContract();

    Entitlement::create([
      'period_id' => $period1->id,
      'type_id' => $type->id,
      'contract_id' => $contract->id,
      'proposed_entitlement' => 20,
      'pro_rata' => 20
    ]);

    Entitlement::create([
      'period_id' => $period2->id,
      'type_id' => $type->id,
      'contract_id' => $contract->id,
      'proposed_entitlement' => 15,
      'pro_rata' => 0
    ]);

    $entitlementPeriod1 = Entitlement::getContractEntitlementForPeriod(
      $contract->id,
      $period1->id,
      $type->id
    );

    $this->assertEquals(20, $entitlementPeriod1->proposed_entitlement);
    $this->assertEquals(20, $entitlementPeriod1->pro_rata);

    $entitlementPeriod2 = Entitlement::getContractEntitlementForPeriod(
      $contract->id,
      $period2->id,
      $type->id
    );

    $this->assertEquals(15, $entitlementPeriod2->proposed_entitlement);
    $this->assertEquals(0, $entitlementPeriod2->pro_rata);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage You must inform the Contract ID
   */
  public function testContractIdIsRequiredForGetContractEntitlementForPeriod()
  {
    CRM_HRLeaveAndAbsences_BAO_Entitlement::getContractEntitlementForPeriod(null, 10, 11);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage You must inform the AbsencePeriod ID
   */
  public function testAbsencePeriodIdIsRequiredForGetContractEntitlementForPeriod()
  {
    CRM_HRLeaveAndAbsences_BAO_Entitlement::getContractEntitlementForPeriod(10, null, 11);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage You must inform the AbsenceType ID
   */
  public function testAbsenceTypeIdIsRequiredForGetContractEntitlementForPeriod()
  {
    CRM_HRLeaveAndAbsences_BAO_Entitlement::getContractEntitlementForPeriod(10, 15, NULL);
  }

  /**
   * @TODO include tests with leave requests, which are not yet implemented
   */
  public function testBalanceShouldNotIncludeExpiredBroughtForward()
  {
    $type = $this->createAbsenceType();

    $period = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+10 days'))
    ]);

    $contract = $this->createJobContract();

    $entitlement = Entitlement::create([
      'period_id' => $period->id,
      'type_id' => $type->id,
      'contract_id' => $contract->id,
      'proposed_entitlement' => 15,
      'pro_rata' => 15
    ]);

    // Any brought forward with a negative balance is
    // considered expired
    BroughtForward::create([
      'entitlement_id' => $entitlement->id,
      'expiration_date' => date('YmdHis'),
      'balance' => -4
    ]);

    $this->assertEquals(11, $entitlement->getBalance());
  }

  public function testBalanceShouldIncludeNonExpiredBroughtForward()
  {
    $type = $this->createAbsenceType();

    $period = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+10 days'))
    ]);

    $contract = $this->createJobContract();

    $entitlement = Entitlement::create([
      'period_id' => $period->id,
      'type_id' => $type->id,
      'contract_id' => $contract->id,
      'proposed_entitlement' => 15,
      'pro_rata' => 5
    ]);

    BroughtForward::create([
      'entitlement_id' => $entitlement->id,
      'expiration_date' => date('YmdHis'),
      'balance' => 10
    ]);

    $this->assertEquals(15, $entitlement->getBalance());
  }

  public function testBalanceShouldIncludeBroughtForwardThatNeverExpires()
  {
    $type = $this->createAbsenceType();

    $period = AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+10 days'))
    ]);

    $contract = $this->createJobContract();

    $entitlement = Entitlement::create([
      'period_id' => $period->id,
      'type_id' => $type->id,
      'contract_id' => $contract->id,
      'proposed_entitlement' => 15,
      'pro_rata' => 10
    ]);

    // A Brought Forward without expiration date
    // will never expire
    BroughtForward::create([
      'entitlement_id' => $entitlement->id,
      'expiration_date' => null,
      'balance' => 5
    ]);

    $this->assertEquals(15, $entitlement->getBalance());
  }

  public function testCanSaveAnEntitlementFromAnEntitlementCalculation()
  {
    $type = $this->createAbsenceType();
    $period = $this->createAbsencePeriodForEntitlementCalculation();
    $contract = $this->createJobContractForEntitlementCalculation();

    $entitlement = Entitlement::getContractEntitlementForPeriod(
      $contract['id'],
      $period->id,
      $type->id
    );
    $this->assertNull($entitlement);

    $broughtForward = 15;
    $proRata = 10;
    $proposedEntitlement = 25;
    $calculation = $this->getEntitlementCalculationMock(
      $period,
      $contract,
      $type,
      $broughtForward,
      $proRata,
      $proposedEntitlement
    );

    Entitlement::saveFromCalculation($calculation);

    $entitlement = Entitlement::getContractEntitlementForPeriod(
      $contract['id'],
      $period->id,
      $type->id
    );

    $this->assertNotNull($entitlement);
    $this->assertEquals($period->id, $entitlement->period_id);
    $this->assertEquals($type->id, $entitlement->type_id);
    $this->assertEquals($contract['id'], $entitlement->contract_id);
    $this->assertEquals(10, $entitlement->pro_rata);
    $this->assertEquals(25, $entitlement->proposed_entitlement);
    $this->assertEquals($broughtForward, $entitlement->getBroughtForwardBalance());
  }

  public function testSaveFromEntitlementCalculationWillReplaceExistingEntitlement()
  {
    $type = $this->createAbsenceType();
    $period = $this->createAbsencePeriodForEntitlementCalculation();
    $contract = $this->createJobContractForEntitlementCalculation();

    $entitlement = Entitlement::create([
      'period_id' => $period->id,
      'type_id' => $type->id,
      'contract_id' => $contract['id'],
      'pro_rata' => 20,
      'proposed_entitlement' => 23.5,
    ]);

    $this->assertNotNull($entitlement->id);

    $broughtForward = 3;
    $proRata = 12;
    $proposedEntitlement = 24.5;
    $calculation = $this->getEntitlementCalculationMock(
      $period,
      $contract,
      $type,
      $broughtForward,
      $proRata,
      $proposedEntitlement
    );

    Entitlement::saveFromCalculation($calculation);

    $entitlement = Entitlement::getContractEntitlementForPeriod(
      $contract['id'],
      $period->id,
      $type->id
    );

    $this->assertNotNull($entitlement);
    $this->assertEquals($period->id, $entitlement->period_id);
    $this->assertEquals($type->id, $entitlement->type_id);
    $this->assertEquals($contract['id'], $entitlement->contract_id);
    $this->assertEquals($broughtForward, $entitlement->getBroughtForwardBalance());
    $this->assertEquals($proRata, $entitlement->pro_rata);
    $this->assertEquals($proposedEntitlement, $entitlement->proposed_entitlement);
  }

  public function testSaveFromEntitlementCalculationCanSaveOverriddenValues()
  {
    $type = $this->createAbsenceType();
    $period = $this->createAbsencePeriodForEntitlementCalculation();
    $contract = $this->createJobContractForEntitlementCalculation();

    $calculation = $this->getEntitlementCalculationMock(
      $period,
      $contract,
      $type
    );

    $overriddenEntitlement = 15;
    Entitlement::saveFromCalculation(
      $calculation,
      $overriddenEntitlement
    );

    $entitlement = Entitlement::getContractEntitlementForPeriod(
      $contract['id'],
      $period->id,
      $type->id
    );

    $this->assertNotNull($entitlement);
    $this->assertEquals($period->id, $entitlement->period_id);
    $this->assertEquals($type->id, $entitlement->type_id);
    $this->assertEquals($contract['id'], $entitlement->contract_id);
    $this->assertEquals(0, $entitlement->getBroughtForwardBalance());
    $this->assertEquals(0, $entitlement->pro_rata);
    $this->assertEquals($overriddenEntitlement, $entitlement->proposed_entitlement);
    $this->assertEquals(1, $entitlement->overridden);
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

  /**
   * Mock the calculation, as we only need to test
   * if the Entitlement BAO can create an Entitlement from a calculation
   * instance
   *
   * @param $period
   * @param $contract
   * @param $type
   * @param int $broughtForward
   * @param int $proRata
   * @param int $proposedEntitlement
   *
   * @return mixed
   * @internal param int $broughForward
   */
  private function getEntitlementCalculationMock(
    $period,
    $contract,
    $type,
    $broughtForward = 0,
    $proRata = 0,
    $proposedEntitlement = 0
  ) {
    $calculation = $this->getMockBuilder(EntitlementCalculation::class)
                        ->setConstructorArgs([$period, $contract, $type])
                        ->setMethods([
                          'getBroughtForward',
                          'getProRata',
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
                ->method('getProposedEntitlement')
                ->will($this->returnValue($proposedEntitlement));

    return $calculation;
  }

  private function createAbsencePeriodForEntitlementCalculation() {
    $period = $this->createAbsencePeriod();
    //EntitlementCalculation needs the period dates in Y-m-d format
    $period->start_date = date('Y-m-d', strtotime($period->start_date));
    $period->end_date   = date('Y-m-d', strtotime($period->end_date));
    return $period;
  }

  private function createJobContractForEntitlementCalculation() {
    $contract = $this->createJobContract();
    // EntitlementCalculation expects the job contract as an array
    $contract = [
      'id'         => $contract->id,
      'contact_id' => $contract->contact_id
    ];
    return $contract;
  }
}
