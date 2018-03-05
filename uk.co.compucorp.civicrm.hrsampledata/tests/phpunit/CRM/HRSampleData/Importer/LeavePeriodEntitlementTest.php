<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;

/**
 * Class CRM_HRSampleData_Importer_LeavePeriodEntitlementTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_LeavePeriodEntitlementTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  private $absenceType;
  private $absencePeriod;
  private $mapping;
  private $contactID;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
    $this->absenceType = AbsenceTypeFabricator::fabricate();
    $this->absencePeriod = AbsencePeriodFabricator::fabricate();
    $this->contactID = 1;

    // The importer uses a mapping to convert the contact and absence type ids
    // in the csv file to the actual ids after the contact were imported, so we
    // create a fake mapping here that maps a contact and an absence type to
    // themselves
    $this->mapping = [
      [ 'contact_mapping', $this->contactID, $this->contactID ],
      [ 'absence_type_mapping', $this->absenceType->id, $this->absenceType->id ],
      [ 'absence_period_mapping', $this->absencePeriod->id, $this->absencePeriod->id ],
    ];
  }

  public function testProcess() {
    $periodEntitlement = $this->apiGet('LeavePeriodEntitlement');
    $this->assertEmpty($periodEntitlement);

    $params = [
      'overridden' => true,
      'leave_amount' => 20,
      'brought_forward_amount' => 5,
      'public_holiday_amount' => 2,
      'overridden_amount' => 3
    ];
    $this->rows[] = $this->getLeavePeriodEntitlementRow($params);

    $this->runProcessor('CRM_HRSampleData_Importer_LeavePeriodEntitlement', $this->rows, $this->mapping);

    $periodEntitlement = $this->apiGet('LeavePeriodEntitlement');

    $fieldsToIgnore = [
      'leave_amount',
      'brought_forward_amount',
      'public_holiday_amount',
      'overridden_amount'
    ];
    $this->assertEntityEqualsToRows($this->rows, $periodEntitlement, $fieldsToIgnore);

    $balanceChanges = civicrm_api3('LeaveBalanceChange', 'get', [
      'source_id' => $periodEntitlement['id'],
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'options' => ['sort' => 'type_id'],
      'sequential' => 1,
    ])['values'];

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id', 'validate'));
    $this->assertCount(4, $balanceChanges);
    $this->assertEquals($balanceChangeTypes['leave'], $balanceChanges[0]['type_id']);
    $this->assertEquals($params['leave_amount'], $balanceChanges[0]['amount']);

    $this->assertEquals($balanceChangeTypes['brought_forward'], $balanceChanges[1]['type_id']);
    $this->assertEquals($params['brought_forward_amount'], $balanceChanges[1]['amount']);

    $this->assertEquals($balanceChangeTypes['public_holiday'], $balanceChanges[2]['type_id']);
    $this->assertEquals($params['public_holiday_amount'], $balanceChanges[2]['amount']);

    $this->assertEquals($balanceChangeTypes['overridden'], $balanceChanges[3]['type_id']);
    $this->assertEquals($params['overridden_amount'], $balanceChanges[3]['amount']);
  }

  public function testProcessBroughtForwardBalanceChangeIsNotCreatedIfItsZero() {
    $periodEntitlement = $this->apiGet('LeavePeriodEntitlement');
    $this->assertEmpty($periodEntitlement);

    $params = [
      'overridden' => true,
      'leave_amount' => 20,
      'brought_forward_amount' => 0,
      'public_holiday_amount' => 2,
      'overridden_amount' => 3
    ];
    $this->rows[] = $this->getLeavePeriodEntitlementRow($params);

    $this->runProcessor('CRM_HRSampleData_Importer_LeavePeriodEntitlement', $this->rows, $this->mapping);

    $periodEntitlement = $this->apiGet('LeavePeriodEntitlement');

    $balanceChanges = civicrm_api3('LeaveBalanceChange', 'get', [
      'source_id' => $periodEntitlement['id'],
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'options' => ['sort' => 'type_id'],
      'sequential' => 1,
    ])['values'];

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id', 'validate'));
    $this->assertCount(3, $balanceChanges);
    $this->assertEquals($balanceChangeTypes['leave'], $balanceChanges[0]['type_id']);
    $this->assertEquals($params['leave_amount'], $balanceChanges[0]['amount']);

    $this->assertEquals($balanceChangeTypes['public_holiday'], $balanceChanges[1]['type_id']);
    $this->assertEquals($params['public_holiday_amount'], $balanceChanges[1]['amount']);

    $this->assertEquals($balanceChangeTypes['overridden'], $balanceChanges[2]['type_id']);
    $this->assertEquals($params['overridden_amount'], $balanceChanges[2]['amount']);
  }

  public function testProcessPublicHolidayBalanceChangeIsNotCreatedIfItsZero() {
    $periodEntitlement = $this->apiGet('LeavePeriodEntitlement');
    $this->assertEmpty($periodEntitlement);

    $params = [
      'overridden' => true,
      'leave_amount' => 20,
      'brought_forward_amount' => 0,
      'public_holiday_amount' => 0,
      'overridden_amount' => 3
    ];
    $this->rows[] = $this->getLeavePeriodEntitlementRow($params);

    $this->runProcessor('CRM_HRSampleData_Importer_LeavePeriodEntitlement', $this->rows, $this->mapping);

    $periodEntitlement = $this->apiGet('LeavePeriodEntitlement');

    $balanceChanges = civicrm_api3('LeaveBalanceChange', 'get', [
      'source_id' => $periodEntitlement['id'],
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'options' => ['sort' => 'type_id'],
      'sequential' => 1,
    ])['values'];

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id', 'validate'));
    $this->assertCount(2, $balanceChanges);
    $this->assertEquals($balanceChangeTypes['leave'], $balanceChanges[0]['type_id']);
    $this->assertEquals($params['leave_amount'], $balanceChanges[0]['amount']);

    $this->assertEquals($balanceChangeTypes['overridden'], $balanceChanges[1]['type_id']);
    $this->assertEquals($params['overridden_amount'], $balanceChanges[1]['amount']);
  }

  public function testProcessOverriddenBalanceChangeIsNotCreatedIfOverriddenIsFalse() {
    $periodEntitlement = $this->apiGet('LeavePeriodEntitlement');
    $this->assertEmpty($periodEntitlement);

    $params = [
      'overridden' => false,
      'leave_amount' => 20,
      'brought_forward_amount' => 0,
      'public_holiday_amount' => 0,
      'overridden_amount' => 2
    ];
    $this->rows[] = $this->getLeavePeriodEntitlementRow($params);

    $this->runProcessor('CRM_HRSampleData_Importer_LeavePeriodEntitlement', $this->rows, $this->mapping);

    $periodEntitlement = $this->apiGet('LeavePeriodEntitlement');

    $balanceChanges = civicrm_api3('LeaveBalanceChange', 'get', [
      'source_id' => $periodEntitlement['id'],
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'options' => ['sort' => 'type_id'],
      'sequential' => 1,
    ])['values'];

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id', 'validate'));
    $this->assertCount(1, $balanceChanges);
    $this->assertEquals($balanceChangeTypes['leave'], $balanceChanges[0]['type_id']);
    $this->assertEquals($params['leave_amount'], $balanceChanges[0]['amount']);
  }

  public function testProcessOverriddenBalanceChangeIsCreatedIfOverriddenIsTrueAndAmountIsZero() {
    $periodEntitlement = $this->apiGet('LeavePeriodEntitlement');
    $this->assertEmpty($periodEntitlement);

    $params = [
      'overridden' => true,
      'leave_amount' => 20,
      'brought_forward_amount' => 0,
      'public_holiday_amount' => 0,
      'overridden_amount' => 0
    ];
    $this->rows[] = $this->getLeavePeriodEntitlementRow($params);

    $this->runProcessor('CRM_HRSampleData_Importer_LeavePeriodEntitlement', $this->rows, $this->mapping);

    $periodEntitlement = $this->apiGet('LeavePeriodEntitlement');

    $balanceChanges = civicrm_api3('LeaveBalanceChange', 'get', [
      'source_id' => $periodEntitlement['id'],
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'options' => ['sort' => 'type_id'],
      'sequential' => 1,
    ])['values'];

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id', 'validate'));
    $this->assertCount(2, $balanceChanges);
    $this->assertEquals($balanceChangeTypes['leave'], $balanceChanges[0]['type_id']);
    $this->assertEquals($params['leave_amount'], $balanceChanges[0]['amount']);

    $this->assertEquals($balanceChangeTypes['overridden'], $balanceChanges[1]['type_id']);
    $this->assertEquals($params['overridden_amount'], $balanceChanges[1]['amount']);
  }

  private function getLeavePeriodEntitlementRow($params = []) {
    $defaultParams = [
      'period_id' => $this->absencePeriod->id,
      'type_id' => $this->absenceType->id,
      'contact_id' => $this->contactID,
      'overridden' => false,
      'created_date' => '2017-01-20 00:00:00',
      'leave_amount' => 0,
      'brought_forward_amount' => 0,
      'public_holiday_amount' => 0,
      'overridden_amount' => 0
    ];

    return array_values(array_merge($defaultParams, $params));
  }

  private function importHeadersFixture() {
    return [
      'period_id',
      'type_id',
      'contact_id',
      'overridden',
      'created_date',
      'leave_amount',
      'brought_forward_amount',
      'public_holiday_amount',
      'overridden_amount'
    ];
  }

}
