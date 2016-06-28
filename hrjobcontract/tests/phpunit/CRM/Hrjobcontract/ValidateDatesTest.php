<?php

require_once 'HRJobContractTestBase.php';

/**
 * Class tests dates validation against currently exsisting Job Contracts by
 * doing checks if there is no dates overlapping.
 *
 * Run by executing: civix test CRM/Hrjobcontract/ValidateDatesTest
 * from hrjobcontract extension's main directory
 *
 * @group headless.
 */
class CRM_Hrjobcontract_ValidateDatesTest extends HRJobContractTestBase {
  function setUp() {
    $this->cleanDB();
    parent::setUp();
    $upgrader = CRM_Hrjobcontract_Upgrader::instance();
    $upgrader->install();
  }

  /**
   * Test 'validatedates' API call of HRJobDetails entity against various dates.
   */
  function testValidateDates() {
    // Create test Contact.
    $contactId = $this->_createContact();
    // Create test Job Contracts.
    $this->_createJobContracts($contactId);

    // Set of tests against invalid dates period (there are Job Contracts
    // already with period dates overlapping.
    $result = civicrm_api3('HRJobDetails', 'validatedates', array(
      'sequential' => 1,
      'contact_id' => $contactId,
      'period_start_date' => "2016-01-01",
      'period_end_date' => "2016-10-10",
    ));
    $this->assertFalse($result['values'][0]);

    $result = civicrm_api3('HRJobDetails', 'validatedates', array(
      'sequential' => 1,
      'contact_id' => $contactId,
      'period_start_date' => "2016-01-01",
      'period_end_date' => "2016-01-02",
    ));
    $this->assertFalse($result['values'][0]);

    $result = civicrm_api3('HRJobDetails', 'validatedates', array(
      'sequential' => 1,
      'contact_id' => $contactId,
      'period_start_date' => "2016-01-31",
      'period_end_date' => "2016-02-01",
    ));
    $this->assertFalse($result['values'][0]);

    $result = civicrm_api3('HRJobDetails', 'validatedates', array(
      'sequential' => 1,
      'contact_id' => $contactId,
      'period_start_date' => "2016-02-10",
      'period_end_date' => "2016-02-20",
    ));
    $this->assertFalse($result['values'][0]);

    $result = civicrm_api3('HRJobDetails', 'validatedates', array(
      'sequential' => 1,
      'contact_id' => $contactId,
      'period_start_date' => "2015-01-01",
    ));
    $this->assertFalse($result['values'][0]);

    $result = civicrm_api3('HRJobDetails', 'validatedates', array(
      'sequential' => 1,
      'contact_id' => $contactId,
      'period_start_date' => "2016-03-01",
    ));
    $this->assertFalse($result['values'][0]);

    // Finally, we test against valid dates so we expect the result of
    // 'validatedates' call to be TRUE.
    $result = civicrm_api3('HRJobDetails', 'validatedates', array(
      'sequential' => 1,
      'contact_id' => $contactId,
      'period_start_date' => "2015-01-01",
      'period_end_date' => "2015-05-31",
    ));
    $this->assertTrue($result['values'][0]);

    $result = civicrm_api3('HRJobDetails', 'validatedates', array(
      'sequential' => 1,
      'contact_id' => $contactId,
      'period_start_date' => "2016-01-21",
      'period_end_date' => "2016-01-31",
    ));
    $this->assertTrue($result['values'][0]);
  }

  protected function _createContact() {
    $contactResult = civicrm_api3('Contact', 'create', array(
      'sequential' => 1,
      'contact_type' => "Individual",
      'first_name' => "Test Contact First Name",
      'last_name' => "Test Contact Last Name",
      'custom_100003' => "testcontact@testemail8624.net",
      'display_name' => "Test Contact Display Name",
    ));
    $this->assertEquals(0, $contactResult['is_error']);
    return $contactResult['id'];
  }

  /**
   * Create two test Job Contracts for given $contactId Contact. First one has
   * it's 'period_start_date' and 'period_end_date' defined. Second one
   * has only 'period_start_date' defined.
   *
   * @param int $contactId
   * @return boolean
   */
  protected function _createJobContracts($contactId) {
    $jobContract1Result = civicrm_api3('HRJobContract', 'create', array(
      'sequential' => 1,
      'contact_id' => $contactId,
    ));
    $this->assertEquals(0, $jobContract1Result['is_error']);
    $jobDetails1Result = civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'jobcontract_id' => $jobContract1Result['id'],
      'period_start_date' => '2016-01-01',
      'period_end_date' => '2016-01-10',
    ));
    $this->assertEquals(0, $jobDetails1Result['is_error']);
    $jobContract2Result = civicrm_api3('HRJobContract', 'create', array(
      'sequential' => 1,
      'contact_id' => $contactId,
    ));
    $this->assertEquals(0, $jobContract2Result['is_error']);
    $jobDetails2Result = civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'jobcontract_id' => $jobContract2Result['id'],
      'period_start_date' => '2016-02-01',
    ));
    $this->assertEquals(0, $jobDetails2Result['is_error']);
    return TRUE;
  }
}
