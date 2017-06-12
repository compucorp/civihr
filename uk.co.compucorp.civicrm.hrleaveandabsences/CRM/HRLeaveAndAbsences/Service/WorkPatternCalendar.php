<?php

use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern as ContactWorkPattern;
use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_Service_JobContract as JobContractService;

/**
 * This class calculates a calendar for a Contact and an Absence Period,
 * based on the the contact's work pattern(s).
 *
 * A calendar is just a list of dates with information about its type, according
 * to a work pattern (i.e. if it's a working day, non working day or weekend)
 */
class CRM_HRLeaveAndAbsences_Service_WorkPatternCalendar {

  /**
   * @var int
   */
  private $contactID;

  /**
   * @var \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   */
  private $absencePeriod;

  /**
   * @var array
   *   An array to cache the WorkPattern instances loaded by getWorkPatternById()
   */
  private $workPatternCache;

  public function __construct($contactID, AbsencePeriod $absencePeriod, JobContractService $jobContractService) {
    $this->contactID = $contactID;
    $this->absencePeriod = $absencePeriod;
    $this->jobContractService = $jobContractService;
  }

  /**
   * Returns a list of all the dates on this calendar, on the same format as
   * WorkPattern.getCalendar(). The difference from the method on the WorkPattern,
   * is that this one only returns the dates between the calendar's contact
   * contracts during the calendar's Absence Period. Also, it deals with the
   * possibility of a contact having multiple work patterns during a single
   * Absence Period.
   *
   * @see CRM_HRLeaveAndAbsences_BAO_WorkPattern.getCalendar()
   *
   * @return array
   */
  public function get() {
    $workPatternsPeriods = $this->getWorkPatternsPeriods();

    $calendar = [];
    foreach($workPatternsPeriods as $workPatternPeriod) {
      $workPattern = $this->getWorkPatternById($workPatternPeriod['pattern_id']);
      $calendar = array_merge($calendar, $workPattern->getCalendar(
        $workPatternPeriod['effective_date'],
        $workPatternPeriod['period_start_date'],
        $workPatternPeriod['period_end_date']
      ));
    }

    return $calendar;
  }

  /**
   * Given that a contact might have multiple active Work Patterns during a
   * single Absence Period, this method returns a list of "Work Patterns Periods",
   * which is a list of all the Work Patterns effective for this calendar's
   * contact, during this calendar's Absence Period, together with information
   * of during which dates this pattern was active during that period.
   *
   * This method take into account the contact's contracts during the period,
   * and the returned dates are adjusted to match the contracts dates. That is,
   * even if a contact has an effective work pattern for a given date, the
   * method won't include that pattern unless the contact also has a contract
   * during that date.
   *
   * @return array
   *   An array of Work Patterns, organized according to the dates of the
   *   contacts contracts during the Absence Period. Each entry has:
   *   - pattern_id: The ID of the Work Pattern
   *   - effective_date: The date this work pattern became active for one
   *     specific contract. @see calculateWorkPatternEffectiveDateForContract
   *   - period_start_date: The start date for this work pattern on the period
   *     @see calculateWorkPatternPeriodStartDate
   *   - period_end_date: The end date for this work pattern on the period
   *     @see calculateWorkPatternPeriodEndDate
   *
   *   Given that gaps between contracts might exist and a single work pattern
   *   might cover more than one contract, the returned array might include more
   *   than one entry for the same work pattern (one for each contract covered
   *   by that work pattern), but with different dates.
   */
  private function getWorkPatternsPeriods() {
    $workPatterns = [];
    $contracts = $this->getContractsWithAdjustedDatesForPeriod();

    foreach($contracts as $contract) {
      $contactWorkPatterns = ContactWorkPattern::getAllForPeriod(
        $this->contactID,
        $contract['period_start_date'],
        $contract['period_end_date']
      );

      // If there's no work pattern for this contract, we use the default one
      // for its whole period
      if(empty($contactWorkPatterns)) {
        $this->fabricateDefaultWorkPatternPeriod(
          $contract['period_start_date'],
          $contract['period_end_date'],
          $contract['original_start_date']
        );
        continue;
      }

      foreach($contactWorkPatterns as $contactWorkPattern) {
        $patternStartDate = $this->calculateWorkPatternEffectiveDateForContract($contactWorkPattern, $contract);
        $patternEffectiveDate = $this->calculateWorkPatternPeriodStartDate($contactWorkPattern, $contract);
        $patternEffectiveEndDate = $this->calculateWorkPatternPeriodEndDate($contactWorkPattern, $contract);

        $workPatterns[] = [
          'pattern_id' => (int)$contactWorkPattern->pattern_id,
          'effective_date' => $patternStartDate,
          'period_start_date' => $patternEffectiveDate,
          'period_end_date' => $patternEffectiveEndDate
        ];
      }
    }

    $workPatterns = $this->fillWorkPatternPeriodsLapses($workPatterns);

    return $workPatterns;
  }

