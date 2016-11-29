<?php

/**
 * Class api_v3_LeaveRequestTest
 *
 * @group headless
 */
class api_v3_LeaveRequestTest extends BaseHeadlessTest {

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id, period_id
   */
  public function testGetBalanceChangeByAbsenceTypeShouldNotAllowParamsWithoutContactIDAndPeriodID() {
    civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', []);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id
   */
  public function testGetBalanceChangeByAbsenceTypeShouldNotAllowParamsWithoutContactID() {
    civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'period_id' => 1
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: period_id
   */
  public function testGetBalanceChangeByAbsenceTypeShouldNotAllowParamsWithoutPeriodID() {
    civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => 1
    ]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage The statuses parameter only supports the IN operator
   *
   * @dataProvider invalidGetBalanceChangeByAbsenceTypeStatusesOperators
   */
  public function testGetBalanceChangeByAbsenceTypeShouldOnlyAllowTheINOperator($operator) {
    civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => 1,
      'period_id' => 1,
      'statuses' => [$operator => [1]]
    ]);
  }

  public function testGetBalanceChangeByAbsenceTypeDoesNotThrowAnErrorWhenUsingTheEqualsOperatorForStatuses() {
    $values = civicrm_api3('LeaveRequest', 'getbalancechangebyabsencetype', [
      'contact_id' => 1,
      'period_id' => 1,
      'statuses' => 1
    ]);

    $this->assertEquals(0, $values['is_error']);
  }

  public function invalidGetBalanceChangeByAbsenceTypeStatusesOperators() {
    return [
      ['>'],
      ['>='],
      ['<='],
      ['<'],
      ['<>'],
      ['!='],
      ['BETWEEN'],
      ['NOT BETWEEN'],
      ['LIKE'],
      ['NOT LIKE'],
      ['NOT IN'],
      ['IS NULL'],
      ['IS NOT NULL'],
    ];
  }
}
