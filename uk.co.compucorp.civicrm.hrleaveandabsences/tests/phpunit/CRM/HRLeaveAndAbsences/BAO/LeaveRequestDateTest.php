<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

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
    LeaveRequestDate::create([
      'date' => date('YmdHis'),
      'leave_request_id' => 1,
    ]);

    LeaveRequestDate::create([
      'date' => date('YmdHis', strtotime('+1 day')),
      'leave_request_id' => 1,
    ]);

    LeaveRequestDate::create([
      'date' => date('YmdHis', strtotime('+1 day')),
      'leave_request_id' => 2,
    ]);

    LeaveRequestDate::create([
      'date' => date('YmdHis', strtotime('+2 days')),
      'leave_request_id' => 2,
    ]);

    LeaveRequestDate::create([
      'date' => date('YmdHis', strtotime('+3 days')),
      'leave_request_id' => 2,
    ]);

    LeaveRequestDate::create([
      'date' => date('YmdHis'),
      'leave_request_id' => 3,
    ]);

    $datesLeaveRequest1 = LeaveRequestDate::getDatesForLeaveRequest(1);
    $this->assertCount(2, $datesLeaveRequest1);
    $this->assertEquals(date('Y-m-d'), $datesLeaveRequest1[0]->date);
    $this->assertEquals(date('Y-m-d', strtotime('+1 day')), $datesLeaveRequest1[1]->date);

    $datesLeaveRequest2 = LeaveRequestDate::getDatesForLeaveRequest(2);
    $this->assertCount(3, $datesLeaveRequest2);
    $this->assertEquals(date('Y-m-d', strtotime('+1 day')), $datesLeaveRequest2[0]->date);
    $this->assertEquals(date('Y-m-d', strtotime('+2 days')), $datesLeaveRequest2[1]->date);
    $this->assertEquals(date('Y-m-d', strtotime('+3 days')), $datesLeaveRequest2[2]->date);

    $datesLeaveRequest3 = LeaveRequestDate::getDatesForLeaveRequest(3);
    $this->assertCount(1, $datesLeaveRequest3);
    $this->assertEquals(date('Y-m-d'), $datesLeaveRequest3[0]->date);
  }

  public function testGetDatesForLeaveRequestReturnsEmptyArrayForANonExistingLeaveRequest() {
    $this->assertCount(0, LeaveRequestDate::getDatesForLeaveRequest(12321321312));
  }

  public function testDeleteDatesDeletesTheDatesForASpecificLeaveRequest() {
    LeaveRequestDate::create([
      'date' => date('YmdHis'),
      'leave_request_id' => 1,
    ]);

    LeaveRequestDate::create([
      'date' => date('YmdHis'),
      'leave_request_id' => 2,
    ]);

    LeaveRequestDate::create([
      'date' => date('YmdHis', strtotime('+1 day')),
      'leave_request_id' => 2,
    ]);

    $this->assertCount(1, LeaveRequestDate::getDatesForLeaveRequest(1));
    $this->assertCount(2, LeaveRequestDate::getDatesForLeaveRequest(2));

    LeaveRequestDate::deleteDatesForLeaveRequest(1);
    $this->assertCount(0, LeaveRequestDate::getDatesForLeaveRequest(1));
    $this->assertCount(2, LeaveRequestDate::getDatesForLeaveRequest(2));

    LeaveRequestDate::deleteDatesForLeaveRequest(2);
    $this->assertCount(0, LeaveRequestDate::getDatesForLeaveRequest(1));
    $this->assertCount(0, LeaveRequestDate::getDatesForLeaveRequest(2));
  }
}
