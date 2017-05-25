<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContractRevision as HRJobContractRevisionFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobHealth as HRJobHealthFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobHour as HRJobHourFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobLeave as HRJobLeaveFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobPay as HRJobPayFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobPension as HRJobPensionFabricator;

/**
 * Class api_v3_HRJobContractTest
 *
 * @group headless
 */
class api_v3_HRJobContractTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface,
  TransactionalInterface {

  use HRJobContractTestTrait;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->installMe(__DIR__)
      ->apply();
  }

  function testFullDetailsResponse() {
    $contact = ContactFabricator::fabricate(['first_name' => 'chrollo', 'last_name' => 'lucilfer']);
    $contract = HRJobContractFabricator::fabricate(['contact_id' => $contact['id']], ['period_start_date' => '2015-01-01']);

    HRJobContractRevisionFabricator::fabricate(['jobcontract_id' => $contract['id']]);
    HRJobHealthFabricator::fabricate(['jobcontract_id' => $contract['id']]);
    HRJobHourFabricator::fabricate(['jobcontract_id' => $contract['id']]);
    HRJobLeaveFabricator::fabricate(['jobcontract_id' => $contract['id']]);
    HRJobPayFabricator::fabricate(['jobcontract_id' => $contract['id']]);
    HRJobPensionFabricator::fabricate(['jobcontract_id' => $contract['id']]);

    $fullDetails = civicrm_api3('HRJobContract', 'getfulldetails', array(
      'jobcontract_id' => $contract['id']
    ));

    foreach (['details', 'health', 'hour', 'leave', 'pay', 'pension'] as $entitiy ) {
      $this->assertArrayHasKey($entitiy, $fullDetails);
    }

    $this->assertInternalType("array", $fullDetails['leave']);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage The start date parameter can only be used with the = operator
   *
   * @dataProvider invalidGetContractsWithDetailsInPeriodDateOperator
   */
  public function testOnGetContractsWithDetailsInPeriodTheStartDateOnlyAcceptsTheEqualOperator($operator) {
    civicrm_api3('HRJobContract', 'getcontractswithdetailsinperiod', ['start_date' => [$operator => '2016-01-01']]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage The contact_id parameter only supports the IN operator
   *
   * @dataProvider invalidGetContractsWithDetailsInPeriodContactIDOperator
   */
  public function testGetContractsWithDetailsInPeriodTheContactIDOnlyAcceptsINOperator($operator) {
    civicrm_api3('HRJobContract', 'getcontractswithdetailsinperiod', ['contact_id' => [$operator => '1']]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage The end date parameter can only be used with the = operator
   *
   * @dataProvider invalidGetContractsWithDetailsInPeriodDateOperator
   */
  public function testOnGetContractsWithDetailsInPeriodTheEndDateOnlyAcceptsTheEqualOperator($operator) {
    civicrm_api3('HRJobContract', 'getcontractswithdetailsinperiod', ['end_date' => [$operator => '2016-01-01']]);
  }

  public function testGetContractsWithDetailsInPeriodCanReturnContractsForASingleContact() {
    $this->createContacts(2);

    // Contact 1 has 2 contracts
    $this->createJobContract(
      $this->contacts[0]['id'],
      '2016-01-01',
      '2016-03-10'
    );

    $this->createJobContract(
      $this->contacts[0]['id'],
      '2016-04-01',
      '2016-10-17'
    );

    // Contact 2 has 1 contract
    $this->createJobContract(
      $this->contacts[1]['id'],
      '2016-03-03'
    );

    $contact1Contracts = civicrm_api3('HRJobContract', 'getcontractswithdetailsinperiod', [
      'start_date' => '2016-01-01',
      'end_date' => '2016-12-31',
      'contact_id' => $this->contacts[0]['id']
    ]);

    $contact2Contracts = civicrm_api3('HRJobContract', 'getcontractswithdetailsinperiod', [
      'start_date' => '2016-01-01',
      'end_date' => '2016-12-31',
      'contact_id' => $this->contacts[1]['id']
    ]);


    $this->assertCount(2, $contact1Contracts['values']);
    $this->assertCount(1, $contact2Contracts['values']);
  }

  public function testGetContractsWithDetailsInPeriodCanReturnContractsForMultipleContact() {
    $this->createContacts(2);

    // Contact 1 has 2 contracts
    $this->createJobContract(
      $this->contacts[0]['id'],
      '2016-01-01',
      '2016-03-10'
    );

    $this->createJobContract(
      $this->contacts[0]['id'],
      '2016-04-01',
      '2016-10-17'
    );

    // Contact 2 has 1 contract
    $this->createJobContract(
      $this->contacts[1]['id'],
      '2016-03-03'
    );

    $allContracts = civicrm_api3('HRJobContract', 'getcontractswithdetailsinperiod', [
      'start_date' => '2016-01-01',
      'end_date' => '2016-12-31',
      'contact_id' => ['IN' => [$this->contacts[0]['id'], $this->contacts[1]['id']]]
    ]);

    $this->assertCount(3, $allContracts['values']);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage The start date parameter can only be used with the = operator
   *
   * @dataProvider invalidGetContractsWithDetailsInPeriodDateOperator
   */
  public function testOnGetContactsWithContractsInPeriodTheStartDateOnlyAcceptsTheEqualOperator($operator) {
    civicrm_api3('HRJobContract', 'getcontactswithcontractsinperiod', ['start_date' => [$operator => '2016-01-01']]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage The end date parameter can only be used with the = operator
   *
   * @dataProvider invalidGetContractsWithDetailsInPeriodDateOperator
   */
  public function testOnGetContactsWithContractsInPeriodTheEndDateOnlyAcceptsTheEqualOperator($operator) {
    civicrm_api3('HRJobContract', 'getcontactswithcontractsinperiod', ['end_date' => [$operator => '2016-01-01']]);
  }

  public function invalidGetContractsWithDetailsInPeriodDateOperator()
  {
    return [
      ['>'],
      ['>='],
      ['<='],
      ['<'],
      ['<>'],
      ['!='],
      ['BETWEEN'],
      ['NOT BETWEEN'],
      ['LIKE'],
      ['NOT LIKE'],
      ['IN'],
      ['NOT IN'],
      ['IS NULL'],
      ['IS NOT NULL'],
    ];
  }

  public function invalidGetContractsWithDetailsInPeriodContactIDOperator() {
    return [
      ['>'],
      ['>='],
      ['<='],
      ['<'],
      ['<>'],
      ['!='],
      ['BETWEEN'],
      ['NOT BETWEEN'],
      ['LIKE'],
      ['NOT LIKE'],
      ['NOT IN'],
      ['IS NULL'],
      ['IS NOT NULL'],
    ];
  }

}
