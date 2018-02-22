<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;

/**
 * Class CRM_HRSampleData_Importer_LeavePeriodEntitlement
 */
class CRM_HRSampleData_Importer_LeavePeriodEntitlement extends CRM_HRSampleData_CSVImporterVisitor {

  public function __construct() {
    $this->removeAllLeavePeriodEntitlements();
  }

  /**
   * Imports Leave Requests and creates their balance changes
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

    $this->callAPI('LeaveBalanceChange', 'create', [
      'amount' => $leaveAmount,
      'type_id' => $balanceChangeTypes['leave'],
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'source_id' => $periodEntitlement['id']
    ]);

    if ($broughtForwardAmount) {
      $this->callAPI('LeaveBalanceChange', 'create', [
        'amount' => $broughtForwardAmount,
        'type_id' => $balanceChangeTypes['brought_forward'],
        'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
        'source_id' => $periodEntitlement['id']
      ]);
    }

    if ($publicHolidayAmount) {
      $this->callAPI('LeaveBalanceChange', 'create', [
        'amount' => $publicHolidayAmount,
        'type_id' => $balanceChangeTypes['public_holiday'],
        'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
        'source_id' => $periodEntitlement['id']
      ]);
    }

    if ($row['overridden']) {
      $this->callAPI('LeaveBalanceChange', 'create', [
        'amount' => $overriddenAmount,
        'type_id' => $balanceChangeTypes['overridden'],
        'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
        'source_id' => $periodEntitlement['id']
      ]);
    }
  }

  /**
   * Removes existing Leave Period Entitlements and their Balance Changes.
   */
  private function removeAllLeavePeriodEntitlements() {
    $this->callAPI('LeavePeriodEntitlement', 'get', [
      'api.LeavePeriodEntitlement.delete' => [ 'id' => '$value.id' ],
    ]);

    $this->callAPI('LeaveBalanceChange', 'get', [
      'source_type' => LeaveBalanceChange::SOURCE_ENTITLEMENT,
      'api.LeaveBalanceChange.delete' => [ 'id' => '$value.id']
    ]);
  }
}