  /**
   * Returns all the contracts for this calendar's contact during the calendar's
   * Absence Period.
   *
   * The dates of the contracts will be adjusted to be contained on absence
   * period dates. That is:
   * - If the contract's start date is less than the period start date, than it
   * will be changed to be the period's one
   * - If the contract's end date is greater than the period end date or if it is
   * null, than it will be changed to be the period's one
   *
   * @return array
   */
  private function getContractsWithAdjustedDatesForPeriod() {
    $contracts = $this->jobContractService->getContractsForPeriod(
      new DateTime($this->absencePeriod->start_date),
      new DateTime($this->absencePeriod->end_date),
      [$this->contactID]
    );

    foreach($contracts as $i => $contract) {
      if(empty($contract['period_end_date'])) {
        $contract['period_end_date'] = $this->absencePeriod->end_date;
      }

      list($startDate, $endDate) = $this->absencePeriod->adjustDatesToMatchPeriodDates(
        $contract['period_start_date'],
        $contract['period_end_date']
      );

      // We need the original date in order to calculate the effective date for
      // the work patterns, so we keep it here in this "fake" field
      $contract['original_start_date'] = new DateTime($contract['period_start_date']);
      $contract['period_start_date'] = new DateTime($startDate);
      $contract['period_end_date'] = new DateTime($endDate);

      $contracts[$i] = $contract;
    }

    $contracts = $this->fillContractsLapses($contracts);

    return $contracts;
  }

  /**
   * Returns a WorkPattern instance for the given ID.
   *
   * This method caches the loaded WorkPatterns, so calling it multiple times
   * with the same ID will always return the same instance.
   *
   * @param int $id
   *
   * @return CRM_HRLeaveAndAbsences_BAO_WorkPattern
   */
  private function getWorkPatternById($id) {
    if(empty($this->workPatternCache[$id])) {
      $this->workPatternCache[$id] = WorkPattern::findById($id);
    }

    return $this->workPatternCache[$id];
  }

  /**
   * Calculates the date the WorkPattern on the given ContactWorkPattern became
   * effective for the given contract.
   *
   * The logic to calculate this date is:
   * - If the WorkPattern became effective before the contract's start date,
   * then we use the contract's start date as the effective date.
   * - If the WorkPattern became effective after the contract's  start date, but
   * before the contract's start on the absence period (a contract spanning two
   * or more absence periods), then we use the pattern's effective date
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern $contactWorkPattern
   * @param array $contract
   *  An array with contact details, as returned by getContractsWithAdjustedDatesForPeriod()
   *
   * @return \DateTime
   */
  private function calculateWorkPatternEffectiveDateForContract(ContactWorkPattern $contactWorkPattern, $contract) {
    $patternEffectiveDate = new DateTime($contactWorkPattern->effective_date);
    $contractOriginalStartDate = $contract['original_start_date'];

    if ($patternEffectiveDate < $contractOriginalStartDate) {
      $patternStartDate = clone $contractOriginalStartDate;
    }

    if ($patternEffectiveDate >= $contractOriginalStartDate) {
      $patternStartDate = clone $patternEffectiveDate;
    }

    return $patternStartDate;
  }

