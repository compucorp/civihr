<?php

/**
 * A simple validator with a single isValid method that checks
 * if a timezone is valid.
 */
class CRM_HRLeaveAndAbsences_Validator_TimeZone {

  /**
   * Check if the given timezone is valid.
   *
   * Please check http://php.net/manual/en/timezones.php for a
   * list of valid time zones.
   *
   * @param string $timeZone
   *
   * @return bool
   */
  public static function isValid($timeZone) {
    $timeZoneList = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

    if (!in_array($timeZone, $timeZoneList)) {
      return FALSE;
    }

    return TRUE;
  }
}
