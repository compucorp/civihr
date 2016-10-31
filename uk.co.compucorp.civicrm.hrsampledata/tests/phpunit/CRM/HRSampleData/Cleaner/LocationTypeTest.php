<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/LocationType.php';

use CRM_HRCore_Test_Fabricator_LocationType as LocationTypeFabricator;

/**
 * Class CRM_HRSampleData_Cleaner_LocationTypeTest
 *
 * @group headless
 */
class CRM_HRSampleData_Cleaner_LocationTypeTest extends CRM_HRSampleData_BaseImporterTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testIterate() {
    LocationTypeFabricator::fabricate();
    $testLocationType = $this->apiGet('LocationType', ['name' => 'test location type']);
    $this->assertEquals('test location type', $testLocationType['name']);

    $this->rows[] = [
      'test location type',
      'test location type',
      '',
      '',
      0,
      1,
      0
    ];

    $this->runIterator('CRM_HRSampleData_Cleaner_LocationType', $this->rows);

    $locationType = $this->apiGet('LocationType', ['name' => 'test location type']);
    $this->assertEmpty($locationType);
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
