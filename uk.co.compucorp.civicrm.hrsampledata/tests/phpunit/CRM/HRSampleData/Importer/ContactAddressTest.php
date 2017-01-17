<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_LocationType as LocationTypeFabricator;

/**
 * Class CRM_HRSampleData_Importer_ContactAddressTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_ContactAddressTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  private $testContact;

  private $testLocationType;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate();

    $this->testLocationType = LocationTypeFabricator::fabricate();
  }

  public function testProcess() {
    $this->rows[] = [
      $this->testContact['id'],
      1,
      '39 Elizabeth St,',
      'Belgravia',
      '',
      'London',
      'SW1W 9RP',
      '',
      $this->testLocationType['name'],
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']]
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_ContactAddress', $this->rows, $mapping);

    $address = $this->apiGet('Address', ['street_address' => '39 Elizabeth St,']);

    $this->assertEquals('39 Elizabeth St,', $address['street_address']);
    $this->assertEquals('Belgravia', $address['supplemental_address_1']);
    $this->assertEquals('London', $address['city']);
    $this->assertEquals('SW1W 9RP', $address['postal_code']);
  }

  private function importHeadersFixture() {
    return [
      'contact_id',
      'is_primary',
      'street_address',
      'supplemental_address_1',
      'supplemental_address_2',
      'city',
      'postal_code',
      'country_id',
      'location_type_id',
    ];
  }

}
