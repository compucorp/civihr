<?php

/**
 * This class is a very basic wrapper around the PHP's DatePeriod which fixes
 * a very common problem with it: It automatically includes the end date in the
 * period.
 *
 * Every PHP developer probably tried something like this at least once:
 *
 * $datePeriod = new DatePeriod($starDate, $interval, $endDate);
 * foreach($datePeriod as $date) {
 *   //do something with the dates
 * }
 *
 * But then realized that the $endDate was never returned in the loop.
 *
 * A common pattern for fix this is to add 1 day to the end date and then do the
 * loop. This class does this automatically for you. It also does the assumption
 * that the interval between dates will always be 1 day, so all you need to do
 * is pass the start and end dates.
 */
class CRM_HRCore_Date_BasicDatePeriod implements IteratorAggregate {

  /**
   * @var \DatePeriod
   *   The wrapper DatePeriod instance
   */
  private $datePeriod;

  /**
   * @param mixed $start
   *   Can either be a DateTimeInterface instance of any value acceptable by the
   *   DateTimeImmutable constructor
   * @param mixed $end
   *   Can either be a DateTimeInterface instance of any value acceptable by the
   *   DateTimeImmutable constructor
   */
  public function __construct($start, $end) {
    if($start instanceof DateTimeInterface) {
      $start = new DateTime($start->format('Y-m-d'));
    } else {
      $start = new DateTime($start);
    }

    if($end instanceof DateTimeInterface) {
      $end = new DateTime($end->format('Y-m-d'));
    } else {
      $end = new DateTime($end);
    }
    $end = $end->modify('+1 day');

    $interval = new DateInterval('P1D');

    $this->datePeriod = new DatePeriod($start, $interval, $end);
  }

  /**
   * Implementation of InteratorAggregate.getIterator.
   *
   * Basically, it returns the wrapped DatePeriod instance, which is also a
   * Traversable object.
   *
   * @return \Traversable
   */
  public function getIterator() {
    return $this->datePeriod;
  }
}
