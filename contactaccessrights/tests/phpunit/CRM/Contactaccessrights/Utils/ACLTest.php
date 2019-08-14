<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;
use CRM_Contactaccessrights_Test_Fabricator_Rights as RightsFabricator;

/**
 * Class CRM_Contactaccessrights_BAO_RightsTest
 * Tests Rights BAO class for contact's access rights to civicrm.
 *
 * @group headless
 */
class CRM_Contactaccessrights_Utils_ACLTest extends BaseHeadlessTest {

  /**
   * Tests if ACL Utility class is building the Where Conditions appropriately,
   * using option_value.value for Locations and Regions in the table join.
   */
  public function testWhereConditionsIncludesTheRightContactIdLocationsAndRegions() {
    $localAdmin = ContactFabricator::fabricate();

    $region1 = $this->createOptionValue('hrjc_region', 'Region 1');
    $region2 = $this->createOptionValue('hrjc_region', 'Region 2');
    $location1 = $this->createOptionValue('hrjc_location', 'Location 1');
    $location2 = $this->createOptionValue('hrjc_location', 'Location 2');

    $this->setContactRights($localAdmin, $region1);
    $this->setContactRights($localAdmin, $location1);
    $this->setContactRights($localAdmin, $location2);

    $aclUtil = new CRM_Contactaccessrights_Utils_ACL($localAdmin['id']);
    $whereConditions = $aclUtil->getWhereConditions();

    $matches = [];
    preg_match(
      "/contact_a.id = {$localAdmin['id']} OR car_jr.location IN \((.+?)\) OR car_jr.region IN \((.+?)\)/",
      $whereConditions[0],
      $matches
    );

    $this->assertTrue(isset($matches[0]), 'Where clause does not the expected format');

    $locations = array_map('trim', explode(',', $matches[1]));
    $this->assertContains("'{$location1['value']}'", $locations, 'Expected value in clause for locations is not found.');
    $this->assertContains("'{$location2['value']}'", $locations, 'Expected value in clause for locations is not found.');

    // Note that Region 2 was not set to this contact, so it shouldn't be
    // in the where condition
    $regions = array_map('trim', explode(',', $matches[2]));
    $this->assertContains("'{$region1['value']}'", $regions);
    $this->assertNotContains("'{$region2['value']}'", $regions);
  }

  /**
   * Creates a new record for Rights entity for the given contact.
   *
   * @param array $contact
   *   Associative array with details for the contact
   * @param array $entity
   *   Details of either Region or Location for which the contact will be
   *   granted access
   * @return array
   *   Details of access right created in an associative array
   */
  private function setContactRights($contact, $entity) {
    return RightsFabricator::fabricate([
      'contact_id' => $contact['id'],
      'entity_id' => $entity['id'],
      'entity_type' => $entity['entity_type']
    ]);
  }

  /**
   * Creates a Record in Option Value for given Option Group
   *
   * @param string $optionGroup
   *   Name of the option group to which the option will be added
   * @param string $value
   *   Value for the new option value, used as its name, label and value
   * @return array
   *   Details of added option value + entity_type attribute
   */
  private function createOptionValue($optionGroup, $value) {
    $params = [
      'option_group_id' => $optionGroup,
      'name' => $value,
      'label' => $value,
      'value' => $value
    ];
    $result = OptionValueFabricator::fabricate($params);
    $result['entity_type'] = $optionGroup;

    return $result;
  }

}
