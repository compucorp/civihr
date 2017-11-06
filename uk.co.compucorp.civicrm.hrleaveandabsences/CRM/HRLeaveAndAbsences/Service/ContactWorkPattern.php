<?php

use CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern as ContactWorkPattern;

/**
 * Class CRM_HRLeaveAndAbsences_Service_ContactWorkPattern
 */
class CRM_HRLeaveAndAbsences_Service_ContactWorkPattern {


  /**
   * Returns the WorkDay information for a given date for the
   * contact based on the Contact's Work pattern.
   *
   * Returns null when no work day can be found for the contact,
   * either due to the absence of a default work pattern or a
   * custom work pattern for the contact or even due to the fact
   * that the contact has no contract.
   *
   * @param int $contactID
   * @param \DateTime $date
   *
   * @return array|null
   */
  public function getContactWorkDayForDate($contactID, DateTime $date) {
    $workPattern = ContactWorkPattern::getWorkPattern($contactID, $date);
    $startDate = ContactWorkPattern::getStartDate($contactID, $date);

    if(!$workPattern->id || !$startDate) {
      return null;
    }

    return $workPattern->getWorkDayForDate($date, $startDate);
  }
}
