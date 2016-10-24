<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/ContactEmail.php';

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;

/**
 * Class CRM_HRSampleData_Importer_ContactEmailTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_ContactEmailTest extends CRM_HRSampleData_BaseImporterTest {

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
      'phoebe@sccs.org',
      1,
      'Work',
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']]
    ];

    $this->runImporter('CRM_HRSampleData_Importer_ContactEmail',  $this->rows, $mapping);

    $this->assertEquals('phoebe@sccs.org', $this->apiGet('Email', 'email', 'phoebe@sccs.org'));
  }

  private function importHeadersFixture() {
    return [
      'contact_id',
      'email',
      'is_primary',
      'location_type_id',
    ];
  }

}
