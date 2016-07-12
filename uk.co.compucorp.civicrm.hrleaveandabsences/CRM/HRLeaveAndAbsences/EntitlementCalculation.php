<?php

use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_Entitlement as Entitlement;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;

/**
 * This class encapsulates all of the entitlement calculation logic.
 *
 * Based on a set of Absence Period, Job Contract and Absence Type, it can
 * calculate the Pro Rata, Number of days brought forward, Contractual
 * Entitlement and a Proposed Entitlement.
 */
class CRM_HRLeaveAndAbsences_EntitlementCalculation {

  /**
   * The AbsencePeriod this calculation is based on
   *
   * @var \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   */
  private $period;

  /**
   * The Job Contract this calculation is based on
   * This is expected to be an array, just like the one returned from an API
   * call
   *
   * @var array
   */
  private $contract;

  /**
   * The AbsenceType this calculation is based on
   *
   * @var \CRM_HRLeaveAndAbsences_BAO_AbsenceType
   */
  private $absenceType;

  /**
   * Variable to cache the return from the getPreviousPeriod method
   *
   * @var \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   */
  private $previousPeriod;

  /**
   * Variable to cache the return from the getPreviousPeriodEntitlement method
   *
   * @var \CRM_HRLeaveAndAbsences_BAO_Entitlement
   */
  private $previousPeriodEntitlement;

  /**
   * Variable to cache the return from the getNumberOfWorkingDaysForPeriod method
   *
   * @var int
   */
  private $numberOfWorkingDays;

  /**
   * Variable to cache the return from the getNumberOfPublicHolidaysForPeriod method
   *
   * @var int
   */
  private $numberOfPublicHolidaysInPeriod;

  /**
   * Variable to cache the return from the getContractDetails method
   *
   * @var array
   */
  private $contractDetails;

  /**
   * Variable to cache the return from the getJobLeaveForAbsenceType method
   *
   * @var array
   */
  private $jobLeave;

  /**
   * Variable to cache the return from the getPeriodEntitlement method.
   *
   * If false, it means the entitlement was never loaded. If null, it means there's
   * no stored entitlement for the current period.
   *
   * @var bool|null
   */
  private $periodEntitlement = false;


  /**
   * Creates a new EntitlementCalculation instance
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $period
   * @param array $contract The contract in array format, like when it's returned by an API call
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsenceType $absenceType
   */
  public function __construct(AbsencePeriod $period, $contract, AbsenceType $absenceType) {
    $this->period = $period;
    $this->contract = $contract;
    $this->absenceType = $absenceType;
  }

  /**
   * Calculates the number of days Brought Forward from the previous Absence
   * Period.
   *
   * This number of days is given by the proposed entitlement of the previous
   * period minus the number of leaves taken on the previous period, minus the
   * expired brought forward, if any. It may be limited by the max number of
   * days allowed to be carried forward for this calculation's absence type.
   *
   * @return float
   */
  public function getBroughtForward() {
    if(!$this->shouldCalculateBroughtForward()) {
      return 0;
    }

    $broughtForward = $this->getNumberOfDaysRemainingInThePreviousPeriod();
    if($broughtForward > $this->absenceType->max_number_of_days_to_carry_forward) {
      return $this->absenceType->max_number_of_days_to_carry_forward;
    }

    return $broughtForward;
  }

  /**
   * Returns the Pro Rata for this calculation contract on the calculation
   * period.
   *
   * The Pro Rata is given by:
   * (no. working days to work / no. of working days) x contractual entitlement.
   *
   * The end result is rounded up to the nearest half day. Example:
   *
   * Number of working days to work: 212
   * Number of working days: 253
   * Contractual entitlement: 28
   * Pro rata: (212 / 253) * 28 = 23.46 = 23.5 (rounded)
   *
   * @return float|int
   */
  public function getProRata() {
    $numberOfWorkingDaysToWork = $this->getNumberOfWorkingDaysToWork();
    $numberOfWorkingDays = $this->getNumberOfWorkingDays();
    $contractualEntitlement = $this->getContractualEntitlement();

    $proRata = ($numberOfWorkingDaysToWork / $numberOfWorkingDays) * $contractualEntitlement;
    $roundedProRata = ceil($proRata * 2) / 2;

    return $roundedProRata;
  }

  /**
   * Returns the Contractual Entitlement for this calculation contract.
   *
   * The Contractual Entitlement is given by the leave amount set for this
   * calculation contract and absence type + the number of public holidays in
   * the period (if the leave settings on the contract allows this).
   *
   * @return int
   */
  public function getContractualEntitlement() {
    $contractualEntitlement = 0;

    $jobLeave = $this->getJobLeaveForAbsenceType();
    if(!$jobLeave) {
      return $contractualEntitlement;
    }

    $contractualEntitlement = $jobLeave['leave_amount'];
    if($jobLeave['add_public_holidays']) {
      $contractualEntitlement += $this->getNumberOfPublicHolidaysForPeriod();
    }

    return $contractualEntitlement;
  }

