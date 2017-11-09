<?php

use CRM_Hremergency_Test_Fabricator_EmergencyContactFabricator as EmergencyContactFabricator;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_Hremergency_Service_EmergencyContactService as EmergencyContactService;

/**
 * @group headless
 */
class EmergencyContactServiceTest extends \PHPUnit_Framework_TestCase
  implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function testFind() {
    $contact = ContactFabricator::fabricate();
    $name = 'Kevin Bacon';
    $created = EmergencyContactFabricator::fabricate($contact['id'], $name);

    $service = new EmergencyContactService();
    $found = $service->find($created['id']);

    $this->assertEquals($name, $found['Name']);
  }

  public function testDelete() {
    $contact = ContactFabricator::fabricate();
    $name = 'Kevin Bacon';
    $created = EmergencyContactFabricator::fabricate($contact['id'], $name);

    $service = new EmergencyContactService();
    $service->delete($created['id']);

    $this->assertNull($service->find($created['id']));
  }
}
