<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/ContactAddress.php';

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;

/**
 * Class CRM_HRSampleData_Importer_ContactAddressTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_ContactAddressTest extends CRM_HRSampleData_BaseImporterTest {

  private $rows;

  private $testContact;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate();
  }

  public function testImport() {
    $this->rows[] = [
      $this->testContact['id'],
      1,
      '39 Elizabeth St,',
      'Belgravia',
      '',
      'London',
      'SW1W 9RP',
      '',
      'Main',
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']]
    ];

    $this->runImporter('CRM_HRSampleData_Importer_ContactAddress', $this->rows, $mapping);

    $this->assertEquals('39 Elizabeth St,', $this->apiGet('Address', 'street_address', '39 Elizabeth St,'));
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
