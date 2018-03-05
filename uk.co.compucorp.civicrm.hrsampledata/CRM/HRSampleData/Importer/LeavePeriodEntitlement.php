<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;

/**
 * Class CRM_HRSampleData_Importer_LeavePeriodEntitlement
 */
class CRM_HRSampleData_Importer_LeavePeriodEntitlement extends CRM_HRSampleData_CSVImporterVisitor {

  public function __construct() {
    $this->removeAllLeavePeriodEntitlementsWithTheirBalances();
  }

  /**
   * Imports Leave Requests and creates their balance changes
   *
   * @param array $row
   */
  protected function importRecord(array $row) {
    $row['type_id'] = $this->getDataMapping('absence_type_mapping', $row['type_id']);
    $row['period_id'] = $this->getDataMapping('absence_period_mapping', $row['period_id']);
    $row['contact_id'] = $this->getDataMapping('contact_mapping', $row['contact_id']);

    $leaveAmount = (float)$this->unsetArrayElement($row, 'leave_amount');
    $broughtForwardAmount = (float)$this->unsetArrayElement($row, 'brought_forward_amount');
    $publicHolidayAmount = (float)$this->unsetArrayElement($row, 'public_holiday_amount');
    $overriddenAmount = (float)$this->unsetArrayElement($row, 'overridden_amount');

    $periodEntitlement = $this->callAPI('LeavePeriodEntitlement', 'create', $row);

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id', 'validate'));

    $this->createEntitlementBalanceChange(
      $periodEntitlement['id'],
      $balanceChangeTypes['leave'],
      $leaveAmount
    );

    if ($broughtForwardAmount) {
      $this->createEntitlementBalanceChange(
        $periodEntitlement['id'],
        $balanceChangeTypes['brought_forward'],
        $broughtForwardAmount
      );
    }

    if ($publicHolidayAmount) {
      $this->createEntitlementBalanceChange(
        $periodEntitlement['id'],
        $balanceChangeTypes['public_holiday'],
        $publicHolidayAmount
      );
    }

    if ($row['overridden']) {
      $this->createEntitlementBalanceChange(
        $periodEntitlement['id'],
        $balanceChangeTypes['overridden'],
        $overriddenAmount
      );
    }
  }

  /**
   * Creates a new Leave Balance Change for the Leave Period Entitlement with
   * the given ID
   *
   * @param int $periodEntitlementID
   * @param string $type
   * @param float $amount
   */
  private function createEntitlementBalanceChange($periodEntitlementID, $type, $amount) {
    $this->callAPI('LeaveBalanceChange', 'create', [
      'amount' => $amount,
      'type_id' => $type,
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'source_id' => $periodEntitlementID
    ]);
  }

  /**
   * Removes existing Leave Period Entitlements and their Balance Changes.
   */
  private function removeAllLeavePeriodEntitlementsWithTheirBalances() {
    $this->callAPI('LeavePeriodEntitlement', 'get', [
      'api.LeavePeriodEntitlement.delete' => [ 'id' => '$value.id' ],
    ]);

    $this->callAPI('LeaveBalanceChange', 'get', [
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'api.LeaveBalanceChange.delete' => [ 'id' => '$value.id']
    ]);
  }
}
