<?php

trait CRM_HRLeaveAndAbsences_LeavePeriodEntitlementHelpersTrait {

  /**
   * Creates a mock to be used on tests for the geBalance() method.
   *
   * For these, we mock the getStartAndEndDates() method, so we don't need an
   * actual AbsencePeriod record on the database.
   *
   * @return CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement
   */
  public function createLeavePeriodEntitlementMockForBalanceTests() {
    $periodEntitlement = $this->getMockBuilder(CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement::class)
                              ->setMethods(['getStartAndEndDates'])
                              ->getMock();

    $periodEntitlement->expects($this->any())
                      ->method('getStartAndEndDates')
                      ->will($this->returnValue([[
                        'start_date' => date('Y-01-01'),
                        'end_date' => date('Y-12-31')
                      ]]));

    $periodEntitlement->id = 1;
    $periodEntitlement->type_id = 1;
    $periodEntitlement->period_id = 1;
    $periodEntitlement->contact_id = 1;

    return $periodEntitlement;
  }
}
