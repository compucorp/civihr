<?php

/**
 * This is the Queue Task which will be executed by the whenever the
 * EntitlementRecalculation queue is processed.
 *
 * Basically, it fetches the period ID from the queue item and delegates the
 * job to the recalculateAbsenceEntitlementsForPeriod method of the
 * HRAbsenceEntitlement BAO
 */
class CRM_HRAbsence_Queue_Task_RecalculateContactsEntitlementForPeriod {

  public function run(CRM_Queue_TaskContext $ctx, $periodID) {
    CRM_HRAbsence_BAO_HRAbsenceEntitlement::recalculateAbsenceEntitlementsForPeriod($periodID);

    return true;
  }

}