  /**
   * Returns the calculated proposed entitlement.
   *
   * This is basically the Pro Rata + the number of days brought forward
   *
   * @return float|int
   */
  public function getProposedEntitlement() {
    $periodEntitlement = $this->getPeriodEntitlement();
    if($periodEntitlement && $periodEntitlement->overridden) {
      return $periodEntitlement->proposed_entitlement;
    }

    return $this->getProRata() + $this->getBroughtForward();
  }

  /**
   * Returns the proposed entitlement for this AbsenceType and Contract on the
   * previous period.
   *
   * @return int
   */
  public function getPreviousPeriodProposedEntitlement() {
    $previousPeriodEntitlement = $this->getPreviousPeriodEntitlement();

    if(!$previousPeriodEntitlement) {
      return 0;
    }

    return $previousPeriodEntitlement->proposed_entitlement;
  }

  /**
   * Return the number of Leaves taken during the Previous Period
   *
   * @TODO The Leaves Request feature is not yet implemented, so we're only returning 0 for now
   *
   * @return int
   */
  public function getNumberOfLeavesTakenOnThePreviousPeriod() {
    return 0;
  }

  /**
   * Returns the entitlement balance for this calculation's absence type during
   * the previous period.
   *
   * @return int
   */
  public function getNumberOfDaysRemainingInThePreviousPeriod() {
    $entitlement = $this->getPreviousPeriodEntitlement();

    if(!$entitlement) {
      return 0;
    }

    return $entitlement->getBalance();
  }

  /**
   * Returns the AbsenceType instance used to create this calculation
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_AbsenceType
   */
  public function getAbsenceType()
  {
    return $this->absenceType;
  }

  /**
   * Returns the Job Contract array used to create this calculation
   *
   * @return array
   */
  public function getContract()
  {
    return $this->contract;
  }

  /**
   * Returns the AbsencePeriod instance used to create this calculation
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   */
  public function getAbsencePeriod()
  {
    return $this->period;
  }

  /**
   * Returns the previously calculated entitlement for the calculation period.
   *
   * If there's no such entitlement, returns null.
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_Entitlement|null
   */
  private function getPeriodEntitlement()
  {
    if($this->periodEntitlement === false) {
      $this->periodEntitlement = Entitlement::getContractEntitlementForPeriod(
        $this->contract['id'],
        $this->period->id,
        $this->absenceType->id
      );
    }

    return $this->periodEntitlement;
  }

  /**
   * Returns if the there's a previously calculated entitlement for this
   * calculation's period and if it is overridden.
   *
   * @return bool
   */
  public function isCurrentPeriodEntitlementOverridden()
  {
    $periodEntitlement = $this->getPeriodEntitlement();

    return $periodEntitlement && $periodEntitlement->overridden;
  }

  /**
   * Returns the comment for the previously calculated entitlement for this
   * calculation's period, if it exists.
   *
   * @return bool
   */
  public function getCurrentPeriodEntitlementComment()
  {
    $periodEntitlement = $this->getPeriodEntitlement();

    return $periodEntitlement ? $periodEntitlement->comment : '';
  }

  /**
   * Returns the calculated Entitlement for the previous period.
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_Entitlement|null
   *          The entitlement for the previous period or null if there's no previous period or entitlement
   */
  private function getPreviousPeriodEntitlement() {
    $previousPeriod = $this->getPreviousPeriod();
    if(!$previousPeriod) {
      return null;
    }

    if(!$this->previousPeriodEntitlement) {
      $this->previousPeriodEntitlement = Entitlement::getContractEntitlementForPeriod(
        $this->contract['id'],
        $previousPeriod->id,
        $this->absenceType->id
      );
    }

    return $this->previousPeriodEntitlement;
  }

  /**
   * Returns the AbsencePeriod previous to the one this calculation is based on
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod|null
   */
  private function getPreviousPeriod() {
    if(!$this->previousPeriod) {
      $this->previousPeriod = $this->period->getPreviousPeriod();
    }

    return $this->previousPeriod;
  }

  /**
   * Check if, based on the AbsenceType expiration duration, if
   * the brought forward has expired for this calculation period.
   *
   * @return bool
   */
  private function broughtForwardHasExpired() {
    if($this->absenceType->carryForwardNeverExpires() === true) {
      return false;
    }

    $expirationDate = $this->period->getExpirationDateForAbsenceType($this->absenceType);

    if($expirationDate) {
      return strtotime($expirationDate) < strtotime('now');
    }

    return true;
  }

