<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_Hrjobcontract_BAO_HRJobDetailsTest
 *
 * @group headless
 */
class CRM_Hrjobcontract_BAO_HRJobDetailsTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface,
  TransactionalInterface {

  use HRJobContractTestTrait;

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  function setUp() {
    $upgrader = CRM_Hrjobcontract_Upgrader::instance();
    $upgrader->install();
  }

  function testValidateDatesWithDifferentDateSets() {
    // Create test Contact.
    $contactParams = array("first_name" => "chrollo", "last_name" => "lucilfer");
    $contactID =  $this->createContact($contactParams);

    // Create Two test Job Contracts.
    $this->createJobContract($contactID, '2016-01-01', '2016-01-10');
    $this->createJobContract($contactID, '2016-02-01');

    // Set of tests against invalid dates period (there are Job Contracts
    // already with period dates overlapping.

    $sampleFailedDates = [
      ['period_start_date' => '2016-01-01', 'period_end_date'=> '2016-10-10'],
      ['period_start_date' => '2016-01-03', 'period_end_date'=> '2016-01-05'],
      ['period_start_date' => '2016-01-01', 'period_end_date'=> '2016-01-02'],
      ['period_start_date' => '2016-01-31', 'period_end_date'=> '2016-02-01'],
      ['period_start_date' => '2016-02-10', 'period_end_date'=> '2016-02-20'],
      ['period_start_date' => '2015-01-01'],
      ['period_start_date' => '2016-03-01'],
    ];

    foreach ($sampleFailedDates as $datePair)  {
      $params = ['contact_id' => $contactID, 'period_start_date' => $datePair['period_start_date']];
      if (!empty($datePair['period_end_date']))  {
        $params['period_end_date'] = $datePair['period_end_date'];
      }

      $result = CRM_Hrjobcontract_BAO_HRJobDetails::validateDates($params);

      $this->assertFalse($result['success']);
    }

    // Now we test against valid dates so we expect the result of
    // 'validateDates' call to be TRUE.

    $sampleCorrectDates = [
      ['period_start_date' => '2015-01-01', 'period_end_date'=> '2015-05-31'],
      ['period_start_date' => '2016-01-21', 'period_end_date'=> '2016-01-31'],
    ];

    foreach ($sampleCorrectDates as $datePair)  {
      $params = ['contact_id' => $contactID, 'period_start_date' => $datePair['period_start_date']];
      if (!empty($datePair['period_end_date']))  {
        $params['period_end_date'] = $datePair['period_end_date'];
      }

      $result = CRM_Hrjobcontract_BAO_HRJobDetails::validateDates($params);

      $this->assertTrue($result['success']);
    }
  }
}
