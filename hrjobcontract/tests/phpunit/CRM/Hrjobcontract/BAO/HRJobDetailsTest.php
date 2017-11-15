<?php

/**
 * Class CRM_Hrjobcontract_BAO_HRJobDetailsTest
 *
 * @group headless
 */
class CRM_Hrjobcontract_BAO_HRJobDetailsTest extends CRM_Hrjobcontract_Test_BaseHeadlessTest {

  use HRJobContractTestTrait;

  private $contactID;

  public function setUp()  {
    // Create test Contact.
    $contactParams = ["first_name" => "chrollo", "last_name" => "lucilfer"];
    $this->contactID =  $this->createContact($contactParams);
  }

  /**
   * @param $start_date
   * @param $end_date
   *
   * @dataProvider invalidDatesProvider
   */
  public function testValidateDatesWithInvalidDates($start_date, $end_date) {

    // Create Two test Job Contracts.
    $this->createJobContract($this->contactID, '2016-01-01', '2016-01-10');
    $this->createJobContract($this->contactID, '2016-02-01');


    $params = ['contact_id' => $this->contactID, 'period_start_date' => $start_date];
    if (!empty($end_date))  {
      $params['period_end_date'] = $end_date;
    }

    $result = CRM_Hrjobcontract_BAO_HRJobDetails::validateDates($params);

    $this->assertFalse($result['success']);
  }

  /**
   * @param $start_date
   * @param $end_date
   *
   * @dataProvider validDatesProvider
   */
  public function testValidateDatesWithValidDates($start_date, $end_date) {

    // Create Two test Job Contracts.
    $this->createJobContract($this->contactID, '2016-01-01', '2016-01-10');
    $this->createJobContract($this->contactID, '2016-02-01');

    $params = ['contact_id' => $this->contactID, 'period_start_date' => $start_date];
    if (!empty($end_date))  {
      $params['period_end_date'] = $end_date;
    }

    $result = CRM_Hrjobcontract_BAO_HRJobDetails::validateDates($params);

    $this->assertTrue($result['success']);
  }

  public function invalidDatesProvider() {
    return [
      ['2016-01-01', '2016-10-10'],
      ['2016-01-03', '2016-01-05'],
      ['2016-01-01', '2016-01-02'],
      ['2016-01-31', '2016-02-01'],
      ['2016-02-10', '2016-02-20'],
      ['2015-01-01', null],
      ['2016-03-01', null],
    ];
  }

  public function validDatesProvider() {
    return [
      ['2015-01-01', '2015-05-31'],
      ['2016-01-21', '2016-01-31'],
    ];
  }
}
