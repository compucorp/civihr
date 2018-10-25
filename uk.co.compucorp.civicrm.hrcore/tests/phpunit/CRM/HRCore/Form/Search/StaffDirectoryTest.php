<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Form_Search_StaffDirectory as SearchDirectory;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_Hrjobroles_Test_Fabricator_HrJobRoles as HRJobRolesFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;
use CRM_HRCore_Test_Fabricator_RelationshipType as RelationshipTypeFabricator;
use CRM_HRCore_Test_Fabricator_Relationship as RelationshipFabricator;

/**
 * @group headless
 */
class CRM_HRCore_Form_Search_StaffDirectoryTest extends CRM_HRCore_Test_BaseHeadlessTest {

  private $relationshipType;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $tableName = CRM_Contact_BAO_Contact::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");
    $this->relationshipType = RelationshipTypeFabricator::fabricate(['is_active' => 1]);
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

  public function testCountReturnsTheTotalNumberOfStaffWithCurrentContractsForSelectStaffFilter() {
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

  public function testCountReturnsTheTotalNumberOfStaffWithPastContractsForSelectStaffFilter() {
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

  public function testCountReturnsTheTotalNumberOfStaffWithFutureContractsForSelectStaffFilter() {
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

  public function testCountReturnsTheCorrectNumberOfStaffWithSpecificJobContractDatesForSelectStaffFilter() {
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

  public function testCountReturnsZeroWhenNoStaffWithContractsOverlappingSpecificJobContractDatesForSelectStaffFilter() {
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

  public function testCountReturnsTheCorrectNumberOfStaffWithRelativeJobContractDateForSelectStaffFilter() {
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

  public function testOnlyJobRoleRelatedValuesLinkedToMostRecentJobContractForContactAreReturned() {
    $contactWorkEmail = 'contactemail@test.com';
    $contactWorkPhone = '209889940';
    $contactWorkPhoneExtension = 01;
    $contractTitle = 'Most Recent Contract';
    $contact1 = $this->fabricateContactWithWorkContactDetails(
      [],
      $contactWorkEmail,
      $contactWorkPhone,
      $contactWorkPhoneExtension
    );

    $manager = ContactFabricator::fabricate();

    //Most recent contract for contact 1
    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2017-01-01',
        'period_end_date' => '2017-12-31',
        'title' => $contractTitle
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-12-31',
        'title' => 'Past Contract'
      ]
    );

    $location1 = $this->createLocation('location1');
    $location2 = $this->createLocation('location2');
    $department1 = $this->createDepartment('department1');
    $department2 = $this->createDepartment('department2');

    //Assign the contact to a job role with access to location1
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'location' => $location1['value'],
      'department' => $department1['value']
    ]);

    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'location' => $location2['value'],
      'department' => $department2['value']
    ]);

    $this->createRelationShip($contact1, $manager);
    $formValues = [];
    $searchDirectory =  new SearchDirectory($formValues);
    $results = $this->extractColumnValues($searchDirectory->all(0, 10));

    $expectedResults = [
      [
        'display_name' => $contact1['display_name'],
        'work_phone' => "{$contactWorkPhone} + {$contactWorkPhoneExtension}",
        'work_email' => $contactWorkEmail,
        'manager' => $manager['display_name'],
        'location' => "{$location1['name']},{$location2['name']}",
        'department' => "{$department1['name']},{$department2['name']}",
        'job_title' => $contractTitle,
      ],
      [
        'display_name' => $manager['display_name'],
        'work_phone' => NULL,
        'work_email' => NULL,
        'manager' => NULL,
        'location' => NULL,
        'department' => NULL,
        'job_title' => NULL,
      ]
    ];

    $this->assertEquals($expectedResults, $results);
  }

