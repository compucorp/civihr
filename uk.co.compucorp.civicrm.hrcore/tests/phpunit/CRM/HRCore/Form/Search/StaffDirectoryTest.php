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
    $searchDirectory = new SearchDirectory($formValues);
    $this->assertEquals(2, $searchDirectory->count());

    //verify contact ids
    $contactIds = $this->extractContactIds($searchDirectory->contactIDs());
    $this->assertEquals($contactIds, [$contact1['id'], $contact2['id']]);
  }

  public function testSelectStaffFilterCanFilterOnlyCurrentStaff() {
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
    $searchDirectory = new SearchDirectory($formValues);

    //Contact2 and contact3 have current contracts
    $this->assertEquals(2, $searchDirectory->count());

    //verify contact ids
    $contactIds = $this->extractContactIds($searchDirectory->contactIDs());
    $this->assertEquals($contactIds, [$contact2['id'], $contact3['id']]);
  }

  public function testSelectStaffFilterCanFilterOnlyPastStaff() {
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
    $searchDirectory = new SearchDirectory($formValues);

    //Contact1 has past contract
    $this->assertEquals(1, $searchDirectory->count());

    //verify contact ids
    $contactIds = $this->extractContactIds($searchDirectory->contactIDs()) ;
    $this->assertEquals($contactIds, [$contact1['id']]);
  }

  public function testSelectStaffFilterCanFilterOnlyFutureStaff() {
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
    $searchDirectory = new SearchDirectory($formValues);

    //Contact1 has future contract
    $this->assertEquals(1, $searchDirectory->count());

    //verify contact ids
    $contactIds = $this->extractContactIds($searchDirectory->contactIDs()) ;
    $this->assertEquals($contactIds, [$contact1['id']]);
  }

  public function testSelectStaffFilterCanFilterStaffWithSpecificJobContractDates() {
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
      'contract_start_date_relative' => 0,
      'contract_end_date_relative' => 0,
      'contract_start_date_low' => '2016-01-01',
      'contract_start_date_high' => '2016-01-05',
      'contract_end_date_low' => '2016-12-30',
      'contract_end_date_high' => '2016-12-31'
    ];
    $searchDirectory = new SearchDirectory($formValues);

    //only Contact1 has contract start dates between the given contract start low and high dates
    // And contract end date between the given contract end low and high dates.
    $this->assertEquals(1, $searchDirectory->count());

    //verify contact ids
    $contactIds = $this->extractContactIds($searchDirectory->contactIDs()) ;
    $this->assertEquals($contactIds, [$contact1['id']]);
  }

  public function testSelectStaffDoesNotReturnResultsWhenNoStaffWithContractsOverlappingSpecificJobContractDates() {
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
      'contract_start_date_relative' => 0,
      'contract_end_date_relative' => 0,
      'contract_start_date_low' => '2017-01-01',
      'contract_start_date_high' => '2017-01-05',
      'contract_end_date_low' => '2018-12-30',
      'contract_end_date_high' => '2018-12-31'
    ];
    $searchDirectory = new SearchDirectory($formValues);

    //No staff with contract start dates between the given contract start low and high dates
    //and contract end dates between the given contract end low and high dates.
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
      ['period_start_date' => date('Y-m-d')]
    );

    $formValues = [
      'select_staff' => 'choose_date',
      'contract_start_date_relative' => 'this.year',
      'contract_end_date_relative' => 'this.year',
      'contract_start_date_low' => '',
      'contract_start_date_high' => '',
      'contract_end_date_low' => '',
      'contract_end_date_high' => ''
    ];

    $searchDirectory = new SearchDirectory($formValues);

    //only Contact2 has contract start dates within this year
    //and also end date within this year(since period end date is NULL)
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

    $this->createRelationship($contact1, $manager);
    $formValues = [];
    $searchDirectory = new SearchDirectory($formValues);
    $results = $this->extractColumnValues($searchDirectory->all(0, 10));

    $expectedResults = [
      [
        'contact_id' => $contact1['id'],
        'display_name' => $contact1['display_name'],
        'work_phone' => "{$contactWorkPhone} + {$contactWorkPhoneExtension}",
        'work_email' => $contactWorkEmail,
        'manager' => $manager['display_name'],
        'location' => "{$location1['name']},{$location2['name']}",
        'department' => "{$department1['name']},{$department2['name']}",
        'job_title' => $contractTitle,
      ],
      [
        'contact_id' => $manager['id'],
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

    $manager1 = ContactFabricator::fabricate(['first_name' => 'Manager1']);
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

    $location1 = $this->createLocation('location1');
    $department1 = $this->createDepartment('department1');

    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'location' => $location1['value'],
      'department' => $department1['value']
    ]);

    //active manager relationship
    $this->createRelationship($contact1, $manager1);
    $this->createRelationship($contact1, $manager3, '2016-01-01');
    //inactive relationship
    $this->createRelationship($contact1, $manager2, '2016-01-01', '2016-12-31');

    $formValues = [];
    $searchDirectory = new SearchDirectory($formValues);
    $results = $this->extractColumnValues($searchDirectory->all(0, 10));

    $expectedResults = [
      [
        'contact_id' => $contact1['id'],
        'display_name' => $contact1['display_name'],
        'work_phone' => "{$contactWorkPhone} + {$contactWorkPhoneExtension}",
        'work_email' => $contactWorkEmail,
        'manager' => "{$manager1['display_name']},{$manager3['display_name']}",
        'location' => $location1['name'],
        'department' => $department1['name'],
        'job_title' => $contractTitle,
      ],
      [
        'contact_id' => $manager1['id'],
        'display_name' => $manager1['display_name'],
        'work_phone' => NULL,
        'work_email' => NULL,
        'manager' => NULL,
        'location' => NULL,
        'department' => NULL,
        'job_title' => NULL,
      ],
      [
        'contact_id' => $manager2['id'],
        'display_name' => $manager2['display_name'],
        'work_phone' => NULL,
        'work_email' => NULL,
        'manager' => NULL,
        'location' => NULL,
        'department' => NULL,
        'job_title' => NULL,
      ],
      [
        'contact_id' => $manager3['id'],
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

  public function testRightContactsAreReturnedWhenIncludeContactIsTrue() {
    $contact1 = ContactFabricator::fabricate(['first_name' => 'Contact1']);
    $contact2 = ContactFabricator::fabricate(['first_name' => 'Contact2']);
    $contact3 = ContactFabricator::fabricate(['first_name' => 'Contact3']);

    $formValues = [];
    //We need to simulate the values here when a contact is selected via the
    //checkbox via the UI.
    $formValues['mark_x_'. $contact1['id']] = '';
    $formValues['mark_x_'. $contact3['id']] = '';

    $searchDirectory = new SearchDirectory($formValues);
    $includeContactId = TRUE;
    $results = $this->extractColumnValues($searchDirectory->all(0, 0, NULL, $includeContactId));

    //Only contact1 and contact3 are expected
    $expectedResults = [
      [
        'contact_id' => $contact1['id'],
        'display_name' => $contact1['display_name'],
        'work_phone' => NULL,
        'work_email' => NULL,
        'manager' => NULL,
        'location' => NULL,
        'department' => NULL,
        'job_title' => NULL,
      ],
      [
        'contact_id' => $contact3['id'],
        'display_name' => $contact3['display_name'],
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

  public function testExpectedTaskListIsReturned() {
    $formValues = [];
    $searchDirectory =  new SearchDirectory($formValues);

    $expectedTaskList = [
      'Create User Accounts(s)',
      'Delete Staff',
      'Delete Staff Permanently',
      'Export Staff',
      'Print/merge document'
    ];

    $form = new CRM_Core_Form_Search();
    $form->setVar('_taskList', [
      'Create User Accounts(s)',
      'Delete contacts',
      'Delete permanently',
      'Export contacts',
      'Print/merge document',
      'Sample Task',
      'Add Tags'
    ]);

    $this->assertEquals($expectedTaskList, array_values($searchDirectory->buildTaskList($form)));
  }

  public function testDisplayNameFilterReturnsOnlyResultsThatMatchesFilterValue() {
    $contact1 = ContactFabricator::fabricate(['first_name' => 'FirstTest', 'last_name' => 'Contact1']);
    $contact2 = ContactFabricator::fabricate(['first_name' => 'Sample', 'last_name' => 'Doe']);
    $contact3 = ContactFabricator::fabricate(['first_name' => 'Contact3', 'last_name' => 'TestLast']);

    $formValues = [
      'name' => 'Test'
    ];
    $searchDirectory = new SearchDirectory($formValues);
    $results = $this->extractColumnValues($searchDirectory->all(0, 10));

    //Contact1 and Contact3 has the keyword 'Test' in their first and last names respectively
    $expectedResults = [
      [
        'contact_id' => $contact1['id'],
        'display_name' => $contact1['display_name'],
        'work_phone' => NULL,
        'work_email' => NULL,
        'manager' => NULL,
        'location' => NULL,
        'department' => NULL,
        'job_title' => NULL,
      ],
      [
        'contact_id' => $contact3['id'],
        'display_name' => $contact3['display_name'],
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

  public function testJobTitleFilterReturnsOnlyResultsThatMatchesFilterValue() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();
    $contact3 = ContactFabricator::fabricate();

    $contract1Title = 'Test Contract';
    $contract3Title = 'ContraTestSample';

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2017-01-01',
        'period_end_date' => '2017-12-31',
        'title' => $contract1Title
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-12-31',
        'title' => 'Sample Contract'
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact3['id']],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-12-31',
        'title' => $contract3Title
      ]
    );

    $formValues = [
      'job_title' => 'Test'
    ];
    $searchDirectory = new SearchDirectory($formValues);
    $results = $this->extractColumnValues($searchDirectory->all(0, 10));
    //Contact1 and Contact3 has the keyword 'Test' in their job contract titles
    $expectedResults = [
      [
        'contact_id' => $contact1['id'],
        'display_name' => $contact1['display_name'],
        'work_phone' => NULL,
        'work_email' => NULL,
        'manager' => NULL,
        'location' => NULL,
        'department' => NULL,
        'job_title' => $contract1Title,
      ],
      [
        'contact_id' => $contact3['id'],
        'display_name' => $contact3['display_name'],
        'work_phone' => NULL,
        'work_email' => NULL,
        'manager' => NULL,
        'location' => NULL,
        'department' => NULL,
        'job_title' => $contract3Title,
      ]
    ];

    $this->assertEquals($expectedResults, $results);
  }

  public function testDepartmentFilterReturnsOnlyResultsThatMatchesFilterValue() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();
    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-12-31',
      ]
    );

    $contract2 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      [
        'period_start_date' => '2017-01-01',
        'period_end_date' => '2017-12-31',
      ]
    );

    $location1 = $this->createLocation('location1');
    $department1 = $this->createDepartment('department1');
    $department2 = $this->createDepartment('department2');

    //Contact1 has a contract linked to two job roles
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'location' => $location1['value'],
      'department' => $department1['value']
    ]);

    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'department' => $department2['value']
    ]);

    //Contact2 has a contract linked to one job role
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract2['id'],
      'department' => $department2['value']
    ]);

    $formValues = [
      'department' => $department1['value']
    ];
    $searchDirectory = new SearchDirectory($formValues);
    $results = $this->extractColumnValues($searchDirectory->all(0, 10));

    //Contact1 belongs to department1 in one of the job roles.
    $expectedResults = [
      [
        'contact_id' => $contact1['id'],
        'display_name' => $contact1['display_name'],
        'work_phone' => NULL,
        'work_email' => NULL,
        'manager' => NULL,
        'location' => $location1['name'],
        'department' => "{$department1['name']},{$department2['name']}",
        'job_title' => NULL,
      ],
    ];

    $this->assertEquals($expectedResults, $results);
  }

  public function testLocationFilterReturnsOnlyResultsThatMatchesFilterValue() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();
    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-12-31',
      ]
    );

    $contract2 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      [
        'period_start_date' => '2017-01-01',
        'period_end_date' => '2017-12-31',
      ]
    );

    $department1 = $this->createDepartment('department1');
    $location1 = $this->createLocation('location1');
    $location2 = $this->createLocation('location2');

    //Contact1 has a contract linked to two job roles
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'location' => $location1['value'],
      'department' => $department1['value']
    ]);

    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'location' => $location2['value']
    ]);

    //Contact2 has a contract linked to one job role
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract2['id'],
      'location' => $location2['value']
    ]);

    $formValues = [
      'location' => $location1['value']
    ];
    $searchDirectory = new SearchDirectory($formValues);
    $results = $this->extractColumnValues($searchDirectory->all(0, 10));

    //Contact1 belongs to location1 in one of the job roles.
    $expectedResults = [
      [
        'contact_id' => $contact1['id'],
        'display_name' => $contact1['display_name'],
        'work_phone' => NULL,
        'work_email' => NULL,
        'manager' => NULL,
        'location' => "{$location1['name']},{$location2['name']}",
        'department' => $department1['name'],
        'job_title' => NULL,
      ],
    ];

    $this->assertEquals($expectedResults, $results);
  }

  public function testDepartmentInConjunctionWithLocationFilterWillNotReturnResultsIfNoMatchFound() {
    $contact1 = ContactFabricator::fabricate();
    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-12-31',
      ]
    );

    $department1 = $this->createDepartment('department1');
    $department2 = $this->createDepartment('department2');
    $location1 = $this->createLocation('location1');
    $location2 = $this->createLocation('location2');

    //Contact1 has a contract linked to two job roles
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

    $formValues = [
      'department' => $department1['value'],
      'location' => $location2['value']
    ];
    $searchDirectory = new SearchDirectory($formValues);
    $results = $this->extractColumnValues($searchDirectory->all(0, 10));

    //Because the filters work with the 'AND' condition, no results will be returned
    //because there is no job role that the user has in which he belongs to department1
    //and location2 at the same time
    $this->assertEmpty($results);
  }

  public function testJobTitleInConjunctionWithLocationFilterWillNotReturnResultsIfNoMatchFound() {
    $contact1 = ContactFabricator::fabricate();
    $contractTitle = 'ContractTitle';
    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2016-01-01',
        'period_end_date' => '2016-12-31',
        'title' => 'Test Contract'
      ]
    );

    $contract2 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2017-01-01',
        'period_end_date' => '2017-12-31',
        'title' => $contractTitle
      ]
    );

    $department1 = $this->createDepartment('department1');
    $location1 = $this->createLocation('location1');

    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'location' => $location1['value'],
      'department' => $department1['value']
    ]);


    $formValues = [
      'job_title' => $contractTitle,
      'department' => $department1['value'],
      'location' => $location1['value']
    ];
    $searchDirectory = new SearchDirectory($formValues);
    $results = $this->extractColumnValues($searchDirectory->all(0, 10));


    //Even though the contact has a Job contract with the specified title, since the
    //operator is 'AND' and there is no contract job title linked to a role with
    //the specified department and location, no result is returned.
    $this->assertEmpty($results);
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

    return $result->fetchAll();
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

  private function createRelationship($contactA, $contactB, $startDate = NULL, $endDate = NULL, $isActive = 1) {
    RelationshipFabricator::fabricate([
      'contact_id_a' => $contactA['id'],
      'contact_id_b' => $contactB['id'],
      'relationship_type_id' => $this->relationshipType['id'],
      'start_date' => $startDate,
      'end_date' => $endDate,
      'is_active' => $isActive
    ]);
  }

  public static function fabricateContactWithWorkContactDetails($params = [], $email, $phone, $phoneExt = '') {
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
      'phone_ext' => $phoneExt
    ]);

    return $contact;
  }
}
