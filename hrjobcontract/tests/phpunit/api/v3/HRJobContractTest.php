<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

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
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage The start date parameter can only be used with the = operator
   *
   * @dataProvider invalidGetActiveContractsWithDetailsDateOperator
   */
  public function testOnGetActiveContractsWithDetailsTheStartDateOnlyAcceptsTheEqualOperator($operator) {
    civicrm_api3('HRJobContract', 'getactivecontractswithdetails', ['start_date' => [$operator => '2016-01-01']]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage The end date parameter can only be used with the = operator
   *
   * @dataProvider invalidGetActiveContractsWithDetailsDateOperator
   */
  public function testOnGetActiveContractsWithDetailsTheEndDateOnlyAcceptsTheEqualOperator($operator) {
    civicrm_api3('HRJobContract', 'getactivecontractswithdetails', ['end_date' => [$operator => '2016-01-01']]);
  }

  public function testGetActiveContractsWithDetailsCanReturnContractsOnlyForASingleContact() {
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

    $contact1ActiveContracts = civicrm_api3('HRJobContract', 'getactivecontractswithdetails', [
      'start_date' => '2016-01-01',
      'end_date' => '2016-12-31',
      'contact_id' => $this->contacts[0]['id']
    ]);

    $contact2ActiveContracts = civicrm_api3('HRJobContract', 'getactivecontractswithdetails', [
      'start_date' => '2016-01-01',
      'end_date' => '2016-12-31',
      'contact_id' => $this->contacts[1]['id']
    ]);


    $this->assertCount(2, $contact1ActiveContracts['values']);
    $this->assertCount(1, $contact2ActiveContracts['values']);
  }

  public function invalidGetActiveContractsWithDetailsDateOperator()
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

}
