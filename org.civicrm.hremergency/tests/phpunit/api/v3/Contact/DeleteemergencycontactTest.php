<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_Hremergency_Service_EmergencyContactService as EmergencyContactService;

/**
 * @group headless
 */
class api_v3_Contact_DeleteEmergencyContactTest extends \PHPUnit_Framework_TestCase
  implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function testDeletingNonExistentContact() {
    $id = 1;
    $this->mockEmergencyContactService(); // no emergency contacts

    $message = sprintf("Could not find emergency contact with ID '%d'", $id);
    $this->setExpectedException(CiviCRM_API3_Exception::class, $message);
    civicrm_api3('Contact', 'deleteemergencycontact', ['id' => $id]);
  }

  public function testDeletingOtherUsersContact() {
    $emergencyContactID = 1;
    $currentContactID = 2;
    $contactOwnerID = 3;
    $this->registerCurrentLoggedInContactInSession($currentContactID);

    // use a different owner to logged in user
    $emergencyContacts[$emergencyContactID] = ['entity_id' => $contactOwnerID];
    $this->mockEmergencyContactService($emergencyContacts);

    $message = "Only an emergency contacts' relation can delete them";
    $this->setExpectedException(CiviCRM_API3_Exception::class, $message);
    $params = ['id' => $emergencyContactID];
    civicrm_api3('Contact', 'deleteemergencycontact', $params);
  }

  public function testDeletionOfOwnContact() {
    $emergencyContactID = 1;
    $contactOwnerID = 2;

    $this->registerCurrentLoggedInContactInSession($contactOwnerID);

    // owner and logged in user are the same
    $emergencyContacts[$emergencyContactID] = ['entity_id' => $contactOwnerID];
    $expectedDeletions = [$emergencyContactID];
    $this->mockEmergencyContactService($emergencyContacts, $expectedDeletions);

    $params = ['id' => $emergencyContactID];
    $result = civicrm_api3('Contact', 'deleteemergencycontact', $params);

    $this->assertEquals(0, $result['is_error']);
  }

  public function testDeletionWithoutApiPermission() {
    $this->setPermission('Access AJAX API', FALSE);

    $message = '/^API permission check failed.*/';
    $this->setExpectedExceptionRegExp(CiviCRM_API3_Exception::class, $message);

    // check permissions defaults to false in tests
    $params = ['id' => 1, 'check_permissions' => TRUE];
    civicrm_api3('Contact', 'deleteemergencycontact', $params);
  }

  /**
   * Sets the current logged in contact ID
   *
   * @param $contactID
   */
  protected function registerCurrentLoggedInContactInSession($contactID) {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', $contactID);
  }

  /**
   * Mocks the emergency contact service
   *
   * @param array $contacts
   *   An array of emergency contact data indexed by ID
   * @param array $deletionIDs
   *   Which emergency contacts are expected to be deleted
   */
  private function mockEmergencyContactService($contacts = [], $deletionIDs = []) {
    $mockService = $this->prophesize(EmergencyContactService::class);
    foreach ($contacts as $id => $data) {
      $mockService->find($id)->willReturn(['id' => $id] + $data);
    }
    foreach ($deletionIDs as $deletionID) {
      $mockService->delete($deletionID)->shouldBeCalled();
    }

    Civi::container()->set('emergency_contact.service', $mockService->reveal());
  }

  /**
   * Sets a permission for the unit test permission checker.
   *
   * @param string $name
   * @param bool $isAllowed
   */
  private function setPermission($name, $isAllowed) {
    $permissionClass = CRM_Core_Config::singleton()->userPermissionClass;
    if ($isAllowed) {
      unset($permissionClass->permissions[$name]);
    } else {
      $permissionClass->permissions[$name] = FALSE;
    }
  }
}
