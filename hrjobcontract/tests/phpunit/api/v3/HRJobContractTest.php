<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

require_once 'tests/phpunit/fabricators/ContactFabricator.php';
require_once 'tests/phpunit/fabricators/HRJobContractFabricator.php';
require_once 'tests/phpunit/fabricators/HRJobContractRevisionFabricator.php';
require_once 'tests/phpunit/fabricators/HRJobHealthFabricator.php';
require_once 'tests/phpunit/fabricators/HRJobHourFabricator.php';
require_once 'tests/phpunit/fabricators/HRJobLeaveFabricator.php';
require_once 'tests/phpunit/fabricators/HRJobPayFabricator.php';
require_once 'tests/phpunit/fabricators/HRJobPensionFabricator.php';

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
   * @dataProvider invalidGetActiveContractsDateOperator
   */
  public function testOnGetActiveContractsTheStartDateOnlyAcceptsTheEqualOperator($operator) {
    civicrm_api3('HRJobContract', 'getactivecontracts', ['start_date' => [$operator => '2016-01-01']]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage The end date parameter can only be used with the = operator
   *
   * @dataProvider invalidGetActiveContractsDateOperator
   */
  public function testOnGetActiveContractsTheEndDateOnlyAcceptsTheEqualOperator($operator) {
    civicrm_api3('HRJobContract', 'getactivecontracts', ['end_date' => [$operator => '2016-01-01']]);
  }

  public function invalidGetActiveContractsDateOperator()
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
