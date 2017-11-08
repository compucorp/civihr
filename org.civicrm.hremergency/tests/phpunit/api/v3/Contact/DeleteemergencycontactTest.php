<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use Tests\CiviHR\HREmergency\Fabricator\EmergencyContactFabricator;

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
    $message = "Could not find emergency contact with ID '0'";
    $this->setExpectedException(CiviCRM_API3_Exception::class, $message);
    civicrm_api3('Contact', 'deleteemergencycontact', ['id' => 0]);
  }

  public function testDeletingWithoutPermission() {
    $contacts = civicrm_api3('Contact', 'get');
    $contactIDs = array_column($contacts['values'], 'id');
    $sampleID = current($contactIDs);

    $id = EmergencyContactFabricator::fabricate($sampleID, 'Kevin Foot');
    $message = "Only an emergency contacts' relation can delete them";
    $this->setExpectedException(CiviCRM_API3_Exception::class, $message);
    civicrm_api3('Contact', 'deleteemergencycontact', ['id' => $id]);
  }

  public function testDeletionOfOwnContact() {
    $contacts = civicrm_api3('Contact', 'get');
    $contactIDs = array_column($contacts['values'], 'id');
    $sampleID = current($contactIDs);

    $this->registerCurrentLoggedInContactInSession($sampleID);
    $id = EmergencyContactFabricator::fabricate($sampleID, 'Kevin Foot');

    $result = civicrm_api3('Contact', 'deleteemergencycontact', ['id' => $id]);

    $this->assertEquals(0, $result['is_error']);
  }

  /**
   * @param $contactID
   */
  protected function registerCurrentLoggedInContactInSession($contactID) {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', $contactID);
  }
}