  /**
   * Calculates the start date for the WorkPattern of the given ContactWorkPattern,
   * on the given contract.
   *
   * The logic is: if the pattern effective date is less than the contract start
   * date on the period, then the start date will be the same as the contract's
   * one. Otherwise, it will be pattern effective date.
   *
   * Note that this date is different from the one returned by the method
   * calculateWorkPatternEffectiveDateForContract(). The former, doesn't care
   * about absence periods and returns when a work pattern became effective for
   * a contract. This one here, takes absence periods into account and returns
   * when the pattern starts to be applying for a contact in the calendar's
   * absence period.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern $contactWorkPattern
   * @param $contract
   *   An array with contact details, as returned by getContractsWithAdjustedDatesForPeriod()
   *
   * @return \DateTime
   */
  private function calculateWorkPatternPeriodStartDate(ContactWorkPattern $contactWorkPattern, $contract) {
    $patternEffectiveDate = new DateTime($contactWorkPattern->effective_date);
    $contractStartDate = $contract['period_start_date'];

    if($patternEffectiveDate < $contractStartDate) {
      $patternEffectiveDate = clone $contractStartDate;
    }

    return $patternEffectiveDate;
  }

  /**
   * Calculates the end date for the WorkPattern of the given ContactWorkPattern,
   * on the given contract.
   *
   * The logic is:
   * - If the WorkPattern has an effective end date and it's less than the
   * contract's end date on the period, then the pattern's effective date will
   * be used.
   * - If the WorkPattern doesn't have an effective end date, or it is greater
   * than the contract's end date on the period, then the contract's end date
   * will be used.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern $contactWorkPattern
   * @param $contract
   *   An array with contact details, as returned by getContractsWithAdjustedDatesForPeriod()
   *
   * @return \DateTime
   */
  private function calculateWorkPatternPeriodEndDate(ContactWorkPattern $contactWorkPattern, $contract) {
    $contractEndDate = $contract['period_end_date'];

    $patternEffectiveEndDate = NULL;
    if ($contactWorkPattern->effective_end_date) {
      $patternEffectiveEndDate = new DateTime($contactWorkPattern->effective_end_date);
    }

    if (!$patternEffectiveEndDate || $patternEffectiveEndDate > $contractEndDate) {
      $patternEffectiveEndDate = clone $contractEndDate;
    }

    return $patternEffectiveEndDate;
  }

