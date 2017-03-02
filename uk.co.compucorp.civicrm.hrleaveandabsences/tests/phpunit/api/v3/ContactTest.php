<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;

/**
 * Class api_v3_LeaveRequestTest
 *
 * @group headless
 */
class api_v3_ContactTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  private $manager;

  private $contact1;

  private $contact2;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');

    $this->contact1 = ContactFabricator::fabricate();
    $this->contact2 = ContactFabricator::fabricate();
    $this->manager = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($this->manager['id']);

    $this->setLeaveApproverRelationshipTypes([
      'approves leaves for',
      'manages things for',
    ]);
  }

  public function testGetLeaveManageesDoesNotReturnDeceasedORDeletedContacts() {
    $contact3 = ContactFabricator::fabricate(['is_deleted' => 1]);
    $contact4 = ContactFabricator::fabricate(['is_deceased' => 1]);

    $this->setContactAsLeaveApproverOf($this->manager, $this->contact2, null, null, true, 'manages things for');
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1, null, null, true, 'approves leaves for');
    $this->setContactAsLeaveApproverOf($this->manager, $contact3, null, null, true, 'approves leaves for');
    $this->setContactAsLeaveApproverOf($this->manager, $contact4, null, null, true, 'manages things for');

    $result = civicrm_api3('Contact', 'getleavemanagees');

    //only the two contacts who are neither deleted nor deceased is returned
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($result['values'][$this->contact1['id']]['id'], $this->contact1['id']);
    $this->assertEquals($result['values'][$this->contact1['id']]['display_name'], $this->contact1['display_name']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);
    $this->assertEquals($result['values'][$this->contact2['id']]['display_name'], $this->contact2['display_name']);
  }

  public function testGetLeaveManageesDoesNotReturnFilteredOutFields() {
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1, null, null, true, 'manages things for');

    $result = civicrm_api3('Contact', 'getleavemanagees');

    //filtered out fields are hash, created_date, modified_date
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($result['values'][$this->contact1['id']]['id'], $this->contact1['id']);
    $this->assertEquals($result['values'][$this->contact1['id']]['display_name'], $this->contact1['display_name']);
    $this->assertArrayNotHasKey('hash', $result['values'][$this->contact1['id']]);
    $this->assertArrayNotHasKey('created_date', $result['values'][$this->contact1['id']]);
    $this->assertArrayNotHasKey('modified_date', $result['values'][$this->contact1['id']]);
  }

  public function testGetLeaveManageesDoesNotReturnFilteredOutFieldsWhenFilteredOutFieldsArePartOfFieldsToReturn() {
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact2, null, null, true, 'manages things for');

    $result = civicrm_api3('Contact', 'getleavemanagees', [
      'return' => ['id','hash', 'display_name', 'created_date', 'modified_date']
    ]);

    //filtered put fields are hash, created_at, modified_at
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);
    $this->assertEquals($result['values'][$this->contact2['id']]['display_name'], $this->contact2['display_name']);
    $this->assertArrayNotHasKey('hash', $result['values'][$this->contact2['id']]);
    $this->assertArrayNotHasKey('created_date', $result['values'][$this->contact2['id']]);
    $this->assertArrayNotHasKey('modified_date', $result['values'][$this->contact2['id']]);
  }

  public function testGetLeaveManageesOnlyReturnsContactsManagedByTheCurrentLoggedInManager() {
    $contact3 = ContactFabricator::fabricate();
    $manager2 = ContactFabricator::fabricate();

    $this->setContactAsLeaveApproverOf($this->manager, $this->contact2, null, null, true, 'manages things for');
    $this->setContactAsLeaveApproverOf($this->manager, $this->contact1, null, null, true, 'approves leaves for');
    $this->setContactAsLeaveApproverOf($manager2, $contact3, null, null, true, 'approves leaves for');

    $result = civicrm_api3('Contact', 'getleavemanagees');

    //only the two contacts who are managed by the manager are returned
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($result['values'][$this->contact1['id']]['id'], $this->contact1['id']);
    $this->assertEquals($result['values'][$this->contact1['id']]['display_name'], $this->contact1['display_name']);
    $this->assertEquals($result['values'][$this->contact2['id']]['id'], $this->contact2['id']);
    $this->assertEquals($result['values'][$this->contact2['id']]['display_name'], $this->contact2['display_name']);
  }
}
