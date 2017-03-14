<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeaveRequestDateTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeaveRequestDateTest extends BaseHeadlessTest {

  public function setUp() {
    // In order to make tests simpler, we disable the foreign key checks,
    // as a way to allow the creation of leave request records related
    // to a non-existing leave period entitlement
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");
  }

  /**
   * @expectedException PEAR_Exception
   * @expectedExceptionMessage DB Error: already exists
   */
  public function testALeaveRequestCantHaveMoreThanOneLeaveRequestDateWithTheSameDate() {
    $date = LeaveRequestDate::create([
      'date' => date('YmdHis'),
      'leave_request_id' => 1,
    ]);

    $this->assertNotNull($date->id);

    LeaveRequestDate::create([
      'date' => date('YmdHis'),
      'leave_request_id' => 1,
    ]);
  }

  public function testGetDatesForLeaveRequestReturnsTheDatesForASpecificLeaveRequest() {
    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => date('YmdHis'),
      'to_date' => date('YmdHis', strtotime('+1 day')),
      'status_id' => 1
    ]);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => date('YmdHis', strtotime('+1 day')),
      'to_date' => date('YmdHis', strtotime('+3 days')),
      'status_id' => 1
    ]);

    $leaveRequest3 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => date('YmdHis'),
      'to_date' => date('YmdHis'),
      'status_id' => 1
    ]);

    $datesLeaveRequest1 = LeaveRequestDate::getDatesForLeaveRequest($leaveRequest1->id);
    $this->assertCount(2, $datesLeaveRequest1);
    $this->assertEquals(date('Y-m-d'), $datesLeaveRequest1[0]->date);
    $this->assertEquals(date('Y-m-d', strtotime('+1 day')), $datesLeaveRequest1[1]->date);

    $datesLeaveRequest2 = LeaveRequestDate::getDatesForLeaveRequest($leaveRequest2->id);
    $this->assertCount(3, $datesLeaveRequest2);
    $this->assertEquals(date('Y-m-d', strtotime('+1 day')), $datesLeaveRequest2[0]->date);
    $this->assertEquals(date('Y-m-d', strtotime('+2 days')), $datesLeaveRequest2[1]->date);
    $this->assertEquals(date('Y-m-d', strtotime('+3 days')), $datesLeaveRequest2[2]->date);

    $datesLeaveRequest3 = LeaveRequestDate::getDatesForLeaveRequest($leaveRequest3->id);
    $this->assertCount(1, $datesLeaveRequest3);
    $this->assertEquals(date('Y-m-d'), $datesLeaveRequest3[0]->date);
  }

  public function testGetDatesForLeaveRequestReturnsEmptyArrayForANonExistingLeaveRequest() {
    $this->assertCount(0, LeaveRequestDate::getDatesForLeaveRequest(12321321312));
  }

  public function testDeleteDatesDeletesTheDatesForASpecificLeaveRequest() {
    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => date('YmdHis'),
      'to_date' => date('YmdHis'),
      'status_id' => 1
    ]);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => date('YmdHis'),
      'to_date' => date('YmdHis', strtotime('+1 day')),
      'status_id' => 1
    ]);

    $this->assertCount(1, LeaveRequestDate::getDatesForLeaveRequest($leaveRequest1->id));
    $this->assertCount(2, LeaveRequestDate::getDatesForLeaveRequest($leaveRequest2->id));

    LeaveRequestDate::deleteDatesForLeaveRequest($leaveRequest1->id);
    $this->assertCount(0, LeaveRequestDate::getDatesForLeaveRequest($leaveRequest1->id));
    $this->assertCount(2, LeaveRequestDate::getDatesForLeaveRequest($leaveRequest2->id));

    LeaveRequestDate::deleteDatesForLeaveRequest($leaveRequest2->id);
    $this->assertCount(0, LeaveRequestDate::getDatesForLeaveRequest($leaveRequest1->id));
    $this->assertCount(0, LeaveRequestDate::getDatesForLeaveRequest($leaveRequest2->id));
  }

  public function testGetDatesForLeaveRequestReturnsEmptyArrayForSoftDeletedLeaveRequest() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => 1,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' =>  CRM_Utils_Date::processDate('2016-01-02'),
      'status_id' => 1
    ]);

    LeaveRequest::softDelete($leaveRequest->id);
    $leaveRequestDates = LeaveRequestDate::getDatesForLeaveRequest($leaveRequest->id);
    $this->assertCount(0, $leaveRequestDates);
  }
}
