<?php

use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;

class CRM_HRLeaveAndAbsences_Service_LeavePeriodEntitlement {

  /**
   * This method uses getEntitlementsForContacts method of the
   * LeavePeriodEntitlement BAO to return a list of entitlements
   * for the given Contacts during the given during the given
   * Absence Period for the given Absence Type
   *
   * @param array $contactIDs
   * @param int $absencePeriodID
   * @param int $absenceTypeID
   *
   * @return array
   */
  public function getEntitlementsForContacts($contactIDs, $absencePeriodID, $absenceTypeID) {
    return LeavePeriodEntitlement::getEntitlementsForContacts($contactIDs, $absencePeriodID, $absenceTypeID);
  }
}
