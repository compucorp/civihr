<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;

trait CRM_HRLeaveAndAbsences_PublicHolidayHelpersTrait {
  
  private function instantiatePublicHoliday($date) {
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = CRM_Utils_Date::processDate($date);

    return $publicHoliday;
  }
}
