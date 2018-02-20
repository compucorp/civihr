<?php

use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;

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

    // The Leave Request API only returns requests overlapping a Job Contract.
    // So we need to create a Job Contract first
    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contactID ],
      [ 'period_start_date' => '2017-01-01' ]
    );

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'FooBar'
    ]);

    $absencePeriod = $this->apiGet('LeaveRequest');
    $this->assertEmpty($absencePeriod);

    $this->rows[] = [
      $absenceType->title,
      $contactID,
      1,
      '2017-01-16 00:00:00',
      1,
      '2017-01-20 00:00:00',
      1,
      'leave',
      0,
    ];

    // The importer uses a mapping to cover the contact ids in the csv file
    // to the actual ids after the contact were imported, so we create a fake
    // mapping here to maps a contact to itself
    $mapping = [
      ['contact_mapping', $contactID, $contactID],
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_LeaveRequest', $this->rows, $mapping);

    $leaveRequest = $this->apiGet('LeaveRequest');

    foreach($this->rows[0] as $index => $fieldName) {
      // During the import, the type_name is converted to a type_id
      // so here we check if it was converted to the right id
      if($fieldName == 'type_name') {
        $this->assertEquals($absenceType->id, $leaveRequest['type_id']);
      } else {
        $this->assertEquals($this->rows[1][$index], $leaveRequest[$fieldName]);
      }
    }
  }

  private function importHeadersFixture() {
    return [
      'type_name',
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
