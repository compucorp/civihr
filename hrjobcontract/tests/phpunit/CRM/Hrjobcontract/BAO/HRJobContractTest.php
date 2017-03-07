<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;

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
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->installMe(__DIR__)
      ->apply();
  }

  public function testGetContractsWithDetailsInPeriodDoesntIncludeContractsWithoutDetails() {
    $this->createContacts(1);
    $this->createJobContract($this->contacts[0]['id']);
    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod();
    $this->assertEmpty($contracts);
  }

  public function testGetContractsWithDetailsInPeriodDoesntIncludeInactiveContracts() {
    $this->createContacts(1);
    $startDate = date('YmdHis', strtotime('-10 days'));
    $endDate = date('YmdHis', strtotime('-1 day'));
    $this->createJobContract($this->contacts[0]['id'], $startDate, $endDate);
    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod();
    $this->assertEmpty($contracts);
  }

  public function testGetContractsWithDetailsInPeriodDoesntIncludeDeletedContracts() {
    $this->createContacts(1);
    $startDate = date('YmdHis');
    $endDate = date('YmdHis', strtotime('+10 days'));
    $contract = $this->createJobContract($this->contacts[0]['id'], $startDate, $endDate);

    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod();
    $this->assertCount(1, $contracts);
    $this->assertEquals($contract->id, $contracts[0]['id']);

    $this->deleteContract($contract->id);
    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod();
    $this->assertEmpty($contracts);
  }

  public function testGetContractsWithDetailsInPeriodShouldIncludeActiveContracts() {
    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod();
    $this->assertEmpty($contracts);

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

    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod();
    $this->assertCount(4, $contracts);
  }

  public function testGetContractsWithDetailsInPeriodCanReturnContractsActiveOnASpecificPeriod() {
    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod();
    $this->assertEmpty($contracts);

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

    // Since both contracts start in the future, no contract should be returned
    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod();
    $this->assertEmpty($contracts);

    // The contracts overlaps only on the end day,
    // so, if we call getContractsWithDetailsInPeriod with a maximum end date of end day - 1 day,
    // only the first contract should be returned
    $oneDayBeforeEndDateInFuture = date('Y-m-d', strtotime('+8 days'));
    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod(
      $startDateInFuture,
      $oneDayBeforeEndDateInFuture
    );
    $this->assertCount(1, $contracts);
    $this->assertEquals($contract1->id, $contracts[0]['id']);

    // Now we are searching for contracts with a start day equals to the
    // end date in the future (the only date where both contracts overlaps).
    // Since we are not passing an end date to the method, that means
    // it will return contracts that are active on the exact given date.
    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod(
      $endDateInFuture
    );
    $this->assertCount(2, $contracts);
  }

  public function testGetContractsWithDetailsInPeriodCanReturnOnlyContractsOfASpecificContact() {
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

    $contact1Contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod(
      '2016-01-01', '2016-12-31', $this->contacts[0]['id']
    );

    $contact2Contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod(
      '2016-01-01', '2016-12-31', $this->contacts[1]['id']
    );

    $this->assertCount(2, $contact1Contracts);
    $this->assertEquals($contract1->id, $contact1Contracts[0]['id']);
    $this->assertEquals($startDateContract1, $contact1Contracts[0]['period_start_date']);
    $this->assertEquals($endDateContract1, $contact1Contracts[0]['period_end_date']);
    $this->assertEquals($contract2->id, $contact1Contracts[1]['id']);
    $this->assertEquals($startDateContract2, $contact1Contracts[1]['period_start_date']);
    $this->assertEquals($endDateContract2, $contact1Contracts[1]['period_end_date']);

    $this->assertCount(1, $contact2Contracts);
    $this->assertEquals($contract3->id, $contact2Contracts[0]['id']);
    $this->assertEquals($startDateContract3, $contact2Contracts[0]['period_start_date']);
    $this->assertNull($contact2Contracts[0]['period_end_date']);
  }

  public function testGetContractsWithDetailsInPeriodShouldReturnTheDetailsFromTheLatestDetailsRevision() {
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

    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod(
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

    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod(
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

    $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getContractsWithDetailsInPeriod(
      $startDate, $endDate, $this->contacts[0]['id']
    );

    $this->assertCount(1, $contracts);
    $this->assertEquals($startDate, $contracts[0]['period_start_date']);
    $this->assertEquals($endDate, $contracts[0]['period_end_date']);
  }

  public function testGetContactsWithContractsInPeriodShouldIncludeDisplayNameAndID() {
    $this->createContacts(2);

    $this->createJobContract(
      $this->contacts[0]['id'],
      date('Y-m-d', strtotime('-2 days')),
      null
    );

    $this->createJobContract(
      $this->contacts[1]['id'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+100 days'))
    );

    $contacts = CRM_Hrjobcontract_BAO_HRJobContract::getContactsWithContractsInPeriod();
    $this->assertCount(2, $contacts);
    // Since the results are ordered by display_name, it's reliable to
    // assert the order like this
    $this->assertEquals($this->contacts[0]['id'], $contacts[0]['id']);
    $this->assertEquals($this->contacts[0]['display_name'], $contacts[0]['display_name']);
    $this->assertEquals($this->contacts[1]['id'], $contacts[1]['id']);
    $this->assertEquals($this->contacts[1]['display_name'], $contacts[1]['display_name']);
  }

  public function testGetContactsWithContractsInPeriodShouldNotIncludeContactsWithDeletedContracts() {
    $this->createContacts(1);

    $contract = $this->createJobContract(
      $this->contacts[0]['id'],
      date('Y-m-d', strtotime('-2 days')),
      null
    );

    $contacts = CRM_Hrjobcontract_BAO_HRJobContract::getContactsWithContractsInPeriod();
    $this->assertCount(1, $contacts);

    $this->deleteContract($contract->id);

    $contacts = CRM_Hrjobcontract_BAO_HRJobContract::getContactsWithContractsInPeriod();
    $this->assertCount(0, $contacts);
  }

  public function testGetContactsWithContractsInPeriodShouldNotIncludeContactsWithContractsOutsideThePeriod() {
    $this->createContacts(2);

    // This will not be returned because it has ended one day ago
    $this->createJobContract(
      $this->contacts[0]['id'],
      date('Y-m-d', strtotime('-2 days')),
      date('Y-m-d', strtotime('-1 day'))
    );

    // This will not be returned because it will start only in 10 days
    $this->createJobContract(
      $this->contacts[0]['id'],
      date('Y-m-d', strtotime('+10 days')),
      null
    );

    $contacts = CRM_Hrjobcontract_BAO_HRJobContract::getContactsWithContractsInPeriod();
    $this->assertCount(0, $contacts);
  }

  public function testGetContactsWithContractsInPeriodCanReturnsContactsWithContractsWithinTheSpecifiedPeriod() {
    $this->createContacts(3);

    $this->createJobContract(
      $this->contacts[0]['id'],
      date('Y-m-d', strtotime('-10 days')),
      null
    );

    $this->createJobContract(
      $this->contacts[1]['id'],
      date('Y-m-d', strtotime('+5 days')),
      date('Y-m-d', strtotime('+10 days'))
    );

    $this->createJobContract(
      $this->contacts[2]['id'],
      date('Y-m-d', strtotime('+100 days')),
      date('Y-m-d', strtotime('+200 days'))
    );


    $contacts = CRM_Hrjobcontract_BAO_HRJobContract::getContactsWithContractsInPeriod(
      date('Y-m-d', strtotime('-11 days'))
    );
    // Should return 0 because there were no contracts
    // existed 11 days ago
    $this->assertCount(0, $contacts);

    $contacts = CRM_Hrjobcontract_BAO_HRJobContract::getContactsWithContractsInPeriod(
      date('Y-m-d'),
      date('Y-m-d', strtotime('+4 days'))
    );
    // Since the second contract starts only after 5 days,
    // this should return only 1 contact (from the first contract,
    // which started 10 days ago and never ends)
    $this->assertCount(1, $contacts);
    $this->assertEquals($this->contacts[0]['id'], $contacts[0]['id']);
    $this->assertEquals($this->contacts[0]['display_name'], $contacts[0]['display_name']);

    $contacts = CRM_Hrjobcontract_BAO_HRJobContract::getContactsWithContractsInPeriod(
      date('Y-m-d', strtotime('+7 days')),
      date('Y-m-d', strtotime('+50 days'))
    );
    // This should return 2. One from the first contract, which never ends
    // and another one for the second contract, even though the end date of the
    // filter is past the contract end date
    $this->assertCount(2, $contacts);
    $this->assertEquals($this->contacts[0]['id'], $contacts[0]['id']);
    $this->assertEquals($this->contacts[0]['display_name'], $contacts[0]['display_name']);
    $this->assertEquals($this->contacts[1]['id'], $contacts[1]['id']);
    $this->assertEquals($this->contacts[1]['display_name'], $contacts[1]['display_name']);

    $contacts = CRM_Hrjobcontract_BAO_HRJobContract::getContactsWithContractsInPeriod(
      date('Y-m-d', strtotime('+101 days')),
      date('Y-m-d', strtotime('+102 days'))
    );
    // This will 2: The contact from the first contract, which never ends, and
    // the contact for the last contract. The contact for the second contract
    // is not returned because it starts and ends before the filter start date
    $this->assertCount(2, $contacts);
    $this->assertEquals($this->contacts[0]['id'], $contacts[0]['id']);
    $this->assertEquals($this->contacts[0]['display_name'], $contacts[0]['display_name']);
    $this->assertEquals($this->contacts[2]['id'], $contacts[1]['id']);
    $this->assertEquals($this->contacts[2]['display_name'], $contacts[1]['display_name']);
  }

  public function testGetContactsWithContractsInPeriodReturnsTheContactOnceWhenItHasMoreThanOneContractDuringThePeriod() {
    $this->createContacts(1);

    $this->createJobContract(
      $this->contacts[0]['id'],
      date('Y-m-d'),
      date('Y-m-d', strtotime('+10 days'))
    );

    $this->createJobContract(
      $this->contacts[0]['id'],
      date('Y-m-d', strtotime('+20 days')),
      null
    );

    $contacts = CRM_Hrjobcontract_BAO_HRJobContract::getContactsWithContractsInPeriod(
      date('Y-m-d'),
      date('Y-m-d', strtotime('+50 days'))
    );
    // During the filter period, the contact has two contracts, but
    // the method should return the contact only once
    $this->assertCount(1, $contacts);
    $this->assertEquals($this->contacts[0]['id'], $contacts[0]['id']);
    $this->assertEquals($this->contacts[0]['display_name'], $contacts[0]['display_name']);
  }

  public function testGetCurrentContract() {
    $contactParams = array("first_name" => "chrollo", "last_name" => "lucilfer");
    $contactID =  $this->createContact($contactParams);

    $params = ['position' => 'spiders boss', 'title' => 'spiders boss'];
    $this->createJobContract($contactID, '2015-06-01', '2015-10-01', $params);

    $params['position'] = 'spiders boss2';
    $params['title'] = 'spiders boss2';
    $this->createJobContract($contactID, '2016-06-01', null, $params);

    $params['position'] = 'spiders boss3';
    $params['title'] = 'spiders boss3';
    $this->createJobContract($contactID, '2014-06-01', '2014-10-01', $params);

    $currentContract = CRM_Hrjobcontract_BAO_HRJobContract::getCurrentContract($contactID);
    $this->assertNotEquals(null, $currentContract);
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

  public function testLengthOfService() {
    $contact = array('first_name' => 'Timothy', 'last_name' => 'Dalton');
    $contactId = $this->createContact($contact);

    // Create first Job Contract starting 5 days ago witn no end date.
    $jobContract1StartDate = (new DateTime())->sub(new DateInterval('P5D'));
    $this->createJobContract($contactId, $jobContract1StartDate->format('Y-m-d'), null);
    $this->assertEquals(6, CRM_Hrjobcontract_BAO_HRJobContract::getLengthOfService($contactId));

    // Create second Job Contract in the past ending within 14 days of first
    // Job Contract start date.
    $jobContract2StartDate = (new DateTime())->sub(new DateInterval('P40D'));
    $jobContract2EndDate = (new DateTime())->sub(new DateInterval('P10D'));
    $this->createJobContract($contactId, $jobContract2StartDate->format('Y-m-d'), $jobContract2EndDate->format('Y-m-d'));
    $this->assertEquals(41, CRM_Hrjobcontract_BAO_HRJobContract::getLengthOfService($contactId));

    // Create third Job Contract in the past ending earlier than within 14 days
    // of second Job Contract start date (so it won't change Length of Service
    // value).
    $jobContract3StartDate = (new DateTime())->sub(new DateInterval('P70D'));
    $jobContract3EndDate = (new DateTime())->sub(new DateInterval('P60D'));
    $this->createJobContract($contactId, $jobContract3StartDate->format('Y-m-d'), $jobContract3EndDate->format('Y-m-d'));
    $this->assertEquals(41, CRM_Hrjobcontract_BAO_HRJobContract::getLengthOfService($contactId));

    // Check for valid format and values of getLengthOfServiceYmd() BAO method.
    // It's Length of Service value split into days, months and years as integers.
    // So it's basically an array containing of days, months and years between
    // today and today - length_of_service value. Hovewer we can't assert any
    // constant value here to compare because months have different length and
    // it may be different depending on date when the test is running. So we
    // are calculating expected values defining 41 days as it's our current
    // Length of Service.
    $today = new DateTime();
    $past = (new DateTime())->sub(new DateInterval('P41D'));
    $interval = $today->diff($past);
    $lengthOfServiceYmdExpected = array(
      'days' => (int)$interval->format('%d'),
      'months' => (int)$interval->format('%m'),
      'years' => (int)$interval->format('%y'),
    );
    $this->assertEquals($lengthOfServiceYmdExpected, CRM_Hrjobcontract_BAO_HRJobContract::getLengthOfServiceYmd($contactId));
  }

  public function testPermanentlyDeleteAllContracts() {
    $contact = ContactFabricator::fabricate(['first_name' => 'chrollo', 'last_name' => 'lucilfer']);

    // Create three Job Contracts.
    HRJobContractFabricator::fabricate(['contact_id' => $contact['id']], ['period_start_date' => '2014-06-01', 'period_end_date' => '2014-10-01']);
    HRJobContractFabricator::fabricate(['contact_id' => $contact['id']], ['period_start_date' => '2015-06-01', 'period_end_date' => '2015-10-01']);
    HRJobContractFabricator::fabricate(['contact_id' => $contact['id']], ['period_start_date' => '2016-06-01', 'period_end_date' => '2016-10-01']);

    // Check if there are three Job Contracts created.
    $jobContract = new CRM_Hrjobcontract_BAO_HRJobContract();
    $jobContract->contact_id = $contact['id'];
    $jobContract->find();
    $this->assertEquals(3, $jobContract->count());

    // Permanently delete the Job Contracts.
    CRM_Hrjobcontract_BAO_HRJobContract::deleteAllContractsPermanently($contact['id']);

    // Check if there is no Job Contracts.
    $jobContract->find();
    $this->assertEquals(0, $jobContract->count());
  }
}
