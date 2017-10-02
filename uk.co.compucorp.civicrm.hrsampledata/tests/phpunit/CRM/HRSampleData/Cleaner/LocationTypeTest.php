<?php

use CRM_HRCore_Test_Fabricator_LocationType as LocationTypeFabricator;

/**
 * Class CRM_HRSampleData_Cleaner_LocationTypeTest
 *
 * @group headless
 */
class CRM_HRSampleData_Cleaner_LocationTypeTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testProcessWithDeleteOnUninstallOn() {
    $testLocationType = LocationTypeFabricator::fabricate();

    $this->rows[] = [
      $testLocationType['name'],
      $testLocationType['name'],
      '',
      '',
      0,
      1,
      0,
      1,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_LocationType', $this->rows);

    $locationType = $this->apiGet('LocationType', ['name' => $testLocationType['name']]);
    $this->assertEmpty($locationType);
  }

  public function testProcessWithDeleteOnUninstallOff() {
    $testLocationType = LocationTypeFabricator::fabricate();

    $this->rows[] = [
      $testLocationType['name'],
      $testLocationType['name'],
      '',
      '',
      0,
      1,
      0,
      0,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_LocationType', $this->rows);

    $locationType = $this->apiGet('LocationType', ['name' => $testLocationType['name']]);
    $this->assertEquals($testLocationType['name'], $locationType['name']);
  }

  private function importHeadersFixture() {
    return [
      'name',
      'display_name',
      'vcard_name',
      'description',
      'is_reserved',
      'is_active',
      'is_default',
      'delete_on_uninstall',
    ];
  }
}
