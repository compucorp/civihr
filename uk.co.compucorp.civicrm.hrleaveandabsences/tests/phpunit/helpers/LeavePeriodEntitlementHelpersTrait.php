<?php

trait CRM_HRLeaveAndAbsences_LeavePeriodEntitlementHelpersTrait {

  /**
   * Creates a mock to be used on tests for the geBalance() method.
   *
   * For these, we mock the getStartAndEndDates() method, so we don't need an
   * actual AbsencePeriod record on the database. Optionally, you can pass the
   * start and end dates returned by this method.
   *
   * @param \DateTime $startDate
   * @param \DateTime $endDate
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement
   */
  public function createLeavePeriodEntitlementMockForBalanceTests(DateTime $startDate = null, DateTime $endDate = null) {
    $periodEntitlement = $this->getMockBuilder(CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement::class)
                              ->setMethods(['getStartAndEndDates'])
                              ->getMock();

    if(!$startDate) {
      $startDate = new DateTime(date('Y-01-01'));
    }

    if(!$endDate) {
      $endDate = clone $startDate;
      $endDate->modify('+1 year')->modify('-1 day');
    }

    $periodEntitlement->expects($this->any())
                      ->method('getStartAndEndDates')
                      ->will($this->returnValue([[
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d')
                      ]]));

    $periodEntitlement->id = 1;
    $periodEntitlement->type_id = 1;
    $periodEntitlement->period_id = 1;
    $periodEntitlement->contact_id = 1;

    return $periodEntitlement;
  }
}
