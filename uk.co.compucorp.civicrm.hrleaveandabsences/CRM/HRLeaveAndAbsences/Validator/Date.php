<?php

/**
 * A simple validator with a single isValid method that checks
 * if a date is valid.
 */
class CRM_HRLeaveAndAbsences_Validator_Date {

  /**
   * Check if the given date is valid according to the give format.
   *
   * If no format is given, then the default YmdHis format will be used.
   *
   * Please check http://php.net/manual/en/datetime.createfromformat.php for a
   * list of valid date formats.
   *
   * @param string $date - The date to be validated
   * @param string $format - The format to check the date against
   *
   * @return bool
   */
  public static function isValid($date, $format = 'YmdHis')
  {
    $dateTime = DateTime::createFromFormat($format, $date);

    // PHP automatically converts some invalid dates to valid ones, like
    // 2016-02-30 to 2016-03-01. That's why we should also check if the
    // returned DateTime object is the same as the given date.
    return $dateTime && $dateTime->format($format) == $date;
  }

}