  /**
   * This method fills the dates lapses between the items in the array passed in using
   * the fabricateMethod.
   * It also fills lapses between the absence period
   * start date and the first item in the array and the also between the last item
   * and the absence period end date using the fabricate method provided.
   *
   * The array passed in could be Work Pattern periods
   * or Contracts.
   *
   * @param array $toFabricateFor
   * @param string $fabricateMethod
   *
   * @return array
   *   An array with the no date lapses in between
   */
  private function fabricateForLapses($toFabricateFor, $fabricateMethod) {
    $fabricated = [];

    if(!method_exists($this, $fabricateMethod)){
      return $toFabricateFor;
    }
    $absencePeriodEndDate = new DateTime($this->absencePeriod->end_date);
    $absencePeriodStartDate = new DateTime($this->absencePeriod->start_date);

    if(!$toFabricateFor) {
      $fabricated[] = $this->$fabricateMethod($absencePeriodStartDate, $absencePeriodEndDate);
      return $fabricated;
    }

    $firstToFabricateFor = reset($toFabricateFor);

    //fabricate for lapse between beginning of absence period and first $toFabricateFor
    $firstToFabricateForStartDate = clone $firstToFabricateFor['period_start_date'];
    if ($firstToFabricateForStartDate > $absencePeriodStartDate) {
      $fabricated[] = $this->$fabricateMethod($absencePeriodStartDate, $firstToFabricateForStartDate->modify('-1 day'));
    }

    $toCompare = $firstToFabricateFor;
    $fabricated[] = $toCompare;

    //fabricate for lapses in between toFabricateFor periods
    while($next = next($toFabricateFor)) {
      $intervalInDays = $this->getDateIntervalInDays(
        $fromDate = clone $toCompare['period_end_date'],
        $toDate = clone $next['period_start_date']
      );

      if($intervalInDays > 1) {
        $fabricated[] = $this->$fabricateMethod($fromDate->modify('+1 day'), $toDate->modify('-1 day'));
      }
      $fabricated[] = $next;
      $toCompare = $next;
    }

    $lastToFabricateFor = end($toFabricateFor);

    //fabricate for lapse between last toFabricateFor and end of absence period
    $lastToFabricateForEndDate = clone $lastToFabricateFor['period_end_date'];
    if ($lastToFabricateForEndDate < $absencePeriodEndDate) {
      $fabricated[] = $this->$fabricateMethod($lastToFabricateForEndDate->modify('+1 day'), $absencePeriodEndDate);
    }

    return $fabricated;
  }

  /**
   * Returns the interval in days between the from and to dates.
   *
   * @param \DateTime $fromDate
   * @param \DateTime $toDate
   *
   * @return int
   */
  private function getDateIntervalInDays(DateTime $fromDate, DateTime $toDate) {
    $interval = $toDate->diff($fromDate);
    return (int) $interval->format("%a");
  }

  /**
   * This method fabricates a work pattern period using the default work pattern
   * and the dates passed in.
   *
   * @param \DateTime $startDate
   * @param \DateTime $endDate
   * @param \DateTime|null $effectiveDate
   *
   * @return array
   */
  private function fabricateDefaultWorkPatternPeriod(DateTime $startDate, DateTime $endDate, DateTime $effectiveDate = null) {
    $workPattern = WorkPattern::getDefault();
    $workPatternPeriod = [
      'pattern_id' => (int)$workPattern->id,
      'effective_date' => $effectiveDate ? $effectiveDate : $startDate,
      'period_start_date' => $startDate,
      'period_end_date' => $endDate,
    ];

    return $workPatternPeriod;
  }

  /**
   * Fabricates a contract period using the start date and end date range passed in.
   *
   * @param \DateTime $startDate
   * @param \DateTime $endDate
   *
   * @return array
   */
  private function fabricateContractPeriod(DateTime $startDate, DateTime $endDate) {
    $contract = [
      'original_start_date' => $startDate,
      'period_start_date' => $startDate,
      'period_end_date' => $endDate,
    ];

    return $contract;
  }

  /**
   * Fills the lapses between the Work Pattern periods with the default work pattern period
   * It also fills lapses between the absence period
   * start date and the first work pattern period and the also between the last work pattern
   * period and the absence period end date using the default work pattern.
   *
   * @param array $workPatternPeriods
   *
   * @return array
   *   An array of Work pattern periods with no lapses in between.
   */
  private function fillWorkPatternPeriodsLapses($workPatternPeriods) {
    return $this->fabricateForLapses($workPatternPeriods, 'fabricateDefaultWorkPatternPeriod');
  }

  /**
   * Fills the lapses between the contract dates with fabricated contract dates.
   * It also fills lapses between the absence period
   * start date and the first work contract and the also between the last contract
   * and the absence period end date.
   *
   * @param array $contracts
   *
   * @return array
   *  An array of contracts with no lapses in between
   */
  private function fillContractsLapses($contracts) {
    return $this->fabricateForLapses($contracts, 'fabricateContractPeriod');
  }
}
