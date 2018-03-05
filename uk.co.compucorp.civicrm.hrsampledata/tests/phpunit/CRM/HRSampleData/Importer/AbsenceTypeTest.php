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
    $absenceType = $this->apiGet('AbsenceType', ['title' => 'Annual Leave']);
    $this->assertEmpty($absenceType);

    $this->rows[] = [
      1,
      'Annual Leave',
      1,
      '#151D2C',
      1,
      0,
      5,
      3,
      0,
      1,
      20.00,
      1,
      1,
      1,
      0,
      3,
      1,
      1,
      1,
      5.00,
      12,
      2,
      0,
      1,
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_AbsenceType', $this->rows);

    $absenceType = $this->apiGet('AbsenceType', ['type' => 'Annual Leave']);

    $fieldsToIgnore = ['id'];
    $this->assertEntityEqualsToRows($this->rows, $absenceType, $fieldsToIgnore);
  }

  private function importHeadersFixture() {
    return [
      'id',
      'title',
      'weight',
      'color',
      'is_default',
      'is_reserved',
      'max_consecutive_leave_days',
      'allow_request_cancelation',
      'allow_overuse',
      'must_take_public_holiday_as_leave',
      'default_entitlement',
      'add_public_holiday_to_entitlement',
      'is_active',
      'allow_accruals_request',
      'allow_accrue_in_the_past',
      'max_leave_accrual',
      'accrual_expiration_duration',
      'accrual_expiration_unit',
      'allow_carry_forward',
      'max_number_of_days_to_carry_forward',
      'carry_forward_expiration_duration',
      'carry_forward_expiration_unit',
      'is_sick',
      'calculation_unit'
    ];
  }

}
