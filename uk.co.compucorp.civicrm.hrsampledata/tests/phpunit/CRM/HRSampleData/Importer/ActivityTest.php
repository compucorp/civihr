<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/Activity.php';

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_Activity as ActivityFabricator;

/**
 * Class CRM_HRSampleData_Importer_ActivityTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_ActivityTest extends CRM_HRSampleData_BaseImporterTest {

  private $rows;

  private $testContact;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate();
  }

  public function testImportWithoutSourceRecord() {
    $this->rows[] = [
      1,
      '',
      'Open Case',
      "New Year's Day",
      '2016-01-01 00:00:00',
      '',
      '',
      'Scheduled',
      'Normal',
      $this->testContact['id'],
      '',
      '',
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']],
    ];

    $this->runImporter('CRM_HRSampleData_Importer_Activity', $this->rows, $mapping);

    $this->assertEquals("New Year's Day", $this->apiGet('Activity','subject', "New Year's Day"));
  }

  public function testImportWithSourceRecord() {
    $sourceRecordID = ActivityFabricator::fabricate();

    $this->rows[] = [
      1,
      $sourceRecordID,
      'Open Case',
      "New Year's Day",
      '2016-01-01 00:00:00',
      '',
      '',
      'Scheduled',
      'Normal',
      $this->testContact['id'],
      '',
      '',
    ];

    $mapping = [
      ['activity_mapping', $sourceRecordID],
      ['contact_mapping', $this->testContact['id']],
    ];

    $this->runImporter('CRM_HRSampleData_Importer_Activity', $this->rows, $mapping);

    $this->assertEquals($sourceRecordID, $this->apiGet('Activity','source_record_id', $sourceRecordID));
  }

  private function importHeadersFixture() {
    return [
      'id',
      'source_record_id',
      'activity_type_id',
      'subject',
      'activity_date_time',
      'duration',
      'details',
      'status_id',
      'priority_id',
      'source_contact_id',
      'assignee_id',
      'target_id',
    ];
  }

}
