<?php

require_once EXTENSION_ROOT_DIR . 'CRM/CiviHRSampleData/Importer/ContactPhone.php';

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;

/**
 * Class CRM_CiviHRSampleData_Importer_ContactPhoneTest
 *
 * @group headless
 */
class CRM_CiviHRSampleData_Importer_ContactPhoneTest extends CRM_CiviHRSampleData_BaseImporterTest {

  private $rows;

  private $testContact;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate(['first_name' => 'chrollo', 'last_name' => 'lucilfer']);
  }

  public function testImport() {
    $this->rows[] = [
      $this->testContact['id'],
      1,
      7586311952,
      7586311952,
      'Mobile',
      'Main',
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']]
    ];

    $this->runImporter('CRM_CiviHRSampleData_Importer_ContactPhone', $this->rows, $mapping);

    $this->assertEquals(7586311952, $this->apiQuickGet('Phone', 'phone', 7586311952));
  }

  private function importHeadersFixture() {
    return [
      'contact_id',
      'is_primary',
      'phone',
      'phone_numeric',
      'phone_type_id',
      'location_type_id',
    ];
  }

}
