<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_Activity as ActivityFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;

/**
 * Class CRM_HRSampleData_Importer_ActivityTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_ActivityTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  private $testContact;

  private $testActivityType;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate();
    $this->testActivityType = OptionValueFabricator::fabricate([
      'option_group_id' => 'activity_type'
    ]);
  }

  public function testProcessWithoutSourceRecord() {
    $this->rows[] = [
      1,
      '',
      $this->testActivityType['name'],
      "New Year's Day",
      '2016-01-01 00:00:00',
      '',
      '',
      'Scheduled',
      'Normal',
      $this->testContact['id'],
      '',
      '',
      '0',
      '0'
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']],
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_Activity', $this->rows, $mapping);

    $activity = $this->apiGet('Activity', ['subject' => "New Year's Day"]);

    $this->assertEquals("New Year's Day", $activity['subject']);
    $this->assertEquals('2016-01-01 00:00:00', $activity['activity_date_time']);
    $this->assertEquals($this->testContact['id'], $activity['source_contact_id']);
  }

  public function testProcessWithSourceRecord() {
    $sourceRecordID = ActivityFabricator::fabricate()['id'];

    $this->rows[] = [
      1,
      $sourceRecordID,
      $this->testActivityType['name'],
      "New Year's Day",
      '2016-01-01 00:00:00',
      '',
      '',
      'Scheduled',
      'Normal',
      $this->testContact['id'],
      '',
      '',
      '0',
      '0'
    ];

    $mapping = [
      ['activity_mapping', $sourceRecordID],
      ['contact_mapping', $this->testContact['id']],
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_Activity', $this->rows, $mapping);

    $activity = $this->apiGet('Activity', ['source_record_id' => $sourceRecordID]);

    $this->assertEquals($sourceRecordID, $activity['source_record_id']);
    $this->assertEquals("New Year's Day", $activity['subject']);
    $this->assertEquals('2016-01-01 00:00:00', $activity['activity_date_time']);
    $this->assertEquals($this->testContact['id'], $activity['source_contact_id']);
  }

  public function testProcessWhenRecordIsTask() {
    $sourceRecordID = ActivityFabricator::fabricate()['id'];

    $activityTypeLabel = 'FooBar';

    // There are two Activity Types with the Same label,
    // but since we're importing a Task, the one for the
    // CiviTask component will be used
    $documentActivityType = OptionValueFabricator::fabricate([
      'option_group_id' => 'activity_type',
      'label' => $activityTypeLabel,
      'component_id' => 'CiviDocument'
    ]);

    $taskActivityType = OptionValueFabricator::fabricate([
      'option_group_id' => 'activity_type',
      'label' => $activityTypeLabel,
      'component_id' => 'CiviTask'
    ]);

    $this->rows[] = [
      1,
      $sourceRecordID,
      $activityTypeLabel,
      "New Year's Day",
      '2016-01-01 00:00:00',
      '',
      '',
      'Scheduled',
      'Normal',
      $this->testContact['id'],
      '',
      '',
      '1', //is_task
      '0'
    ];

    $mapping = [
      ['activity_mapping', $sourceRecordID],
      ['contact_mapping', $this->testContact['id']],
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_Activity', $this->rows, $mapping);

    $activity = $this->apiGet('Activity', ['source_record_id' => $sourceRecordID]);

    $this->assertEquals($sourceRecordID, $activity['source_record_id']);
    $this->assertEquals("New Year's Day", $activity['subject']);
    $this->assertEquals('2016-01-01 00:00:00', $activity['activity_date_time']);
    $this->assertEquals($this->testContact['id'], $activity['source_contact_id']);
    $this->assertEquals($taskActivityType['value'], $activity['activity_type_id']);
  }

  public function testProcessWhenRecordIsDocument() {
    $sourceRecordID = ActivityFabricator::fabricate()['id'];

    $activityTypeLabel = 'FooBar';

    // There are two Activity Types with the Same label,
    // but since we're importing a Document, the one for the
    // CiviDocument component will be used
    $documentActivityType = OptionValueFabricator::fabricate([
      'option_group_id' => 'activity_type',
      'label' => $activityTypeLabel,
      'component_id' => 'CiviDocument'
    ]);

    $taskActivityType = OptionValueFabricator::fabricate([
      'option_group_id' => 'activity_type',
      'label' => $activityTypeLabel,
      'component_id' => 'CiviTask'
    ]);

    $this->rows[] = [
      1,
      $sourceRecordID,
      $activityTypeLabel,
      "New Year's Day",
      '2016-01-01 00:00:00',
      '',
      '',
      'Scheduled',
      'Normal',
      $this->testContact['id'],
      '',
      '',
      '0',
      '1' // is_document
    ];

    $mapping = [
      ['activity_mapping', $sourceRecordID],
      ['contact_mapping', $this->testContact['id']],
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_Activity', $this->rows, $mapping);

    $activity = $this->apiGet('Activity', ['source_record_id' => $sourceRecordID]);

    $this->assertEquals($sourceRecordID, $activity['source_record_id']);
    $this->assertEquals("New Year's Day", $activity['subject']);
    $this->assertEquals('2016-01-01 00:00:00', $activity['activity_date_time']);
    $this->assertEquals($this->testContact['id'], $activity['source_contact_id']);
    $this->assertEquals($documentActivityType['value'], $activity['activity_type_id']);
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
      'is_task',
      'is_document'
    ];
  }

}