  public function testOnlyActiveContactManagersAreReturnedForTheManagerColumn() {
    $contactWorkEmail = 'contactemail@test.com';
    $contactWorkPhone = '209889940';
    $contactWorkPhoneExtension = 01;
    $contractTitle = 'Most Recent Contract';
    $contact1 = $this->fabricateContactWithWorkContactDetails(
      [],
      $contactWorkEmail,
      $contactWorkPhone,
      $contactWorkPhoneExtension
    );

    $manager1 = ContactFabricator::fabricate(['first_name' => 'Manager2']);
    $manager2 = ContactFabricator::fabricate(['first_name' => 'Manager2']);
    $manager3 = ContactFabricator::fabricate(['first_name' => 'Manager3']);

    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2017-01-01',
        'period_end_date' => '2017-12-31',
        'title' => $contractTitle
      ]
    );

    $location1= $this->createLocation('location1');
    $department1 = $this->createDepartment('department1');

    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'location' => $location1['value'],
      'department' => $department1['value']
    ]);

    //active manger relationship
    $this->createRelationShip($contact1, $manager1);
    $this->createRelationShip($contact1, $manager3, '2016-01-01');
    //inactive relationship
    $this->createRelationShip($contact1, $manager2, '2016-01-01', '2016-12-31');

    $formValues = [];
    $searchDirectory =  new SearchDirectory($formValues);
    $results = $this->extractColumnValues($searchDirectory->all(0, 10));

    $expectedResults = [
      [
        'display_name' => $contact1['display_name'],
        'work_phone' => "{$contactWorkPhone} + {$contactWorkPhoneExtension}",
        'work_email' => $contactWorkEmail,
        'manager' => "{$manager1['display_name']},{$manager3['display_name']}",
        'location' => $location1['name'],
        'department' => $department1['name'],
        'job_title' => $contractTitle,
      ],
      [
        'display_name' => $manager1['display_name'],
        'work_phone' => NULL,
        'work_email' => NULL,
        'manager' => NULL,
        'location' => NULL,
        'department' => NULL,
        'job_title' => NULL,
      ],
      [
        'display_name' => $manager2['display_name'],
        'work_phone' => NULL,
        'work_email' => NULL,
        'manager' => NULL,
        'location' => NULL,
        'department' => NULL,
        'job_title' => NULL,
      ],
      [
        'display_name' => $manager3['display_name'],
        'work_phone' => NULL,
        'work_email' => NULL,
        'manager' => NULL,
        'location' => NULL,
        'department' => NULL,
        'job_title' => NULL,
      ]
    ];

    $this->assertEquals($expectedResults, $results);
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

  private function extractColumnValues($sql) {
    $result = CRM_Core_DAO::executeQuery($sql);
    $results = [];

    while ($result->fetch()) {
      $results[] = [
        'display_name' => $result->display_name,
        'work_phone' => $result->work_phone,
        'work_email' => $result->work_email,
        'manager' => $result->manager,
        'location' => $result->location,
        'department' => $result->department,
        'job_title' => $result->job_title
      ];
    }

    return $results;
  }

  private function createDepartment($departmentName) {
    $department = OptionValueFabricator::fabricate([
      'option_group_id' => 'hrjc_department',
      'name' => $departmentName,
      'value' => $departmentName,
      'label' => $departmentName,
    ]);

    return $department;
  }

  private function createLocation($locationName) {
    $location = OptionValueFabricator::fabricate([
      'option_group_id' => 'hrjc_location',
      'name' => $locationName,
      'value' => $locationName,
      'label' => $locationName,
    ]);

    return $location;
  }

  private function createRelationShip($contactA, $contactB, $startDate = NULL, $endDate = NULL, $isActive = 1) {
    RelationshipFabricator::fabricate([
      'contact_id_a' => $contactA['id'],
      'contact_id_b' => $contactB['id'],
      'relationship_type_id' => $this->relationshipType['id'],
      'start_date' => $startDate,
      'end_date' => $endDate,
      'is_active' => $isActive
    ]);
  }

  public static function fabricateContactWithWorkContactDetails($params = [], $email, $phone, $phone_ext = '') {
    $contact = ContactFabricator::fabricate($params);
    $workLocationId = CRM_Core_DAO::getFieldValue(
      'CRM_Core_DAO_LocationType',
      'Work',
      'id',
      'name'
    );

    civicrm_api3('Email', 'create', [
      'email' => $email,
      'contact_id' => $contact['id'],
      'is_primary' => 1,
      'location_type_id' => $workLocationId
    ]);

    civicrm_api3('Phone', 'create', [
      'contact_id' => $contact['id'],
      'phone' => $phone,
      'location_type_id' => $workLocationId,
      'phone_ext' => $phone_ext
    ]);

    return $contact;
  }
}
