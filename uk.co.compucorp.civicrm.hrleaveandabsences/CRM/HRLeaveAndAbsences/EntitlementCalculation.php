<?php

use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;

/**
 * This class encapsulates all of the entitlement calculation logic.
 *
 * Based on a set of Absence Period, Contract and Absence Type, it can
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
   * The Contact this calculation is based on.
   * This is expected to be an array, just like the one returned from an API
   * call
   *
   * @var array
   */
  private $contact;

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
   * @var \CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement
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
   * Variable to cache the return from the getContractsInPeriod method
   *
   * @var array
   */
  private $contractInPeriod = null;

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
  private $periodEntitlement = FALSE;

  /**
   * Creates a new EntitlementCalculation instance
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $period
   * @param array $contact The contact in array format, like when it's returned by an API call
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsenceType $absenceType
   */
  public function __construct(AbsencePeriod $period, $contact, AbsenceType $absenceType) {
    $this->period = $period;
    $this->contact = $contact;
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
   * The Contractual Entitlement is given by the sum of the leave amount of all
   * of the employee's contracts + the number of public holidays during the
   * contract start and end dates (if the leave settings on the contract allows
   * this).
   *
   * @return int
   */
  public function getContractualEntitlement() {
    $contracts = $this->getContractsInPeriod();
    return array_reduce($contracts, function($contractualEntitlement, $contract) {
      $contractualEntitlement += $this->getContractualEntitlementForContract($contract);

      return $contractualEntitlement;
    }, 0);
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
      return $periodEntitlement->getEntitlement();
    }

    return $this->getProRata() + $this->getBroughtForward();
  }

  /**
   * Returns the previously calculated entitlement for the calculation period.
   *
   * If there's no such entitlement, returns null.
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement|null
   */
  private function getPeriodEntitlement() {
    if($this->periodEntitlement === false) {
      $this->periodEntitlement = LeavePeriodEntitlement::getPeriodEntitlementForContact(
        $this->contact['id'],
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
  public function isCurrentPeriodEntitlementOverridden() {
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

    return $previousPeriodEntitlement->getEntitlement();
  }

  /**
   * Return the number of days taken as leave during the Previous Period.
   *
   * This is, basically, the LeaveRequest balance from the previous period, but
   * returned as a positive number
   *
   * @see CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement::getLeaveRequestBalance()
   *
   * @return float
   */
  public function getNumberOfDaysTakenOnThePreviousPeriod() {
    $entitlement = $this->getPreviousPeriodEntitlement();

    if(!$entitlement) {
      return 0.0;
    }

    return $entitlement->getLeaveRequestBalance() * -1.0;
  }

  /**
   * Return the number of days remaining on the previous period. That is, the
   * balance of that period, which is given by the sum of all days added to the
   * entitlement plus the days deducted
   *
   * @return float
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
   * Returns the Contact array used to create this calculation
   *
   * @return array
   */
  public function getContact()
  {
    return $this->contact;
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
   * Returns the calculated Entitlement for the previous period.
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement|null
   *          The entitlement for the previous period or null if there's no previous period or entitlement
   */
  private function getPreviousPeriodEntitlement() {
    $previousPeriod = $this->getPreviousPeriod();
    if(!$previousPeriod) {
      return null;
    }

    if(!$this->previousPeriodEntitlement) {
      $this->previousPeriodEntitlement = LeavePeriodEntitlement::getPeriodEntitlementForContact(
        $this->contact['id'],
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

    $expirationDate = $this->getBroughtForwardExpirationDate();

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
   * Returns an array containing the start and end dates of all the contracts
   * for this calculation's employee during the calculation's Absence Period.
   *
   * To help with the calculation, if any of the contract doesn't have an end date,
   * the period end date will be used instead.
   *
   * @return array
   *  The array with the dates in this format:
   *  [
   *    ['start_date' => '2016-01-01', 'end_date' => '2016-09-15'],
   *    ['start_date' => '2016-10-10', 'end_date' => '2016-12-31'],
   *    ...
   *  ]
   */
  private function getContractsDates() {
    $contractsDates = [];

    $contracts = $this->getContractsInPeriod();
    foreach ($contracts as $contract) {
      if(empty($contract['period_end_date'])) {
        $contract['period_end_date'] = $this->period->end_date;
      }

      $contractsDates[] = [
        'start_date' => $contract['period_start_date'],
        'end_date' => $contract['period_end_date']
      ];
    }

    return $contractsDates;
  }

  /**
   * Returns an array with the values of the JobLeave of this calculation's
   * contract and absence type and the given contract.
   *
   * @param int $contractID
   * @return array|null An array with the JobLeave fields or null if there's
   *                    no JobLeave for this AbsenceType
   */
  private function getJobLeaveForAbsenceType($contractID) {
    if(!$this->jobLeave) {
      try {
        $this->jobLeave = civicrm_api3('HRJobLeave', 'getsingle', array(
          'jobcontract_id' => (int)$contractID,
          'leave_type' => (int)$this->absenceType->id
        ));
      } catch(CiviCRM_API3_Exception $ex) {
        $this->jobLeave = null;
      }
    }

    return $this->jobLeave;
  }

  /**
   * Returns an array with all the of the contracts for the calculation's contact
   * during the calculation's Absence Period.
   *
   * @return array
   *  An array with the output of the HRJobContract.getactivecontractswithdetails
   *  API endpoint
   */
  private function getContractsInPeriod() {
    if(is_null($this->contractInPeriod)) {
      $result = civicrm_api3('HRJobContract', 'getactivecontractswithdetails', [
        'contact_id' => $this->contact['id'],
        'start_date' => $this->period->start_date,
        'end_date'   => $this->period->end_date
      ]);

      $this->contractInPeriod = $result['values'];
    }

    return $this->contractInPeriod;
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
    $contractsDates = $this->getContractsDates();

    return array_reduce($contractsDates, function($numberOfDays, $contractDates) {
      $numberOfDays += $this->period->getNumberOfWorkingDaysToWork(
        $contractDates['start_date'],
        $contractDates['end_date']
      );
      return $numberOfDays;
    });
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

  /**
   * Returns the expiration date for a brought forward, based on the
   * AbsencePeriod start date and the AbsenceType carry forward rules
   *
   * @return null|string
   */
  public function getBroughtForwardExpirationDate() {
    return $this->period->getExpirationDateForAbsenceType($this->absenceType);
  }

  /**
   * Returns a list of PublicHolidays instances representing the Public Holidays
   * added to the entitlement.
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_PublicHoliday[]
   */
  public function getPublicHolidaysInEntitlement() {
    $publicHolidays = [];

    $contracts = $this->getContractsInPeriod();
    foreach($contracts as $contract) {
      $jobLeave = $this->getJobLeaveForAbsenceType($contract['id']);

      if(!empty($jobLeave['add_public_holidays'])) {
        if(empty($contract['period_end_date'])) {
          $contract['period_end_date'] = $this->period->end_date;
        }

        list($startDate, $endDate) = $this->period->adjustDatesToMatchPeriodDates(
          $contract['period_start_date'],
          $contract['period_end_date']
        );

        $publicHolidays += PublicHoliday::getPublicHolidaysForPeriod($startDate, $endDate);
      }
    }

    return $publicHolidays;
  }

  /**
   * Returns the contractual entitlement for the given contract
   *
   * @param array $contract
   *   An array representing a HRJobContract
   *
   * @return float
   */
  private function getContractualEntitlementForContract($contract) {
    $contractualEntitlement = 0;

    $jobLeave = $this->getJobLeaveForAbsenceType($contract['id']);
    if(!$jobLeave) {
      return $contractualEntitlement;
    }

    $contractualEntitlement = (float)$jobLeave['leave_amount'];
    if($jobLeave['add_public_holidays']) {
      $contractualEntitlement += $this->getNumberOfPublicHolidaysForContract($contract);
    }

    return $contractualEntitlement;
  }

  /**
   * Returns the number of Public Holidays between the contract start and end
   * dates. If the contract has no end date, then the Absence Period end date
   * will be used instead.
   *
   * @param array $contract
   *  An array representing a HRJobContract
   *
   * @return int
   */
  private function getNumberOfPublicHolidaysForContract($contract) {
    if(empty($contract['period_end_date'])) {
      $contract['period_end_date'] = $this->period->end_date;
    }

    list($startDate, $endDate) = $this->period->adjustDatesToMatchPeriodDates(
      $contract['period_start_date'],
      $contract['period_end_date']
    );

    return PublicHoliday::getNumberOfPublicHolidaysForPeriod($startDate, $endDate);
  }
}
