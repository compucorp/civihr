<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContractRevision as HRJobContractRevisionFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobHealth as HRJobHealthFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobHour as HRJobHourFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobLeave as HRJobLeaveFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobPay as HRJobPayFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobPension as HRJobPensionFabricator;

/**
 * Class CRM_Hrjobcontract_BAO_HRJobContractRevisionTest
 *
 * @group headless
 */
class CRM_Hrjobcontract_BAO_HRJobContractRevisionTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface,
  TransactionalInterface {

  use HRJobContractTestTrait;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->installMe(__DIR__)
      ->apply();
  }

  /**
   * Tests that structure of response for CRM_Hrjobcontract_BAO_HRJobContractRevision::fullDetails()
   * has all required sets of values.
   */
  function testFullDetailsResponse() {
    $contact = ContactFabricator::fabricate();
    $contract = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => '2015-01-01']
    );

    HRJobContractRevisionFabricator::fabricate(['jobcontract_id' => $contract['id']]);
    HRJobHealthFabricator::fabricate(['jobcontract_id' => $contract['id']]);
    HRJobHourFabricator::fabricate(['jobcontract_id' => $contract['id']]);
    HRJobLeaveFabricator::fabricate(['jobcontract_id' => $contract['id']]);
    HRJobPayFabricator::fabricate(['jobcontract_id' => $contract['id']]);

    $pensionDetails = HRJobPensionFabricator::fabricate([
      'jobcontract_id' => $contract['id'],
      'pension_type' => 'Pension Provider ' . mt_rand(1000, 9000)
    ]);

    $currentRevision = $this->getContractCurrentRevision($contract['id']);
    $fullDetails = CRM_Hrjobcontract_BAO_HRJobContractRevision::fullDetails($currentRevision);

    foreach (['details', 'health', 'hour', 'leave', 'pay', 'pension'] as $entitiy ) {
      $this->assertArrayHasKey($entitiy, $fullDetails);
    }

    $this->assertEquals($pensionDetails['pension_type'], $fullDetails['pension']['pension_type']);

    $this->assertInternalType("array", $fullDetails['leave']);
  }

  /**
   * Obtains current revision for given contract.
   * 
   * @param int $jobContractID
   *   ID of job contract
   * 
   * @return array
   *   Details for current revision of job contract
   */
  private function getContractCurrentRevision($jobContractID) {
    $result = civicrm_api3('HRJobContractRevision', 'getcurrentrevision', [
      'debug' => 1,
      'sequential' => 1,
      'jobcontract_id' => $jobContractID
    ]);
    return $result['values'];
  }

  /**
   * Job Contract Revision's flow testing.
   * Create and modify a Revision with several different Job Contract's
   * start dates and Revision's effective dates and test its valididy
   * after each change basing on current time.
   */
  function testHRJobContractRevisionFlow() {
    // Create test data.
    $time = time();
    $testContactId = $this->createTestContactAndGetItsID();
    $testJobContractId = $this->createTestJobContractAndGetItsID($testContactId);

    // Create a future Job Contract Details revision.
    civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'jobcontract_id' => $testJobContractId,
      'title' => "Job Contract Test Title",
      'period_start_date' => date('Y-m-d', strtotime('+2 week', $time)),
      'period_end_date' => date('Y-m-d', strtotime('+4 week', $time)),
    ));
    $details = $this->getJobContractDetails($testJobContractId);
    // Checking a current Details Revision for the Job Contract.
    // It should be the revision which we've just created (as there
    // is no revision with effective date matching today's date then
    // the first found revision will be picked).
    $this->assertEquals("Job Contract Test Title", $details['title']);

    // Edit Job Contract Details revision with changing its start date and title.
    civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'id' => $details['id'],
      'jobcontract_id' => $testJobContractId,
      'jobcontract_revision_id' => $details['jobcontract_revision_id'],
      'title' => "Job Contract Test Title changed",
      'period_start_date' => date('Y-m-d', strtotime('+1 week', $time)),
    ));
    $details = $this->getJobContractDetails($testJobContractId);
    // The same scenario as before - just with changed Job Contract start date
    // to one week less.
    $this->assertEquals("Job Contract Test Title changed", $details['title']);

    // Edit Job Contract Details revision with changing its start date to past
    // and also changing its title.
    civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'id' => $details['id'],
      'jobcontract_id' => $testJobContractId,
      'jobcontract_revision_id' => $details['jobcontract_revision_id'],
      'title' => "Job Contract started two weeks ago",
      'period_start_date' => date('Y-m-d', strtotime('-2 week', $time)),
    ));
    $details = $this->getJobContractDetails($testJobContractId);
    // Now a revision with past effective date is created and it's currently
    // valid basing on today's date so it should be found as current one.
    $this->assertEquals("Job Contract started two weeks ago", $details['title']);

    // Create a new revision of Job Contract with start date a week ago.
    $detailsRevision = civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'title' => "Job Contract started one week ago",
      'period_start_date' => date('Y-m-d', strtotime('-1 week', $time)),
      'jobcontract_id' => $testJobContractId,
    ));
    civicrm_api3('HRJobContractRevision', 'create', array(
      'sequential' => 1,
      'id' => $detailsRevision['values'][0]['jobcontract_revision_id'],
      'effective_date' => date('Y-m-d', strtotime('-1 week', $time)),
    ));
    $details = $this->getJobContractDetails($testJobContractId);
    // Now we created a revision newer than before but still in the past,
    // so the revision should be valid currently.
    $this->assertEquals("Job Contract started one week ago", $details['title']);

    // Create a new revision of Job Contract with start date three days ago.
    $detailsRevision = civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'title' => "Job Contract started three days ago",
      'period_start_date' => date('Y-m-d', strtotime('-3 day', $time)),
      'jobcontract_id' => $testJobContractId,
    ));
    civicrm_api3('HRJobContractRevision', 'create', array(
      'sequential' => 1,
      'id' => $detailsRevision['values'][0]['jobcontract_revision_id'],
      'effective_date' => date('Y-m-d', strtotime('-3 day', $time)),
    ));
    $details = $this->getJobContractDetails($testJobContractId);
    // Same scenario as before, just for testing of creating a new revision
    // between an exsisting ones.
    $this->assertEquals("Job Contract started three days ago", $details['title']);

    // Create a new revision of Job Contract with its effective date in next month.
    $detailsRevision = civicrm_api3('HRJobDetails', 'create', array(
      'sequential' => 1,
      'title' => "Job Contract's revision in the far future",
      'jobcontract_id' => $testJobContractId,
    ));
    civicrm_api3('HRJobContractRevision', 'create', array(
      'sequential' => 1,
      'id' => $detailsRevision['values'][0]['jobcontract_revision_id'],
      'effective_date' => date('Y-m-d', strtotime('+1 month', $time)),
    ));
    $details = $this->getJobContractDetails($testJobContractId);
    // We created a new Details Revision having its effective date in a next month,
    // so still we should get a revision which has its effective date 3 days ago
    // (and is currently valid).
    $this->assertEquals("Job Contract started three days ago", $details['title']);
  }

  /**
   * @dataProvider validateEffectiveDateProvider
   */
  public function testValidateEffectiveDate($effectiveDate, $expected) {
    // Create test Contacts.
    $contactParams = ["first_name" => "chrollo", "last_name" => "lucilfer"];
    $contactID =  $this->createContact($contactParams);

    // Create test Job Contract.
    $this->createJobContract($contactID, '2015-01-01');

    $result = CRM_Hrjobcontract_BAO_HRJobContractRevision::validateEffectiveDate($contactID, $effectiveDate);
    $this->assertEquals($expected, $result['success']);
  }

  public function validateEffectiveDateProvider() {
    return [
      ['2015-01-01', false],
      ['2015-01-02', true],
      ['2015-01-03', true],
    ];
  }

  /**
   * Create a test Contact (Individual).
   *
   * @param string $displayName
   * @throws Exception
   */
  private function createTestContactAndGetItsID($displayName = "Test Contact") {
    $result = civicrm_api3('Contact', 'create', array(
      'sequential' => 1,
      'contact_type' => "Individual",
      'display_name' => $displayName,
    ));
    if (empty($result['id'])) {
      throw new Exception("Cannot create a test Contact.");
    }
    return $result['id'];
  }

  /**
   * Create a test Job Contract.
   *
   * @param int $contactId
   * @throws Exception
   */
  private function createTestJobContractAndGetItsID($contactId) {
    $result = civicrm_api3('HRJobContract', 'create', array(
      'sequential' => 1,
      'contact_id' => $contactId,
    ));
    if (empty($result['id'])) {
      throw new Exception("Cannot create a test Job Contract.");
    }
    return $result['id'];
  }

  /**
   * Return an array containing Job Contract Details entity's array
   * of a Job Contract by given Job Contract ID.
   *
   * @param int $jobContractId
   * @return array
   */
  private function getJobContractDetails($jobContractId) {
    return civicrm_api3('HRJobDetails', 'getsingle', array(
      'sequential' => 1,
      'jobcontract_id' => $jobContractId,
    ));
  }
}
