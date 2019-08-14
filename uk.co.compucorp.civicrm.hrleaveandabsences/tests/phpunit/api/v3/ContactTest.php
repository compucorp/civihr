<?php

use Civi\Test\HookInterface;
use CRM_Contact_BAO_Contact as Contact;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_RelationshipType as RelationshipTypeFabricator;
use CRM_HRCore_Test_Fabricator_Relationship as RelationshipFabricator;

/**
 * Class api_v3_LeaveRequestTest
 *
 * @group headless
 */
class api_v3_ContactTest extends BaseHeadlessTest implements HookInterface {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_ApiHelpersTrait;

  private $manager;

  private $contact1;

  private $contact2;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $tableName = Contact::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");

    $this->contact1 = ContactFabricator::fabricate(['first_name' => 'ContactA']);
    $this->contact2 = ContactFabricator::fabricate(['first_name' => 'ContactB']);
    $this->manager = ContactFabricator::fabricate();

    $this->setLeaveApproverRelationshipTypes([
      'has leaves approved by',
      'has things managed by',
    ]);

    $this->registerCurrentLoggedInContactInSession($this->manager['id']);
    $this->setPermissions(['access AJAX API']) ;
  }

  public function testGetLeaveManageesDoesNotReturnDeceasedORDeletedContacts() {
    $contact3 = ContactFabricator::fabricate(['is_deleted' => 1]);
    $contact4 = ContactFabricator::fabricate(['is_deceased' => 1]);

    $this->setContactAsLeaveApproverOf($this->manager, $this->contact2, null, null, true, 'has things managed by');
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1, null, null, true, 'has leaves approved by');
    $this->setContactAsLeaveApproverOf($this->manager, $contact3, null, null, true, 'has leaves approved by');
    $this->setContactAsLeaveApproverOf($this->manager, $contact4, null, null, true, 'has things managed by');

    $result = $this->callAPI('Contact', 'getleavemanagees', ['managed_by' => $this->manager['id']]);

    //only the two contacts who are neither deleted nor deceased is returned
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($result['values'][$this->contact1['id']]['id'], $this->contact1['id']);
    $this->assertEquals($result['values'][$this->contact1['id']]['display_name'], $this->contact1['display_name']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);
    $this->assertEquals($result['values'][$this->contact2['id']]['display_name'], $this->contact2['display_name']);
  }

  public function testGetLeaveManageesDoesNotReturnFilteredOutFields() {
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1, null, null, true, 'has things managed by');

    $result = $this->callAPI('Contact', 'getleavemanagees', ['managed_by' => $this->manager['id']]);

    //filtered out fields are hash, created_date, modified_date
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($result['values'][$this->contact1['id']]['id'], $this->contact1['id']);
    $this->assertEquals($result['values'][$this->contact1['id']]['display_name'], $this->contact1['display_name']);
    $this->assertArrayNotHasKey('hash', $result['values'][$this->contact1['id']]);
    $this->assertArrayNotHasKey('created_date', $result['values'][$this->contact1['id']]);
    $this->assertArrayNotHasKey('modified_date', $result['values'][$this->contact1['id']]);
  }

  public function testGetLeaveManageesDoesNotReturnFilteredOutFieldsWhenFilteredOutFieldsArePartOfFieldsToReturn() {
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact2, null, null, true, 'has things managed by');

    $result = $this->callAPI('Contact', 'getleavemanagees', [
      'managed_by' => $this->manager['id'],
      'return' => ['id', 'hash', 'display_name', 'created_date', 'modified_date']
    ]);

    //filtered put fields are hash, created_at, modified_at
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);
    $this->assertEquals($result['values'][$this->contact2['id']]['display_name'], $this->contact2['display_name']);
    $this->assertArrayNotHasKey('hash', $result['values'][$this->contact2['id']]);
    $this->assertArrayNotHasKey('created_date', $result['values'][$this->contact2['id']]);
    $this->assertArrayNotHasKey('modified_date', $result['values'][$this->contact2['id']]);
  }

  public function testGetLeaveManageesOnlyReturnsContactsManagedByTheContactPassedInManagedByParameter() {
    $contact3 = ContactFabricator::fabricate();
    $manager2 = ContactFabricator::fabricate();

    $this->setContactAsLeaveApproverOf($this->manager, $this->contact2, null, null, true, 'has things managed by');
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1, null, null, true, 'has leaves approved by');
    $this->setContactAsLeaveApproverOf($manager2, $contact3, null, null, true, 'has leaves approved by');

    //even though the logged in manager does not have 'edit all contacts' or 'view all contacts' permission
    //the manager is able to view results even with check_permissions set to true because
    // the Contact.getleavemanagees api changes this value to false.
    $result = $this->callAPI('Contact', 'getleavemanagees', ['managed_by' => $this->manager['id']]);

    $this->assertEquals(2, $result['count']);
    $this->assertEquals($result['values'][$this->contact1['id']]['id'], $this->contact1['id']);
    $this->assertEquals($result['values'][$this->contact1['id']]['display_name'], $this->contact1['display_name']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);
    $this->assertEquals($result['values'][$this->contact2['id']]['display_name'], $this->contact2['display_name']);
  }

  public function testGetLeaveManageesCanBeFilteredByDifferentFields() {

    $this->setContactAsLeaveApproverOf($this->manager, $this->contact2, null, null, true, 'has things managed by');
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1, null, null, true, 'has leaves approved by');

    $result = $this->callAPI('Contact', 'getleavemanagees', [
      'managed_by' => $this->manager['id'],
      'id' => $this->contact1['id'],
      'display_name' => $this->contact1['display_name'],
      'sort_name' => $this->contact1['sort_name']
    ]);

    $this->assertEquals(1, $result['count']);
    $this->assertEquals($result['values'][$this->contact1['id']]['id'], $this->contact1['id']);

    //filter results by the display_name field
    $result = $this->callAPI('Contact', 'getleavemanagees', [
      'managed_by' => $this->manager['id'],
      'display_name' => $this->contact2['display_name'],
    ]);

    $this->assertEquals(1, $result['count']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);

    //this will return the two contacts because both have 'Contact' in their first names
    $result = $this->callAPI('Contact', 'getleavemanagees', [
      'managed_by' => "user_contact_id",
      'display_name' => ['LIKE' => "%Contact%"],
    ]);

    $this->assertEquals(2, $result['count']);
    $this->assertEquals($result['values'][$this->contact1['id']]['id'], $this->contact1['id']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);
  }

  public function testGetLeaveManageesReturnsEmptyWhenALoggedInManagerIsTryingToAccessTheManageesOfAnotherManager() {
    $manager2 = ContactFabricator::fabricate();

    $this->setContactAsLeaveApproverOf($this->manager, $this->contact2, null, null, true, 'has things managed by');
    $this->setContactAsLeaveApproverOf($manager2, $this->contact1, null, null, true, 'has leaves approved by');

    //the logged in manager can't access the the managees of another manager.
    $result = $this->callAPI('Contact', 'getleavemanagees', ['managed_by' => $manager2['id']]);

    //No result will be returned because a manager is not allowed to access the managees of another manager
    $this->assertEquals(0, $result['count']);
  }

  public function testGetLeaveManageesReturnsResultsWhenALoggedInAdminIsTryingToAccessTheManageesOfAManager() {
    $adminID = 1;
    $this->registerCurrentLoggedInContactInSession($adminID);
    $this->setPermissions(['access AJAX API', 'administer leave and absences']);

    $this->setContactAsLeaveApproverOf($this->manager, $this->contact2, null, null, true, 'has things managed by');
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1, null, null, true, 'has leaves approved by');

    $result = $this->callAPI('Contact', 'getleavemanagees', ['managed_by' => $this->manager['id']]);

    $this->assertEquals(2, $result['count']);
    $this->assertEquals($result['values'][$this->contact1['id']]['id'], $this->contact1['id']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);
  }

  public function testGetLeaveManageesReturnsEmptyResultsWhenStaffMemberIsTryingToAccessTheManageesOfAManager() {
    $staffID = 1;
    $this->registerCurrentLoggedInContactInSession($staffID);
    $this->setPermissions(['access AJAX API']);

    $this->setContactAsLeaveApproverOf($this->manager, $this->contact2, null, null, true, 'has things managed by');
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1, null, null, true, 'has leaves approved by');

    $result = $this->callAPI('Contact', 'getleavemanagees', ['managed_by' => $this->manager['id']]);

    $this->assertEquals(0, $result['count']);
  }

  /**
   * @expectedExceptionMessage Either unassigned must be true or managed_by parameter present
   * @expectedException CiviCRM_API3_Exception
   */
  public function testGetLeaveManageesThrowsAnExceptionWhenUnassignedIsFalseAndManagedByIsAbsent() {
    $this->callAPI('Contact', 'getleavemanagees', ['unassigned' => false]);
  }

  /**
   * @expectedExceptionMessage Unassigned cannot be true and managed_by parameter also present
   * @expectedException CiviCRM_API3_Exception
   */
  public function testGetLeaveManageesThrowsAnExceptionWhenUnassignedIsTrueAndManagedByParameterIsPresent() {
    $this->callAPI('Contact', 'getleavemanagees', ['unassigned' => true, 'managed_by' => 1]);
  }

  /**
   * @expectedExceptionMessage Either unassigned must be true or managed_by parameter present
   * @expectedException CiviCRM_API3_Exception
   */
  public function testGetLeaveManageesThrowsAnExceptionWhenUnassignedAndManagedByParameterIsAbsent() {
    $this->callAPI('Contact', 'getleavemanagees', []);
  }

  public function testGetLeaveManageesReturnsOnlyContactsWithoutActiveLeaveApproverRelationshipWhenUnassignedTrue() {
    $contact3 = ContactFabricator::fabricate();

    $relationshipType = RelationshipTypeFabricator::fabricate();
    //Contact3 has a relationship with manager but the relationship is not
    //of type leave approver.
    RelationshipFabricator::fabricate([
      'contact_id_a' => $contact3['id'],
      'contact_id_b' => $this->manager['id'],
      'relationship_type_id' => $relationshipType['id']
    ]);

    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1);

    $result = $this->callAPI('Contact', 'getleavemanagees', ['unassigned' => true]);

    //The manager, contact2 and contact3 does not have an active leave approver
    //relationship.
    $this->assertEquals(3, $result['count']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);
    $this->assertEquals($result['values'][$contact3['id']]['id'], $contact3['id']);
    $this->assertEquals($result['values'][$this->manager['id']]['id'], $this->manager['id']);
  }

  public function testGetLeaveManageesReturnsNoInformationForContactWithActiveLeaveManagerAndOtherRelationshipWhenUnassignedIsTrue() {
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1);
    $relationshipType = RelationshipTypeFabricator::fabricate();

    //Add a neutral relationship between contact1 and manager that is not of
    //type leave approver.
    RelationshipFabricator::fabricate([
      'contact_id_a' => $this->contact1['id'],
      'contact_id_b' => $this->manager['id'],
      'relationship_type_id' => $relationshipType['id']
    ]);

    $result = $this->callAPI('Contact', 'getleavemanagees', ['unassigned' => true]);

    //contact2 and manager will be returned because they are without active leave
    //approver relationship.
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);
    $this->assertEquals($result['values'][$this->manager['id']]['id'], $this->manager['id']);
  }


  public function testGetLeaveManageesReturnsOnlyContactsWithoutActiveLeaveApproverRelationshipForAdminWhenUnassignedTrue() {
    $adminID = 1;
    $this->registerCurrentLoggedInContactInSession($adminID);
    $this->setPermissions(['access AJAX API', 'administer leave and absences']);

    $contact3 = ContactFabricator::fabricate();

    $relationshipType = RelationshipTypeFabricator::fabricate();
    //Contact3 has a relationship with manager but the relationship is not
    //of type leave approver.
    RelationshipFabricator::fabricate([
      'contact_id_a' => $contact3['id'],
      'contact_id_b' => $this->manager['id'],
      'relationship_type_id' => $relationshipType['id']
    ]);

    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1);

    $result = $this->callAPI('Contact', 'getleavemanagees', ['unassigned' => true]);

    //The manager, contact2 and contact3 does not have an active leave approver
    //relationship.
    $this->assertEquals(3, $result['count']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);
    $this->assertEquals($result['values'][$contact3['id']]['id'], $contact3['id']);
    $this->assertEquals($result['values'][$this->manager['id']]['id'], $this->manager['id']);
  }

  public function testGetLeaveManageesReturnsNoInformationForContactWithActiveLeaveManagerAndOtherRelationshipForAdminWhenUnassignedIsTrue() {
    $adminID = 1;
    $this->registerCurrentLoggedInContactInSession($adminID);
    $this->setPermissions(['access AJAX API', 'administer leave and absences']);
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1);

    $relationshipType = RelationshipTypeFabricator::fabricate();

    //Add a neutral relationship between contact1 and manager that is not of
    //type leave approver.
    RelationshipFabricator::fabricate([
      'contact_id_a' => $this->contact1['id'],
      'contact_id_b' => $this->manager['id'],
      'relationship_type_id' => $relationshipType['id']
    ]);

    $result = $this->callAPI('Contact', 'getleavemanagees', ['unassigned' => true]);

    //contact2 and manager will be returned because they are without active leave
    //approver relationship.
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);
    $this->assertEquals($result['values'][$this->manager['id']]['id'], $this->manager['id']);
  }

  public function testGetStaffOnlyReturnContactsOfTypeIndividual() {
    $organization = ContactFabricator::fabricateOrganization();

    $result = civicrm_api3('Contact', 'getStaff');

    // 3 contacts are created in the setUp method, so they should be
    // returned and the organization one create here should not.
    $this->assertEquals(3, $result['count']);
    $this->assertFalse(array_key_exists($organization['id'], $result['values']));
  }

  public function testGetStaffDoesntAllowFilteringByAFieldNotIncludedInTheSpec() {
    $randomEmail = date('YmdHis') . '@example.org';
    $result = civicrm_api3('Contact', 'getStaff', ['email' => $randomEmail]);

    // The email param (which is not included in the spec)
    // was simply ignored and all the 3 existing contacts are returned
    $this->assertEquals(3, $result['count']);
  }

  public function testGetStaffAllowsFilteringByAFieldIncludedInTheSpec() {
    $value = date('YmdHis');

    // Ideally we should get the field names from the API spec and test
    // each individually, but doing that is actually quite difficult
    // because some might not be strings and some, like the display_name
    // one, have their values generated automatically by Civi. For this
    // reason, we just have a hardcoded list of fields here. Since the
    // API doesn't support a lot of fields, this should be good enough
    // for now
    $params = [
      'first_name' => 'first_name' . $value,
      'middle_name' => 'middle_name' . $value,
      'last_name' => 'last_name' . $value,
    ];
    $contact = ContactFabricator::fabricate($params);

    $result = civicrm_api3('Contact', 'getStaff', ['id' => $contact['id']]);
    $this->assertEquals(1, $result['count']);
    $this->assertTrue(array_key_exists($contact['id'], $result['values']));

    $result = civicrm_api3('Contact', 'getStaff', ['first_name' => $params['first_name']]);
    $this->assertEquals(1, $result['count']);
    $this->assertTrue(array_key_exists($contact['id'], $result['values']));

    $result = civicrm_api3('Contact', 'getStaff', ['middle_name' => $params['middle_name']]);
    $this->assertEquals(1, $result['count']);
    $this->assertTrue(array_key_exists($contact['id'], $result['values']));

    $result = civicrm_api3('Contact', 'getStaff', ['last_name' => $params['last_name']]);
    $this->assertEquals(1, $result['count']);
    $this->assertTrue(array_key_exists($contact['id'], $result['values']));
  }

  public function testGetStaffAllowsOptions() {
    // There are many options that can be passed and they are all implemented
    // by Civi. What we want to test here is that options are passed to
    // Civi internally, so testing only one of the options is good enough
    $result = civicrm_api3('Contact', 'getStaff', ['options' => ['limit' => 1]]);
    $this->assertEquals(1, $result['count']);
  }

  public function testGetStaffAllowsSequential() {
    $sequentialResult = civicrm_api3('Contact', 'getStaff', ['sequential' => true]);
    $nonSequentialResult = civicrm_api3('Contact', 'getStaff', ['sequential' => false]);

    $sequentialKeys = array_keys($sequentialResult['values']);
    $nonSequentialKeys = array_keys($nonSequentialResult['values']);

    $this->assertNotEquals($sequentialKeys, $nonSequentialKeys);
  }

  public function testGetStaffAllowsReturn() {
    $result = civicrm_api3('Contact', 'getStaff', ['return' => ['first_name']]);

    foreach ($result['values'] as $record) {
      $recordKeys = array_keys($record);
      sort($recordKeys);
      // The ID is always returned by civi, so even though we asked
      // for first_name only, we'll actually get the ID too
      $this->assertEquals($recordKeys, ['first_name', 'id']);
    }
  }

  public function testGetStaffDoesNotReturnDisallowedFields() {
    $result = civicrm_api3('Contact', 'getStaff', ['return' => 'email']);

    foreach ($result['values'] as $record) {
      $recordKeys = array_keys($record);
      // ID is always returned, so this is why count is 1
      // However, since email is not an allowed field,
      // it won't be returned.
      $this->assertCount(1, $recordKeys);
      $this->assertEquals('id', $recordKeys[0]);
    }
  }

  public function testGetStaffDoesNotCheckForPermissions() {
    // In hook_civicrm_aclWhereClause, implemented in this class, we
    // make sure the current logged in user (manager) will only be able to
    // see contact1.
    // When check_permissions is true, the ACL will be applied and only contact 1
    // will be returned
    $result = civicrm_api3('Contact', 'get', ['check_permissions' => true]);
    $this->assertEquals(1, $result['count']);
    $this->assertTrue(array_key_exists($this->contact1['id'], $result['values']));

    // Now we use check_permissions again, but getStaff overrides that and no
    // ACLs will be applied, resulting in all existing contacts being returned
    $result = civicrm_api3('Contact', 'getStaff', ['check_permissions' => true]);
    $this->assertEquals(3, $result['count']);
  }

  /**
   * An implementation of the aclWhereClause to help with the tests
   * that rely on ACLs (testGetStaffDoesNotCheckForPermissions).
   *
   * Basically, it makes sure the current logged in user can only see
   * contact1.
   *
   * IMPORTANT: Even though this was created mainly for the
   * testGetStaffDoesNotCheckForPermissions tests, it will actually be
   * applied to any test on the class that makes an API call where
   * check_permissions is TRUE
   */
  public function hook_civicrm_aclWhereClause($type, &$tables, &$whereTables, &$contactID, &$where) {
    if (!$contactID) {
      return;
    }

    $where = 'contact_a.id = ' . $this->contact1['id'];
  }
}
