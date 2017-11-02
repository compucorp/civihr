<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_Relationship as RelationshipFabricator;
use CRM_HRCore_Service_Manager as ManagerService;

/**
 * @group headless
 */
class CRM_HRCore_Service_ManagerTest extends CRM_HRCore_Test_BaseHeadlessTest {

  public function testGetLineManagersList() {
    $contactA = ContactFabricator::fabricate([
      'first_name' => 'chrollo',
      'last_name' => 'lucilfer'
    ]);

    $contactB = ContactFabricator::fabricate([
      'first_name' => 'hisoka',
      'last_name' => 'morou'
    ]);

    $contactC = ContactFabricator::fabricate([
      'first_name' => 'illumi',
      'last_name' => 'zoldyck'
    ]);

    $managerService = new ManagerService();

    $this->setContactAsLineManagerOf($contactB, $contactA);
    $managers = $managerService->getLineManagersFor($contactA['id']);

    $this->assertContains($contactB['display_name'], $managers);
    $this->assertCount(1, $managers);

    $this->setContactAsLineManagerOf($contactC, $contactA);
    $managers = $managerService->getLineManagersFor($contactA['id']);

    $this->assertContains($contactC['display_name'], $managers);
    $this->assertContains($contactB['display_name'], $managers);
    $this->assertCount(2, $managers);
  }

  private function setContactAsLineManagerOf($manager, $contact) {
    $relationshipType = civicrm_api3('RelationshipType', 'getsingle', [
      'return' => ['id'],
      'name_a_b' => 'Line Manager is',
    ]);

    RelationshipFabricator::fabricate([
      'contact_id_a' => $contact['id'],
      'contact_id_b' => $manager['id'],
      'relationship_type_id' => $relationshipType['id'],
    ]);
  }

}
