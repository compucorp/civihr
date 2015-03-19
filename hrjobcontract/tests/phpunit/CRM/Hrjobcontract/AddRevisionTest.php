<?php

require_once 'HRJobContractTestBase.php';

/**
 * FIXME
 */
class CRM_Hrjobcontract_AddRevisionTest extends HRJobContractTestBase {
  function setUp() {
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
  }

  /**
   * Test Job Contract Revision after adding changes to the entities.
   */
  function testAddRevision() {
    
    $expected = array(
        "id" => "11",
        "jobcontract_id" => "1",
        "details_revision_id" => "11",
        "health_revision_id" => "3",
        "role_revision_id" => "4",
        "hour_revision_id" => "5",
        "pay_revision_id" => "10",
        "leave_revision_id" => "7",
        "pension_revision_id" => "8",
    );
    
    
    $this->createJobContract();
    $this->createJobContractEntities(1);
    
    // Adding jobcontract_details entity:
    civicrm_api3('HRJobDetails', 'create', array(
      'title' => "new title",
      'jobcontract_id' => 1,
    ));
    
    // Adding jobcontract_pay entity:
    civicrm_api3('HRJobPay', 'create', array(
      'pay_is_auto_est' => 5,
      'pay_amount' => 3,
      'jobcontract_id' => 1,
    ));
    
    // Adding jobcontract_details entity again:
    civicrm_api3('HRJobDetails', 'create', array(
      'title' => "newest title",
      'jobcontract_id' => 1,
    ));
      
    $current_revision = civicrm_api3('HRJobContractRevision', 'getcurrentrevision', array(
        'sequential' => 1,
        'jobcontract_id' => 1,
    ));
    
    $this->assertAPIArrayComparison($current_revision['values'], $expected);
  }
}