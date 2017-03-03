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

  public function testGetActiveContractsDoesntIncludeContractsWithoutDetails() {
    $this->createContacts(1);
    $this->createJobContract($this->contacts[0]['id']);
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContracts();
    $this->assertEmpty($activeContracts);
  }

  public function testGetActiveContractsDoesntIncludeInactiveContracts() {
    $this->createContacts(1);
    $startDate = date('YmdHis', strtotime('-10 days'));
    $endDate = date('YmdHis', strtotime('-1 day'));
    $this->createJobContract($this->contacts[0]['id'], $startDate, $endDate);
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContracts();
    $this->assertEmpty($activeContracts);
  }

  public function testGetActiveContractsDoesntIncludeDeletedContracts()
  {
    $this->createContacts(1);
    $startDate = date('YmdHis');
    $endDate = date('YmdHis', strtotime('+10 days'));
    $contract = $this->createJobContract($this->contacts[0]['id'], $startDate, $endDate);

    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContracts();
    $this->assertCount(1, $activeContracts);
    $this->assertEquals($contract->id, $activeContracts[0]['id']);

    $this->deleteContract($contract->id);
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContracts();
    $this->assertEmpty($activeContracts);
  }

  public function testGetActiveContractsShouldIncludeActiveContracts()
  {
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContracts();
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

    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContracts();
    $this->assertCount(4, $activeContracts);
  }

  public function testGetActiveContractsCanReturnContractsActiveOnASpecificPeriod()
  {
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContracts();
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
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContracts();
    $this->assertEmpty($activeContracts);

    // The contracts overlaps only on the end day,
    // so, if we getActiveContracts with a maximum end date of end day - 1 day,
    // only the first contract should be returned
    $oneDayBeforeEndDateInFuture = date('Y-m-d', strtotime('+8 days'));
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContracts(
      $startDateInFuture,
      $oneDayBeforeEndDateInFuture
    );
    $this->assertCount(1, $activeContracts);
    $this->assertEquals($contract1->id, $activeContracts[0]['id']);

    // Now we are searching for active contracts with a start day equals to the
    // end date in the future (the only date where both contracts overlaps).
    // Since we are not passing an end date to getActiveContracts, that means
    // it will return contracts that are active on the exact given date.
    $activeContracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContracts(
      $endDateInFuture
    );
    $this->assertCount(2, $activeContracts);
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
