<?php

require_once 'HRJobContractTestBase.php';

/**
 * FIXME
 */
class CRM_Hrjobcontract_EditRevisionTest extends HRJobContractTestBase {
  function setUp() {
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
  }

  /**
   * Test Job Contract Revision after editing currently existing entities.
   */
  function testEditRevision() {
    
    $expected = array(
        "id" => "8",
        "jobcontract_id" => "1",
        "details_revision_id" => "1",
        "health_revision_id" => "3",
        "role_revision_id" => "4",
        "hour_revision_id" => "5",
        "pay_revision_id" => "6",
        "leave_revision_id" => "7",
        "pension_revision_id" => "8",
    );
    
    
    $this->createJobContract();
    $this->createJobContractEntities(1);
    
    // Changing existing jobcontract_details entity:
    civicrm_api3('HRJobDetails', 'create', array(
      'title' => "edited title",
      'id' => 1,
    ));
      
    $current_revision = civicrm_api3('HRJobContractRevision', 'getcurrentrevision', array(
        'sequential' => 1,
        'jobcontract_id' => 1,
    ));
    
    $this->assertAPIArrayComparison($current_revision['values'], $expected);
  }
}