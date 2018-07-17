<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;

/**
 * Class CRM_HRSampleData_Importer_ExtendedDemographicsTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_ExtendedDemographicsTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  private $testContact;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate();
  }

  public function testProcess() {
    $this->rows[] = [
      $this->testContact['id'],
      1020,
      'Not Applicable',
      'Not Applicable',
      'Not Applicable',
      'Single',
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']],
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_ExtendedDemographics', $this->rows, $mapping);

    $extendedDemographic = $this->apiGet('CustomValue', ['entity_id' => $this->testContact['id']]);

    $this->assertEquals($this->testContact['id'], $extendedDemographic['entity_id']);
  }

  private function importHeadersFixture() {
    return [
      'entity_id',
      'Nationality',
      'Ethnicity',
      'Religion',
      'Sexual_Orientation',
      'Marital_Status',
    ];
  }

}
