<?php

require_once EXTENSION_ROOT_DIR . 'CRM/CiviHRSampleData/Importer/Relationships.php';

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_Case as CaseFabricator;
use CRM_HRCore_Test_Fabricator_Relationship as RelationshipFabricator;

/**
 * Class CRM_CiviHRSampleData_Importer_RelationshipsTest
 *
 * @group headless
 */
class CRM_CiviHRSampleData_Importer_RelationshipsTest extends CRM_CiviHRSampleData_BaseImporterTest {

  private $rows;

  private $testContactA;

  private $testContactB;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContactA = ContactFabricator::fabricate(['first_name' => 'chrollo', 'last_name' => 'lucilfer']);
    $this->testContactB = ContactFabricator::fabricate(['first_name' => 'chrollo2', 'last_name' => 'lucilfer2']);

    RelationshipFabricator::fabricateRelationshipType();
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

    $this->runImporter('CRM_CiviHRSampleData_Importer_Relationships', $this->rows, $mapping);

    $this->assertNotEmpty($this->apiQuickGet('Relationship', 'contact_id_a', $this->testContactA['id']));
  }

  public function testImportWithCase() {
    CaseFabricator::fabricateCaseType();
    $caseID = CaseFabricator::fabricateCase()['id'];

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

    $this->runImporter('CRM_CiviHRSampleData_Importer_Relationships', $this->rows, $mapping);

    $this->assertNotEmpty($this->apiQuickGet('Relationship', 'contact_id_a', $this->testContactA['id']));
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
