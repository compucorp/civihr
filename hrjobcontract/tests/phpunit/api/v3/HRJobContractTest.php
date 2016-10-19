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
