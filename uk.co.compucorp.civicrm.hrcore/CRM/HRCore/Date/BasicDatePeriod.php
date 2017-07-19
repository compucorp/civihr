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
   * @var \DateTime
   *   The start date
   */
  private $startDate;

  /**
   * @var \DateTime
   *   The end date
   */
  private $endDate;
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
    $this->startDate = $start;
    $this->endDate = clone $end;

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

  /**
   * This method will adjust the date range given by $startDate and $endDate
   * to be inside this period date range.
   *
   * If the given $startDate is less than the period start date, it will be
   * changed to be equals the period start date. If the given $endDate is greater
   * than the period end date, it will be changed to be equals to the period
   * end date.
   *
   * Example:
   * Period start date: 2016-01-01
   * Period end date: 2016-12-31
   * $startDate: 2015-10-01
   * $endDate: 2016-07-01
   *
   * Adjusted values:
   * $startDate: 2016-01-01 (Adjusted to be equals to the period start date)
   * $endDate: 2016-07-01 (Not adjusted since it's less then the period end date)
   *
   * @param string $startDate
   *    A date any in any format acceptable by the DateTimeImmutable constructor
   * @param string $endDate
   *    A date any in any format acceptable by the DateTimeImmutable constructor
   *
   * @return self
   *  A BasicDatePeriod object with having the adjusted dates
   *  as its start and end date property.
   */
  public function adjustDatesToMatchPeriodDates($startDate, $endDate) {
    if (new DateTime($startDate) < $this->startDate) {
      $startDate = $this->startDate;
    }

    if (new DateTime($endDate) > $this->endDate) {
      $endDate = $this->endDate;
    }

    return new self($startDate, $endDate);
  }

  /**
   * Returns the period start date
   *
   * @return DateTime
   */
  public function getStartDate() {
    return $this->startDate;
  }

  /**
   * Returns the period end date
   *
   * @return DateTime
   */
  public function getEndDate() {
    return $this->endDate;
  }
}
