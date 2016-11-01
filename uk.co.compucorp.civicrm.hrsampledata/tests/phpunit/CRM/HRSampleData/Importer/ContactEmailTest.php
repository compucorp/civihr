<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_LocationType as LocationTypeFabricator;

/**
 * Class CRM_HRSampleData_Importer_ContactEmailTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_ContactEmailTest extends CRM_HRSampleData_BaseCSVProcessorTest {

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
      'phoebe@sccs.org',
      1,
      $this->testLocationType['name'],
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']]
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_ContactEmail',  $this->rows, $mapping);

    $email = $this->apiGet('Email', ['email' => 'phoebe@sccs.org']);

    $this->assertEquals('phoebe@sccs.org', $email['email']);
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
