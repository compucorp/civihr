<?php

use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_Entitlement as Entitlement;
use CRM_Hrjobcontract_BAO_HRJobContract as JobContract;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

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
   * Returns the proposed entitlement for this AbsenceType and Contract on the
   * previous period.
   *
   * @return int
   */
  private function getPreviousPeriodProposedEntitlement()
  {
    $previousPeriod = $this->getPreviousPeriod();

    if(!$previousPeriod) {
      return 0;
    }

    $previousPeriodEntitlement = Entitlement::getContractEntitlementForPeriod(
      $this->contract->id,
      $previousPeriod->id,
      $this->absenceType->id
    );

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
  private function getNumberOfLeavesTakenOnThePreviousPeriod() {
    return 0;
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
}
