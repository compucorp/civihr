<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Service_JobContract as JobContractService;

/**
 * Class CRM_HRLeaveAndAbsences_Service_JobContractTest
 *
 * The JobContract service is nothing but a wrapper for the JobContract API, so
 * all the tests here are just very basic and were created just to verify that
 * the proper API endpoints are being used and the expected values are returned.
 * More complete tests should be on the HRJobContract extension.
 *
 * Ideally, these tests would mock the API and just check if the methods have
 * been called, but currently it's not possible to mock api calls in CiviCRM.
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_JobContractTest extends BaseHeadlessTest {

  /**
   * @var array
   */
  private $contact;

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_JobContract
   */
  private $jobContractService;

  public function setUp() {
    $this->contact = ContactFabricator::fabricate();
    $this->jobContractService= new JobContractService();
  }

  public function testGetContractById() {
    $expectedContract = HRJobContractFabricator::fabricate([
      'contact_id' => $this->contact['id']
    ]);

    $contract = $this->jobContractService->getContractByID($expectedContract['id']);
    $this->assertEquals($expectedContract['id'], $contract['id']);
    $this->assertEquals($expectedContract['contact_id'], $contract['contact_id']);
  }

  public function testGetContractByIdWithoutPeriodEndDate() {
    $title = 'Title';
    $periodStartDate = '2016-01-01';

    $expectedContract = HRJobContractFabricator::fabricate([
      'contact_id' => $this->contact['id']
    ],
    [
      'period_start_date' => $periodStartDate,
      'title' => $title
    ]);

    $contract = $this->jobContractService->getContractByID($expectedContract['id']);
    $this->assertEquals($periodStartDate, $contract['period_start_date']);
    $this->assertEquals($title, $contract['title']);
    $this->assertNull($contract['period_end_date']);
  }

  public function testGetContractsForPeriod() {
    $contract1 = HRJobContractFabricator::fabricate([
      'contact_id' => $this->contact['id']
    ],
    [
      'period_start_date' => '2016-01-01',
      'period_end_date' => '2016-03-15',
    ]);

    $contract2 = HRJobContractFabricator::fabricate([
      'contact_id' => $this->contact['id']
    ],
      [
        'period_start_date' => '2016-04-02',
        'period_end_date' => '2016-11-30',
      ]);

    $contract3 = HRJobContractFabricator::fabricate([
      'contact_id' => $this->contact['id']
    ],
    [
      'period_start_date' => '2016-12-01'
    ]);

    $contracts = $this->jobContractService->getContractsForPeriod(
      new DateTime('2016-07-01'),
      new DateTime('2016-12-21')
    );

    $this->assertCount(2, $contracts);
    $this->assertEquals($contract2['id'], $contracts[0]['id']);
    $this->assertEquals($contract3['id'], $contracts[1]['id']);
  }

  public function testGetContractsForPeriodWithContactIDs() {
    $contact2 = ContactFabricator::fabricate();
    $contract1 = HRJobContractFabricator::fabricate([
      'contact_id' => $this->contact['id']
    ],
    [
      'period_start_date' => '2016-01-01',
      'period_end_date' => '2016-03-15',
    ]);

    $contract2 = HRJobContractFabricator::fabricate([
      'contact_id' => $this->contact['id']
    ],
    [
      'period_start_date' => '2016-04-02',
      'period_end_date' => '2016-11-30',
    ]);

    $contract3 = HRJobContractFabricator::fabricate([
      'contact_id' => $contact2['id']
    ],
    [
      'period_start_date' => '2016-12-01'
    ]);

    $contracts = $this->jobContractService->getContractsForPeriod(
      new DateTime('2016-07-01'),
      new DateTime('2016-12-21'),
      [$this->contact['id'], $contact2['id']]
    );

    $this->assertCount(2, $contracts);
    $this->assertEquals($contract2['id'], $contracts[0]['id']);
    $this->assertEquals($contract3['id'], $contracts[1]['id']);
  }
}
