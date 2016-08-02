<?php

require_once 'CiviTest/CiviUnitTestCase.php';

/**
 * Class CRM_Hrjobcontract_RevisionEffectiveDateTest
 */
class CRM_Hrjobcontract_RevisionEffectiveDateTest extends CiviUnitTestCase {
  private $_time = null;
  private $_testContactId = null;
  private $_testJobContractId = null;

  function setUp() {
    parent::setUp();
    $this->quickCleanup(array(
      'civicrm_hrjobcontract_details',
      'civicrm_hrjobcontract_health',
      'civicrm_hrjobcontract_role',
      'civicrm_hrjobcontract_hour',
      'civicrm_hrjobcontract_pay',
      'civicrm_hrjobcontract_leave',
      'civicrm_hrjobcontract_pension',
      'civicrm_hrjobcontract_revision',
      'civicrm_hrjobcontract',
    ));
    $upgrader = CRM_Hrjobcontract_Upgrader::instance();
    $upgrader->install();
    $this->_time = time();
    $this->_createTestContact();
    $this->_createTestJobContract($this->_testContactId);
  }

  function tearDown() {
    parent::tearDown();
  }

  function testRevisionEffectiveDates() {
    // Create a future Job Contract Details revision.
    civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'jobcontract_id' => $this->_testJobContractId,
      'title' => "Job Contract Test Title",
      'period_start_date' => date('Y-m-d', strtotime('+2 week', $this->_time)),
      'period_end_date' => date('Y-m-d', strtotime('+4 week', $this->_time)),
    ));
    $details = $this->_getJobContractDetails($this->_testJobContractId);
    $this->assertEquals("Job Contract Test Title", $details['title']);
    // Edit Job Contract Details revision with changing its start date and title.
    civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'id' => $details['id'],
      'jobcontract_id' => $this->_testJobContractId,
      'jobcontract_revision_id' => $details['jobcontract_revision_id'],
      'title' => "Job Contract Test Title changed",
      'period_start_date' => date('Y-m-d', strtotime('+1 week', $this->_time)),
    ));
    $details = $this->_getJobContractDetails($this->_testJobContractId);
    $this->assertEquals("Job Contract Test Title changed", $details['title']);

    // Edit Job Contract Details revision with changing its start date to past
    // and also changing its title.
    civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'id' => $details['id'],
      'jobcontract_id' => $this->_testJobContractId,
      'jobcontract_revision_id' => $details['jobcontract_revision_id'],
      'title' => "Job Contract started two weeks ago",
      'period_start_date' => date('Y-m-d', strtotime('-2 week', $this->_time)),
    ));
    $details = $this->_getJobContractDetails($this->_testJobContractId);
    $this->assertEquals("Job Contract started two weeks ago", $details['title']);

    // Create a new revision of Job Contract with start date a week ago.
    $detailsRevision = civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'title' => "Job Contract started one week ago",
      'period_start_date' => date('Y-m-d', strtotime('-1 week', $this->_time)),
      'jobcontract_id' => $this->_testJobContractId,
    ));
    civicrm_api3('HRJobContractRevision', 'create', array(
      'sequential' => 1,
      'id' => $detailsRevision['values'][0]['jobcontract_revision_id'],
      'effective_date' => date('Y-m-d', strtotime('-1 week', $this->_time)),
    ));
    $details = $this->_getJobContractDetails($this->_testJobContractId);
    $this->assertEquals("Job Contract started one week ago", $details['title']);

    // Create a new revision of Job Contract with start date three days ago.
    $detailsRevision = civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'title' => "Job Contract started three days ago",
      'period_start_date' => date('Y-m-d', strtotime('-3 day', $this->_time)),
      'jobcontract_id' => $this->_testJobContractId,
    ));
    civicrm_api3('HRJobContractRevision', 'create', array(
      'sequential' => 1,
      'id' => $detailsRevision['values'][0]['jobcontract_revision_id'],
      'effective_date' => date('Y-m-d', strtotime('-3 day', $this->_time)),
    ));
    $details = $this->_getJobContractDetails($this->_testJobContractId);
    $this->assertEquals("Job Contract started three days ago", $details['title']);
  }

  /**
   * Create a test Contact (Individual).
   * 
   * @param string $displayName
   * @throws Exception
   */
  private function _createTestContact($displayName = "Test Contact") {
    $result = civicrm_api3('Contact', 'create', array(
      'sequential' => 1,
      'contact_type' => "Individual",
      'display_name' => $displayName,
    ));
    if (empty($result['id'])) {
      throw new Exception("Cannot create a test Contact.");
    }
    $this->_testContactId = $result['id'];
  }

  /**
   * Create a test Job Contract.
   * 
   * @param int $contactId
   * @throws Exception
   */
  private function _createTestJobContract($contactId) {
    $result = civicrm_api3('HRJobContract', 'create', array(
      'sequential' => 1,
      'contact_id' => $contactId,
    ));
    if (empty($result['id'])) {
      throw new Exception("Cannot create a test Job Contact.");
    }
    $this->_testJobContractId = $result['id'];
  }

  private function _getJobContractDetails($jobContractId) {
    return civicrm_api3('HRJobDetails', 'getsingle', array(
      'sequential' => 1,
      'jobcontract_id' => $jobContractId,
    ));
  }
}
