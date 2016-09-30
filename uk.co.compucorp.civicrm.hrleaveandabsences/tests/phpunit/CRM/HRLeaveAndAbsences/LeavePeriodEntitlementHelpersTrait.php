<?php

trait CRM_HRLeaveAndAbsences_LeavePeriodEntitlementHelpersTrait {

  /**
   * Creates a mock to be used on tests for the geBalance() method.
   *
   * For these, we mock the getStartAndEndDates() method, so we don't need an
   * actual AbsencePeriod record on the database and also the
   * getContactIDFromContract() method so we don't need actual contract and
   * contact records.
   *
   * @return CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement
   */
  public function createLeavePeriodEntitlementMockForBalanceTests() {
    $periodEntitlement = $this->getMockBuilder(CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement::class)
                              ->setMethods([
                                'getContactIDFromContract',
                                'getStartAndEndDates'
                              ])
                              ->getMock();

    $periodEntitlement->expects($this->any())
                      ->method('getContactIDFromContract')
                      ->will($this->returnValue(1));

    $periodEntitlement->expects($this->any())
                      ->method('getStartAndEndDates')
                      ->will($this->returnValue([
                        date('Y-01-01'),
                        date('Y-12-31')
                      ]));

    $periodEntitlement->id = 1;
    $periodEntitlement->type_id = 1;
    $periodEntitlement->period_id = 1;

    return $periodEntitlement;
  }
}
