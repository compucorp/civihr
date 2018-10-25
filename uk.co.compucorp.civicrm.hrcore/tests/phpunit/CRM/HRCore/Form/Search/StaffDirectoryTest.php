<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Form_Search_StaffDirectory as SearchDirectory;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;

/**
 * @group headless
 */
class CRM_HRCore_Form_Search_StaffDirectoryTest extends CRM_HRCore_Test_BaseHeadlessTest {

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $tableName = CRM_Contact_BAO_Contact::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");
  }

  public function testCountReturnsTheTotalNumberOfStaff() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    $formValues = [];
    $searchDirectory =  new SearchDirectory($formValues);
    $this->assertEquals(2, $searchDirectory->count());

    //verify contact ids
    $contactIds = $this->extractContactIds($searchDirectory->contactIDs());
    $this->assertEquals($contactIds, [$contact1['id'], $contact2['id']]);
  }

  public function testCountReturnsTheTotalNumberOfStaffWithCurrentContracts() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();
    $contact3 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-12-31'
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      ['period_start_date' => '2018-01-01']
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact3['id']],
      [
        'period_start_date' => '2018-01-01',
        'period_end_date' => date('Y-m-d', strtotime('+1 year'))
      ]
    );

    $formValues = ['select_staff' => 'current'];
    $searchDirectory =  new SearchDirectory($formValues);

    //Contact2 and contact3 have current contracts
    $this->assertEquals(2, $searchDirectory->count());

    //verify contact ids
    $contactIds = $this->extractContactIds($searchDirectory->contactIDs());
    $this->assertEquals($contactIds, [$contact2['id'], $contact3['id']]);
  }

  public function testCountReturnsTheTotalNumberOfStaffWithPastContracts() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-12-31'
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      ['period_start_date' => '2018-01-01']
    );

    $formValues = ['select_staff' => 'past'];
    $searchDirectory =  new SearchDirectory($formValues);

    //Contact1 has past contract
    $this->assertEquals(1, $searchDirectory->count());

    //verify contact ids
    $contactIds = $this->extractContactIds($searchDirectory->contactIDs()) ;
    $this->assertEquals($contactIds, [$contact1['id']]);
  }

  public function testCountReturnsTheTotalNumberOfStaffWithFutureContracts() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => date('Y-m-d', strtotime('+1 day'))]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      ['period_start_date' => date('Y-m-d')]
    );

    $formValues = ['select_staff' => 'future'];
    $searchDirectory =  new SearchDirectory($formValues);

    //Contact1 has future contract
    $this->assertEquals(1, $searchDirectory->count());

    //verify contact ids
    $contactIds = $this->extractContactIds($searchDirectory->contactIDs()) ;
    $this->assertEquals($contactIds, [$contact1['id']]);
  }

  public function testCountReturnsTheCorrectNumberOfStaffWithSpecificJobContractDates() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-12-31'
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      ['period_start_date' => '2018-01-01']
    );

    $formValues = [
      'select_staff' => 'choose_date',
      'contract_start_date' => '2016-04-01',
      'contract_end_date' => '2016-05-01'
    ];
    $searchDirectory =  new SearchDirectory($formValues);

    //only Contact1 has contract dates overlapping selected dates
    $this->assertEquals(1, $searchDirectory->count());

    //verify contact ids
    $contactIds = $this->extractContactIds($searchDirectory->contactIDs()) ;
    $this->assertEquals($contactIds, [$contact1['id']]);
  }

  public function testCountReturnsZeroWhenNoStaffWithContractsOverlappingSpecificJobContractDates() {
    $contact1 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2016-05-02',
        'period_end_date' => '2016-12-31'
      ]
    );

    $formValues = [
      'select_staff' => 'choose_date',
      'contract_start_date' => '2016-04-01',
      'contract_end_date' => '2016-05-01'
    ];
    $searchDirectory =  new SearchDirectory($formValues);

    //No staff with contract dates overlapping the contract dates selected
    $this->assertEquals(0, $searchDirectory->count());
  }

  public function testCountReturnsTheCorrectNumberOfStaffWithRelativeJobContractDate() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-12-31'
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      ['period_start_date' => '2018-01-01']
    );

    $formValues = ['select_staff' => 'this.day'];
    $searchDirectory =  new SearchDirectory($formValues);

    //only Contact2 has contract dates overlapping today
    $this->assertEquals(1, $searchDirectory->count());

    //verify contact ids
    $contactIds = $this->extractContactIds($searchDirectory->contactIDs()) ;
    $this->assertEquals($contactIds, [$contact2['id']]);
  }

  private function extractContactIds($sql) {
    $result = CRM_Core_DAO::executeQuery($sql);
    $contactId = [];
    while ($result->fetch()) {
      $contactId[] = $result->contact_id;
    }

    sort($contactId);

    return $contactId;
  }
}
