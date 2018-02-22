<?php

/**
 * Class CRM_HRSampleData_Importer_AbsenceTypeTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_AbsenceTypeTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testProcess() {

    $absenceType = $this->apiGet('AbsenceType', ['title' => '2016']);
    $this->assertEmpty($absenceType);

    $this->rows[] = [
      1,
      'Annual Leave',
      1,
      '#151D2C',
      1,
      0,
      3,
      0,
      1,
      20.00,
      1,
      1,
      0,
      0,
      1,
      5.00,
      12,
      2,
      0,
      1
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_AbsenceType', $this->rows);

    $absenceType = $this->apiGet('AbsenceType', ['type' => 'Annual Leave']);

    foreach($this->rows[0] as $index => $fieldName) {
      // ID is just a placeholder and it will be changed once inserted into the
      // database, so we ignore it here
      if($fieldName == 'id') {
        continue;
      }

      $this->assertEquals(
        $this->rows[1][$index],
        $absenceType[$fieldName],
        "The value of {$fieldName} was expected to be {$this->rows[1][$index]}, but it is {$absenceType[$fieldName]}"
      );
    }
  }

  private function importHeadersFixture() {
    return [
      'id',
      'title',
      'weight',
      'color',
      'is_default',
      'is_reserved',
      'allow_request_cancelation',
      'allow_overuse',
      'must_take_public_holiday_as_leave',
      'default_entitlement',
      'add_public_holiday_to_entitlement',
      'is_active',
      'allow_accruals_request',
      'allow_accrue_in_the_past',
      'allow_carry_forward',
      'max_number_of_days_to_carry_forward',
      'carry_forward_expiration_duration',
      'carry_forward_expiration_unit',
      'is_sick',
      'calculation_unit'
    ];
  }

}
