<?php

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
    $locationType = LocationTypeFabricator::fabricate();
    $testLocationType = $this->apiGet('LocationType', ['name' => $locationType['name']]);
    $this->assertEquals($locationType['name'], $testLocationType['name']);

    $this->rows[] = [
      $locationType['name'],
      $locationType['name'],
      '',
      '',
      0,
      1,
      0
    ];

    $this->runIterator('CRM_HRSampleData_Cleaner_LocationType', $this->rows);

    $locationType = $this->apiGet('LocationType', ['name' => $locationType['name']]);
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
