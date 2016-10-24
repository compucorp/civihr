<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/Relationships.php';

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_Case as CaseFabricator;
use CRM_HRCore_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_HRCore_Test_Fabricator_RelationshipType as RelationshipTypeFabricator;

/**
 * Class CRM_HRSampleData_Importer_RelationshipsTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_RelationshipsTest extends CRM_HRSampleData_BaseImporterTest {

  private $rows;

  private $testContactA;

  private $testContactB;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContactA = ContactFabricator::fabricate();
    $this->testContactB = ContactFabricator::fabricate(['first_name' => 'chrollo2', 'last_name' => 'lucilfer2']);

    RelationshipTypeFabricator::fabricate();
  }

  public function testImportWithoutCase() {
    $this->rows[] = [
      $this->testContactA['id'],
      $this->testContactB['id'],
      'test AB',
      '',
      1,
      '',
    ];

    $mapping = [
      ['contact_mapping', $this->testContactA['id']],
      ['contact_mapping', $this->testContactB['id']],
    ];

    $this->runImporter('CRM_HRSampleData_Importer_Relationships', $this->rows, $mapping);

    $this->assertEquals($this->testContactA['id'], $this->apiGet('Relationship', 'contact_id_a', $this->testContactA['id']));
  }

  public function testImportWithCase() {
    CaseTypeFabricator::fabricate();
    $caseID = CaseFabricator::fabricate()['id'];

    $this->rows[] = [
      $this->testContactA['id'],
      $this->testContactB['id'],
      'test AB',
      '',
      1,
      $caseID,
    ];

    $mapping = [
      ['contact_mapping', $this->testContactA['id']],
      ['contact_mapping', $this->testContactB['id']],
      ['case_mapping', $caseID],
    ];

    $this->runImporter('CRM_HRSampleData_Importer_Relationships', $this->rows, $mapping);

    $this->assertEquals($this->testContactA['id'], $this->apiGet('Relationship', 'contact_id_a', $this->testContactA['id']));
  }

  private function importHeadersFixture() {
    return [
      'contact_id_a',
      'contact_id_b',
      'relationship_type_id',
      'start_date',
      'is_active',
      'case_id',
    ];
  }

}
