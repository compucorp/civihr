<?php

require_once EXTENSION_ROOT_DIR . 'CRM/CiviHRSampleData/Importer/LocationType.php';

/**
 * Class CRM_CiviHRSampleData_Importer_LocationTypeTest
 *
 * @group headless
 */
class CRM_CiviHRSampleData_Importer_LocationTypeTest extends CRM_CiviHRSampleData_BaseImporterTest {

  private $rows;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testImport() {
    $this->rows[] = [
      'Correspondence',
      'Correspondence',
      'Correspondence',
      'Postal address',
      0,
      0,
      0
    ];

    $this->runImporter('CRM_CiviHRSampleData_Importer_LocationType', $this->rows);


    $this->assertEquals('Correspondence', $this->apiQuickGet('LocationType', 'name', 'Correspondence'));
  }

  private function importHeadersFixture() {
    return [
      'name',
      'display_name',
      'vcard_name',
      'description',
      'is_reserved',
      'is_active',
      'is_default'
    ];
  }

}
