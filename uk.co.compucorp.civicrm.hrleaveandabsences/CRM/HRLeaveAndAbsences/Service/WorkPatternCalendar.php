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
      $contractStartDate = new DateTime($contract['period_start_date']);
      $contractEndDate = new DateTime($contract['period_end_date']);
      $contractOriginalStartDate = new DateTime($contract['original_start_date']);

      $contactWorkPatterns = ContactWorkPattern::getAllForPeriod(
        $this->contactID,
        $contractStartDate,
        $contractEndDate
      );

      // If there's no work pattern for this contract, we use the default one
      // for its whole period
      if(empty($contactWorkPatterns)) {
        $workPattern = WorkPattern::getDefault();
        $workPatterns[] = [
          'pattern_id' => (int)$workPattern->id,
          'effective_date' => $contractOriginalStartDate,
          'period_start_date' => $contractStartDate,
          'period_end_date' => $contractEndDate
        ];

        continue;
      }

      // If the first returned work pattern starts being effective after the
      // contract start date, it means that, for some time, that contract's
      // contact didn't have any work pattern assigned. In this case, we have to
      // use the default work pattern for this period
      $firstWorkPattern = reset($contactWorkPatterns);
      $firstWorkPatternEffectiveDate = new DateTime($firstWorkPattern->effective_date);

      if($firstWorkPatternEffectiveDate > $contractStartDate) {
        $workPattern = WorkPattern::getDefault();

        $workPatterns[] = [
          'pattern_id' => (int)$workPattern->id,
          'effective_date' => $contractStartDate,
          'period_start_date' => $contractStartDate,
          'period_end_date' => $firstWorkPatternEffectiveDate->modify('-1 day')
        ];
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
      $contract['original_start_date'] = $contract['period_start_date'];
      $contract['period_start_date'] = $startDate;
      $contract['period_end_date'] = $endDate;

      $contracts[$i] = $contract;
    }

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
    $contractOriginalStartDate = new DateTime($contract['original_start_date']);

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
    $contractStartDate = new DateTime($contract['period_start_date']);

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
    $contractEndDate = new DateTime($contract['period_end_date']);

    $patternEffectiveEndDate = NULL;
    if ($contactWorkPattern->effective_end_date) {
      $patternEffectiveEndDate = new DateTime($contactWorkPattern->effective_end_date);
    }

    if (!$patternEffectiveEndDate || $patternEffectiveEndDate > $contractEndDate) {
      $patternEffectiveEndDate = clone $contractEndDate;
    }

    return $patternEffectiveEndDate;
  }

}
