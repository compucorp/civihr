<?php

use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;

/**
 * Class CRM_HRSampleData_Importer_LeaveRequestTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_LeaveRequestTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testProcess() {
    $contactID = 1;
    $absenceTypeID = 1;

    // The Leave Request API only returns requests overlapping a Job Contract.
    // So we need to create a Job Contract first
    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contactID ],
      [ 'period_start_date' => '2017-01-01' ]
    );

    $absencePeriod = $this->apiGet('LeaveRequest');
    $this->assertEmpty($absencePeriod);

    $this->rows[] = [
      $absenceTypeID,
      $contactID,
      1,
      '2017-01-16 00:00:00',
      1,
      '2017-01-20 00:00:00',
      1,
      'leave',
      0,
    ];

    // The importer uses a mapping to convert the contact and absence type ids
    // in the csv file to the actual ids after the contact were imported, so we
    // create a fake mapping here that maps a contact and an absence type to
    // themselves
    $mapping = [
      [ 'contact_mapping', $contactID, $contactID ],
      [ 'absence_type_mapping', $absenceTypeID, $absenceTypeID ]
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_LeaveRequest', $this->rows, $mapping);

    $leaveRequest = $this->apiGet('LeaveRequest');

    foreach($this->rows[0] as $index => $fieldName) {
      $this->assertEquals($this->rows[1][$index], $leaveRequest[$fieldName]);
    }
  }

  private function importHeadersFixture() {
    return [
      'type_id',
      'contact_id',
      'status_id',
      'from_date',
      'from_date_type',
      'to_date',
      'to_date_type',
      'request_type',
      'is_deleted',
    ];
  }

}
