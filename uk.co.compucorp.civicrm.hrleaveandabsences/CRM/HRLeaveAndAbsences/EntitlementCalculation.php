<?php

use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_Entitlement as Entitlement;
use CRM_Hrjobcontract_BAO_HRJobContract as JobContract;
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
   * @var \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   */
  private $period;

  /**
   * @var \CRM_Hrjobcontract_BAO_HRJobContract
   */
  private $contract;

  /**
   * @var \CRM_HRLeaveAndAbsences_BAO_AbsenceType
   */
  private $absenceType;

  /**
   * @var \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   */
  private $previousPeriod;

  /**
   * Creates a new EntitlementCalculation instance
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $period
   * @param \CRM_Hrjobcontract_BAO_HRJobContract $contract
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsenceType $absenceType
   */
  public function __construct(AbsencePeriod $period, JobContract $contract, AbsenceType $absenceType) {
    $this->period = $period;
    $this->contract = $contract;
    $this->absenceType = $absenceType;
  }

  /**
   * Calculates the number of days Brought Forward from the previous Absence
   * Period.
   *
   * This number of days is given by the proposed entitlement of the previous
   * period - the number of leaves taken on the previous period. It may be
   * limited by the max number of days allowed to be carried forward for this
   * calculation's absence type.
   *
   * @return int
   */
  public function getBroughtForward()
  {
    if(!$this->shouldCalculateBroughtForward()) {
      return 0;
    }

    $previousPeriodProposedEntitlement = $this->getPreviousPeriodProposedEntitlement();
    $leavesTaken = $this->getNumberOfLeavesTakenOnThePreviousPeriod();

    $broughtForward = $previousPeriodProposedEntitlement - $leavesTaken;
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
  public function getProRata()
  {
    $contractDates = $this->getContractDates();
    if(!$contractDates) {
      return 0;
    }

    $numberOfWorkingDaysToWork = $this->period->getNumberOfWorkingDaysToWork(
      $contractDates['start_date'],
      $contractDates['end_date']
    );
    $numberOfWorkingDays = $this->period->getNumberOfWorkingDays();
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
  public function getContractualEntitlement()
  {
    $contractualEntitlement = 0;

    $jobLeave = $this->getJobLeaveForAbsenceType();
    if(!$jobLeave) {
      return $contractualEntitlement;
    }

    $contractualEntitlement = $jobLeave['leave_amount'];
    if($jobLeave['add_public_holidays']) {
      $contractualEntitlement += PublicHoliday::getNumberOfPublicHolidaysForPeriod(
        $this->period->start_date,
        $this->period->end_date
      );
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
  public function getProposedEntitlement()
  {
    return $this->getProRata() + $this->getBroughtForward();
  }

  /**
   * Returns the proposed entitlement for this AbsenceType and Contract on the
   * previous period.
   *
   * @return int
   */
  public function getPreviousPeriodProposedEntitlement()
  {
    $previousPeriod = $this->getPreviousPeriod();

    if(!$previousPeriod) {
      return 0;
    }

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
   * Return the number of days remaining on the previous period. That is,
   * the proposed entitlement - the number of leaves taken
   *
   * @return int
   */
  public function getNumberOfDaysRemainingInThePreviousPeriod()
  {
    $leavesTaken = $this->getNumberOfLeavesTakenOnThePreviousPeriod();
    $proposedEntitlement = $this->getPreviousPeriodProposedEntitlement();
    return $proposedEntitlement - $leavesTaken;
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

    $previousPeriodEntitlement = Entitlement::getContractEntitlementForPeriod(
      $this->contract->id,
      $previousPeriod->id,
      $this->absenceType->id
    );

    return $previousPeriodEntitlement;
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
  private function getJobLeaveForAbsenceType()
  {
    try {
      return civicrm_api3('HRJobLeave', 'getsingle', array(
        'jobcontract_id' => (int)$this->contract->id,
        'leave_type' => (int)$this->absenceType->id
      ));
    } catch(CiviCRM_API3_Exception $ex) {
      return null;
    }
  }

  /**
   * Returns an array with the values of the JobDetails of this calculation's
   * contract and absence type.
   *
   * @return array|null An array with the JobDetails fields or null if there's
   *                    no JobDetails for this AbsenceType
   */
  private function getContractDetails()
  {
    try {
      return civicrm_api3('HRJobDetails', 'getsingle', array(
        'jobcontract_id' => (int)$this->contract->id,
      ));
    } catch(CiviCRM_API3_Exception $ex) {
      return null;
    }
  }
}