  /**
   * We only calculate the amount of days brought forward if the AbsenceType
   * allows carry forward and it has not expired
   *
   * @return bool
   */
  private function shouldCalculateBroughtForward() {
    return $this->absenceType->allow_carry_forward &&
           !$this->broughtForwardHasExpired();
  }

  /**
   * Returns an array containing the Contract's start and end dates.
   *
   * To help with the calculation, if the contract doesn't have an end date,
   * the period end date will be used instead.
   *
   * @return array|null The array with the dates or null if the contract details could not be found
   */
  private function getContractDates() {
    $contractDetails = $this->getContractDetails();
    if(!$contractDetails) {
      return null;
    }

    if(!isset($contractDetails['period_end_date'])) {
      $contractDetails['period_end_date'] = $this->period->end_date;
    }

    return [
      'start_date' => $contractDetails['period_start_date'],
      'end_date' => $contractDetails['period_end_date']
    ];
  }

  /**
   * Returns an array with the values of the JobLeave of this calculation's
   * contract and absence type.
   *
   * @return array|null An array with the JobLeave fields or null if there's
   *                    no JobLeave for this AbsenceType
   */
  private function getJobLeaveForAbsenceType() {
    if(!$this->jobLeave) {
      try {
        $this->jobLeave = civicrm_api3('HRJobLeave', 'getsingle', array(
          'jobcontract_id' => (int)$this->contract['id'],
          'leave_type' => (int)$this->absenceType->id
        ));
      } catch(CiviCRM_API3_Exception $ex) {
        $this->jobLeave = null;
      }
    }

    return $this->jobLeave;
  }

  /**
   * Returns an array with the values of the JobDetails of this calculation's
   * contract and absence type.
   *
   * @return array|null An array with the JobDetails fields or null if there's
   *                    no JobDetails for this AbsenceType
   */
  private function getContractDetails() {
    if(!$this->contractDetails) {
      try {
        $this->contractDetails = civicrm_api3('HRJobDetails', 'getsingle', array(
          'jobcontract_id' => (int)$this->contract['id'],
        ));
      } catch(CiviCRM_API3_Exception $ex) {
        $this->contractDetails = null;
      }
    }

    return $this->contractDetails;
  }

  /**
   * Returns the number of Public Holidays for this calculation period
   *
   * @return int
   */
  private function getNumberOfPublicHolidaysForPeriod() {
    if(!$this->numberOfPublicHolidaysInPeriod) {
      $this->numberOfPublicHolidaysInPeriod = PublicHoliday::getNumberOfPublicHolidaysForPeriod(
        $this->period->start_date,
        $this->period->end_date
      );
    }

    return $this->numberOfPublicHolidaysInPeriod;
  }

  /**
   * Returns the number of working days to work for the calculation contract on
   * the calculation period.
   *
   * @return int
   */
  private function getNumberOfWorkingDaysToWork() {
    $contractDates = $this->getContractDates();
    if(!$contractDates) {
      return 0;
    }

    return $this->period->getNumberOfWorkingDaysToWork(
      $contractDates['start_date'],
      $contractDates['end_date']
    );
  }

  /**
   * Returns the number of working days (excluding public holidays) for this
   * calculation period
   *
   * @return int
   */
  private function getNumberOfWorkingDays() {
    if(!$this->numberOfWorkingDays) {
      $this->numberOfWorkingDays = $this->period->getNumberOfWorkingDays();
    }

    return $this->numberOfWorkingDays;
  }


  /**
   * Returns a string representation of the calculation in the format:
   *
   * ((CE + PH) * (WDTW / WD)) = (PR) + (BF) = PE
   *
   * Where:
   * CE: Contractual Entitlement (Not including public holidays)
   * PH: Number of Public Holidays
   * WDTW: Number of Working days to work
   * WD: Number of Working days
   * PR: Pro Rata, rounded to the nearest half day
   * BF: Number of days Brought Forward
   * PE: Proposed Entitlement
   *
   * @return string
   */
  public function __toString()
  {
    return sprintf(
      '((%s + %s) * (%s / %s)) = (%s) + (%s) = %s days',
      $this->getContractualEntitlement() - $this->getNumberOfPublicHolidaysForPeriod(),
      $this->getNumberOfPublicHolidaysForPeriod(),
      $this->getNumberOfWorkingDaysToWork(),
      $this->period->getNumberOfWorkingDays(),
      $this->getProRata(),
      $this->getBroughtForward(),
      $this->getProposedEntitlement()
    );
  }
}
