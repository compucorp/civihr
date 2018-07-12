<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_Hrjobroles_Test_Fabricator_HrJobRoles as HRJobRolesFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequestCalendarFeedConfig as LeaveCalendarFeedConfigFabricator;

/**
 * Class api_v3_LeaveRequestCalendarFeedConfigTest
 *
 * @group headless
 */
class api_v3_LeaveRequestCalendarFeedConfigTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  public function testGetReturnsUnSerializedValuesForFilterFields() {
    $visibleTo = [
      'department' => [1,2],
      'location' => [1]
    ];

    $composedOf = [
      'leave_type' => [1],
      'department' => [3],
      'location' => [3]
    ];

    $results = civicrm_api3('LeaveRequestCalendarFeedConfig', 'create', [
      'title' => 'Feed 1',
      'timezone' => 'America/Monterrey',
      'composed_of' => $composedOf,
      'visible_to' => $visibleTo
    ]);

    $calendarFeedConfig = civicrm_api3('LeaveRequestCalendarFeedConfig', 'get', ['id' => $results['id']]);

    $calendarFeedConfig = array_shift($calendarFeedConfig['values']);
    $this->assertEquals($visibleTo, $calendarFeedConfig['visible_to']);
    $this->assertEquals($composedOf, $calendarFeedConfig['composed_of']);
  }

  public function testGetReturnsAllFeedsForCalendarFeedAdmin() {
    $adminId = 2;
    $this->setPermissions(['access AJAX API', 'can administer calendar feeds']);
    $this->registerCurrentLoggedInContactInSession($adminId);
    $feedConfig1 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'visible_to' => [
        'location' => ['Sample Location1'],
        'department' => ['Sample Department1']
      ]
    ]);

    $feedConfig2 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 2',
      'visible_to' => [
        'location' => ['Sample Location2'],
        'department' => ['Sample Department2']
      ]
    ]);

    $feedConfig3 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 3',
      'visible_to' => []
    ]);

    $results = civicrm_api3('LeaveRequestCalendarFeedConfig', 'get', [
      'check_permissions' => TRUE,
      'sequential' => 1
    ]);

    //Admin has access to all feeds
    $this->assertEquals(3, $results['count']);
    $this->assertEquals($feedConfig1->id, $results['values'][0]['id']);
    $this->assertEquals($feedConfig2->id, $results['values'][1]['id']);
    $this->assertEquals($feedConfig3->id, $results['values'][2]['id']);

    $this->setPermissions([]);
  }

  public function testGetReturnsTheFeedsStaffHasLocationAccessTo() {
    $contact1 = ContactFabricator::fabricate();
    $this->setPermissions(['access AJAX API']);
    $this->registerCurrentLoggedInContactInSession($contact1['id']);

    $contract1 = HRJobContractFabricator::fabricate(['contact_id' => $contact1['id']]);

    $location1 = $this->createLocation('location1');
    $location2 = $this->createLocation('location2');
    $department1 = $this->createDepartment('department1');

    //Assign the contact to a job role with access to location1
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'location' => $location1['value']
    ]);

    $feedConfig1 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'visible_to' => [
        'location' => [$location1['value']],
        'department' => [$department1['value']]
      ]
    ]);

    $feedConfig2 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 2',
      'visible_to' => [
        'location' => [$location2['value']],
        'department' => [$department1['value']]
      ]
    ]);

    //A feed with an empty array as its visible_to filter means it is accessible to all locations
    // and departments.
    $feedConfig3 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 3',
      'visible_to' => []
    ]);

    $results = civicrm_api3('LeaveRequestCalendarFeedConfig', 'get', [
      'check_permissions' => TRUE,
      'sequential' => 1
    ]);

    //Contact1 has access to feed 1 which has visibility for the location he belongs to
    //and feed3 which has visibility for all departments and locations.
    $this->assertEquals(2, $results['count']);
    $this->assertEquals($feedConfig1->id, $results['values'][0]['id']);
    $this->assertEquals($feedConfig3->id, $results['values'][1]['id']);
    $this->setPermissions([]);
  }

  public function testGetReturnsTheFeedsStaffHasDepartmentAccessTo() {
    $contact1 = ContactFabricator::fabricate();
    $this->setPermissions(['access AJAX API']);
    $this->registerCurrentLoggedInContactInSession($contact1['id']);

    $contract1 = HRJobContractFabricator::fabricate(['contact_id' => $contact1['id']]);

    $location1 = $this->createLocation('location1');
    $department1 = $this->createDepartment('department1');
    $department2 = $this->createDepartment('department2');

    //Assign the contact to a job role with access to department
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'department' => $department1['value']
    ]);

    $feedConfig1 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'visible_to' => [
        'location' => [$location1['value']],
        'department' => [$department2['value']]
      ]
    ]);

    $feedConfig2 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 2',
      'visible_to' => [
        'location' => [$location1['value']],
        'department' => [$department1['value']]
      ]
    ]);

    //A feed with an empty array as its visible_to filter means it is accessible to all locations
    //and departments.
    $feedConfig3 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 3',
      'visible_to' => []
    ]);

    $results = civicrm_api3('LeaveRequestCalendarFeedConfig', 'get', [
      'check_permissions' => TRUE,
      'sequential' => 1
    ]);

    //Contact1 has access to feed 2 which has visibility for the department he belongs to
    //and feed3 which has visibility for all department and locations.
    $this->assertEquals(2, $results['count']);
    $this->assertEquals($feedConfig2->id, $results['values'][0]['id']);
    $this->assertEquals($feedConfig3->id, $results['values'][1]['id']);
    $this->setPermissions([]);
  }

  public function testGetReturnsTheFeedsStaffHasBothLocationAndDepartmentAccessTo() {
    $contact1 = ContactFabricator::fabricate();
    $this->setPermissions(['access AJAX API']);
    $this->registerCurrentLoggedInContactInSession($contact1['id']);

    $contract1 = HRJobContractFabricator::fabricate(['contact_id' => $contact1['id']]);

    $location1 = $this->createLocation('location1');
    $location2 = $this->createLocation('location2');
    $department1 = $this->createDepartment('department1');
    $department2 = $this->createDepartment('department2');

    //Assign the contact to a job role with access to department
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'department' => $department1['value'],
      'location' => $location1['value']
    ]);

    $feedConfig1 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'visible_to' => [
        'location' => [$location1['value']],
        'department' => [$department1['value']]
      ]
    ]);

    $feedConfig2 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 2',
      'visible_to' => [
        'location' => [$location2['value']],
        'department' => [$department2['value']]
      ]
    ]);

    //A feed with an empty array as its visible_to filter means it is accessible to all locations
    //and departments.
    $feedConfig3 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 3',
      'visible_to' => []
    ]);

    $results = civicrm_api3('LeaveRequestCalendarFeedConfig', 'get', [
      'check_permissions' => TRUE,
      'sequential' => 1
    ]);

    //Contact1 has access to feed1 which has visibility for the department and location he belongs to
    //and feed3 which has visibility for all department and locations.
    $this->assertEquals(2, $results['count']);
    $this->assertEquals($feedConfig1->id, $results['values'][0]['id']);
    $this->assertEquals($feedConfig3->id, $results['values'][1]['id']);
    $this->setPermissions([]);
  }

  public function testGetDoesNotReturnResultsWhenThereAreNoFeedsStaffHasLocationOrDepartmentAccessTo() {
    $contact1 = ContactFabricator::fabricate();
    $this->setPermissions(['access AJAX API']);
    $this->registerCurrentLoggedInContactInSession($contact1['id']);

    $contract1 = HRJobContractFabricator::fabricate(['contact_id' => $contact1['id']]);

    $location1 = $this->createLocation('location1');
    $location2 = $this->createLocation('location2');
    $department1 = $this->createDepartment('department1');
    $department2 = $this->createDepartment('department2');

    //Assign the contact to a job role with access to department
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'department' => $department1['value'],
      'location' => $location1['value']
    ]);

    $feedConfig1 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'visible_to' => [
        'location' => [$location2['value']],
        'department' => [$department2['value']]
      ]
    ]);

    $feedConfig2 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 2',
      'visible_to' => [
        'department' => [$department2['value']]
      ]
    ]);

    $results = civicrm_api3('LeaveRequestCalendarFeedConfig', 'get', [
      'check_permissions' => TRUE,
      'sequential' => 1
    ]);

    $this->assertEquals(0, $results['count']);
    $this->setPermissions([]);
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
}
