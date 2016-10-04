<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_Hrjobcontract_BAO_HRJobContractTest
 *
 * @group headless
 */
class CRM_Hrjobcontract_BAO_HRJobContractTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface,
  TransactionalInterface {

  use HRJobContractTestTrait;

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  public function testGetActiveContractsWithDetailsDoesntIncludeContractsWithoutDetails() {
    $this->createContacts(1);
    $this->createJobContract($this->contacts[0]['id']);
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails();
    $this->assertEmpty($activeContracts);
  }

  public function testGetActiveContractsWithDetailsDoesntIncludeInactiveContracts() {
    $this->createContacts(1);
    $startDate = date('YmdHis', strtotime('-10 days'));
    $endDate = date('YmdHis', strtotime('-1 day'));
    $this->createJobContract($this->contacts[0]['id'], $startDate, $endDate);
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails();
    $this->assertEmpty($activeContracts);
  }

  public function testGetActiveContractsWithDetailsDoesntIncludeDeletedContracts()
  {
    $this->createContacts(1);
    $startDate = date('YmdHis');
    $endDate = date('YmdHis', strtotime('+10 days'));
    $contract = $this->createJobContract($this->contacts[0]['id'], $startDate, $endDate);

    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails();
    $this->assertCount(1, $activeContracts);
    $this->assertEquals($contract->id, $activeContracts[0]['id']);

    $this->deleteContract($contract->id);
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails();
    $this->assertEmpty($activeContracts);
  }

  public function testGetActiveContractsWithDetailsShouldIncludeActiveContracts()
  {
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails();
    $this->assertEmpty($activeContracts);

    $this->createContacts(4);
    $this->createJobContract($this->contacts[0]['id'], date('Y-m-d'));
    $this->createJobContract(
      $this->contacts[1]['id'],
      date('Y-m-d', strtotime('-1 year'))
    );
    $this->createJobContract(
      $this->contacts[2]['id'],
      date('Y-m-d', strtotime('-5 months')),
      date('Y-m-d', strtotime('+5 months'))
    );
    $this->createJobContract(
      $this->contacts[3]['id'],
      date('Y-m-d', strtotime('-5 months')),
      date('Y-m-d', strtotime('now'))
    );

    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails();
    $this->assertCount(4, $activeContracts);
  }

  public function testGetActiveContractsWithDetailsCanReturnContractsActiveOnASpecificPeriod()
  {
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails();
    $this->assertEmpty($activeContracts);

    $this->createContacts(2);

    $startDateInFuture = date('Y-m-d', strtotime('+2 days'));
    $endDateInFuture = date('Y-m-d', strtotime('+9 days'));
    $contract1 = $this->createJobContract(
      $this->contacts[0]['id'],
      $startDateInFuture,
      $endDateInFuture
    );
    $this->createJobContract(
      $this->contacts[1]['id'],
      $endDateInFuture
    );

    // Since both contracts start in the future,
    // getActiveContracts without start date should return empty
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails();
    $this->assertEmpty($activeContracts);

    // The contracts overlaps only on the end day,
    // so, if we getActiveContracts with a maximum end date of end day - 1 day,
    // only the first contract should be returned
    $oneDayBeforeEndDateInFuture = date('Y-m-d', strtotime('+8 days'));
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails(
      $startDateInFuture,
      $oneDayBeforeEndDateInFuture
    );
    $this->assertCount(1, $activeContracts);
    $this->assertEquals($contract1->id, $activeContracts[0]['id']);

    // Now we are searching for active contracts with a start day equals to the
    // end date in the future (the only date where both contracts overlaps).
    // Since we are not passing an end date to getActiveContracts, that means
    // it will return contracts that are active on the exact given date.
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails(
      $endDateInFuture
    );
    $this->assertCount(2, $activeContracts);
  }

  public function testGetActiveContractsWithDetailsCanReturnOnlyContractsOfASpecificContact() {
    $this->createContacts(2);

    $startDateContract1 = '2016-01-01';
    $endDateContract1 = '2016-03-10';
    // Contact 1 has 2 contracts
    $contract1 = $this->createJobContract(
      $this->contacts[0]['id'],
      $startDateContract1,
      $endDateContract1
    );

    $startDateContract2 = '2016-04-01';
    $endDateContract2 = '2016-10-17';
    $contract2 = $this->createJobContract(
      $this->contacts[0]['id'],
      $startDateContract2,
      $endDateContract2
    );

    $startDateContract3 = '2016-03-03';
    // Contact 2 has 1 contract
    $contract3 = $this->createJobContract(
      $this->contacts[1]['id'],
      $startDateContract3
    );

    $contact1ActiveContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails(
      '2016-01-01', '2016-12-31', $this->contacts[0]['id']
    );

    $contact2ActiveContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails(
      '2016-01-01', '2016-12-31', $this->contacts[1]['id']
    );

    $this->assertCount(2, $contact1ActiveContracts);
    $this->assertEquals($contract1->id, $contact1ActiveContracts[0]['id']);
    $this->assertEquals($startDateContract1, $contact1ActiveContracts[0]['period_start_date']);
    $this->assertEquals($endDateContract1, $contact1ActiveContracts[0]['period_end_date']);
    $this->assertEquals($contract2->id, $contact1ActiveContracts[1]['id']);
    $this->assertEquals($startDateContract2, $contact1ActiveContracts[1]['period_start_date']);
    $this->assertEquals($endDateContract2, $contact1ActiveContracts[1]['period_end_date']);

    $this->assertCount(1, $contact2ActiveContracts);
    $this->assertEquals($contract3->id, $contact2ActiveContracts[0]['id']);
    $this->assertEquals($startDateContract3, $contact2ActiveContracts[0]['period_start_date']);
    $this->assertNull($contact2ActiveContracts[0]['period_end_date']);
  }

  public function testGetActiveContractWithDetailsShouldReturnTheDetailsFromTheLatestDetailsRevision() {
    $this->createContacts(1);

    $startDate = '2016-02-01';
    $endDate = '2016-03-10';

    $contract1 = $this->createJobContract(
      $this->contacts[0]['id'],
      $startDate,
      $endDate
    );

    //Change the start date
    $startDate = '2016-02-01';
    CRM_Hrjobcontract_BAO_HRJobDetails::create([
      'jobcontract_id' => $contract1->id,
      'period_start_date' => date('YmdHis', strtotime($startDate)),
      'period_end_date' => date('YmdHis', strtotime($endDate))
    ]);

    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails(
      $startDate, $endDate, $this->contacts[0]['id']
    );

    $this->assertCount(1, $contracts);
    $this->assertEquals($startDate, $contracts[0]['period_start_date']);
    $this->assertEquals($endDate, $contracts[0]['period_end_date']);

    //Change both dates
    $startDate = '2016-01-15';
    $endDate = '2016-10-27';
    CRM_Hrjobcontract_BAO_HRJobDetails::create([
      'jobcontract_id' => $contract1->id,
      'period_start_date' => date('YmdHis', strtotime($startDate)),
      'period_end_date' => date('YmdHis', strtotime($endDate))
    ]);

    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails(
      $startDate, $endDate, $this->contacts[0]['id']
    );

    $this->assertCount(1, $contracts);
    $this->assertEquals($startDate, $contracts[0]['period_start_date']);
    $this->assertEquals($endDate, $contracts[0]['period_end_date']);

    // Adding a new Job Leave will result in new revision,
    // but we should still get the latest details
    CRM_Hrjobcontract_BAO_HRJobLeave::create([
      'jobcontract_id' => $contract1->id,
      'leave_type' => 1,
      'leave_amount' => 10,
      'add_public_holidays' => 0
    ]);

    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContractsWithDetails(
      $startDate, $endDate, $this->contacts[0]['id']
    );

    $this->assertCount(1, $contracts);
    $this->assertEquals($startDate, $contracts[0]['period_start_date']);
    $this->assertEquals($endDate, $contracts[0]['period_end_date']);
  }


  public function testGetCurrentContract() {
    $contactParams = array("first_name" => "chrollo", "last_name" => "lucilfer");
    $contactID =  $this->createContact($contactParams);

    $params = array(
      'position' => 'spiders boss',
      'title' => 'spiders boss');
    $this->createJobContract($contactID, '2015-06-01', '2015-10-01', $params);

    $params = array(
      'position' => 'spiders boss2',
      'title' => 'spiders boss2');
    $this->createJobContract($contactID, '2016-06-01', null, $params);

    $params = array(
      'position' => 'spiders boss3',
      'title' => 'spiders boss3');
    $this->createJobContract($contactID, '2014-06-01', '2014-10-01', $params);

    $currentContract = CRM_Hrjobcontract_BAO_HRJobContract::getCurrentContract($contactID);
    $this->assertEquals('spiders boss2', $currentContract->title);
  }

  public function testGetStaffCount() {
    // create contacts
    $contact1 = array('first_name'=>'walter', 'last_name'=>'white');
    $contactID1 = $this->createContact($contact1);
    $contact2 = array('first_name'=>'walter1', 'last_name'=>'white1');
    $contactID2 = $this->createContact($contact2);
    $contact3 = array('first_name'=>'walter2', 'last_name'=>'white2');
    $contactID3 = $this->createContact($contact3);
    $contact4 = array('first_name'=>'walter3', 'last_name'=>'white3');
    $contactID4 = $this->createContact($contact4);

    // create contracts
    $this->createJobContract($contactID1, date('Y-m-d', strtotime('-14 days')));
    $this->createJobContract($contactID2, date('Y-m-d', strtotime('-5 days')));
    $this->createJobContract($contactID3, date('Y-m-d', strtotime('+3 years')));

    $this->assertEquals(2, CRM_Hrjobcontract_BAO_HRJobContract::getStaffCount());
  }
}
