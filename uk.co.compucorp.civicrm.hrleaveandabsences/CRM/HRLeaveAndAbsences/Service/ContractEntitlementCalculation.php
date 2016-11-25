<?php

use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;

/**
 * Class CRM_HRLeaveAndAbsences_Service_ContractEntitlementCalculation
 */
class CRM_HRLeaveAndAbsences_Service_ContractEntitlementCalculation {

  private $contract;
  private $period;
  private $absenceType;
  private $jobLeave = false;

  /**
   * @var int
   *   Caches the value returned by the getNumberOfPublicHolidaysInEntitlement()
   *   method
   */
  private $numberOfPublicHolidaysInEntitlement = null;

  /**
   * Creates a new Contract Entitlement Calculation based on the give Absence
   * Period, Contract and AbsenceType
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $period
   * @param array $contract
   *  An array representing the contract. The contract must have the
   *  period_start_date and period_end_date
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsenceType $type
   */
  public function __construct(AbsencePeriod $period, array $contract, AbsenceType $type) {
    $this->contract = $contract;
    $this->period = $period;
    $this->absenceType = $type;
  }

  /**
   * Calculates the Pro Rata for the contract, which is given by:
   *
   * (CE * (WDTW / WD)) + Number of Public Holidays
   *
   * Where:
   * CE: Contractual Entitlement
   * WDTW: No. Working Days to Work
   * WD: No. Working Days
   *
   * @return float
   */
  public function getProRata() {
    $numberOfWorkingDaysToWork = $this->getNumberOfWorkingDaysToWork();
    $numberOfWorkingDays = $this->getNumberOfWorkingDays();

    $contractualEntitlement = $this->getContractualEntitlement();

    $proRata = ($numberOfWorkingDaysToWork / $numberOfWorkingDays) * $contractualEntitlement;

    return $proRata + $this->getNumberOfPublicHolidaysInEntitlement();
  }

  /**
   * Returns the number of working days for this contract in this calculation's
   * period
   *
   * @return int
   */
  public function getNumberOfWorkingDaysToWork() {
    return $this->period->getNumberOfWorkingDaysToWork(
      $this->contract['period_start_date'],
      $this->contract['period_end_date']
    );
  }

  /**
   * Returns the number of working days for this calculation's period
   *
   * @return int
   */
  public function getNumberOfWorkingDays() {
    return $this->period->getNumberOfWorkingDays();
  }

  /**
   * Returns the contractual entitlement for this calculation's contract,
   * based on the Job Leave settings
   *
   * @return float
   */
  public function getContractualEntitlement() {
    $jobLeave = $this->getJobLeave();

    if(!$jobLeave) {
      return 0;
    }

    return (float)$jobLeave['leave_amount'];
  }

  /**
   * Returns the number of Public Holidays added to the entitlement because of
   * contract with "Add Public Holiday?" set.
   *
   * @return int
   */
  public function getNumberOfPublicHolidaysInEntitlement() {
    if(is_null($this->numberOfPublicHolidaysInEntitlement)) {
      $this->numberOfPublicHolidaysInEntitlement = 0;

      $jobLeave = $this->getJobLeave();

      if(!empty($jobLeave['add_public_holidays'])) {
        $this->numberOfPublicHolidaysInEntitlement = PublicHoliday::getCountForPeriod(
          $this->contract['period_start_date'],
          $this->contract['period_end_date']
        );
      }
    }

    return $this->numberOfPublicHolidaysInEntitlement;
  }

  /**
   * Returns a list of PublicHolidays instances representing the Public Holidays
   * added to the entitlement.
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_PublicHoliday[]
   */
  public function getPublicHolidaysInEntitlement() {
    $jobLeave = $this->getJobLeave();

    if(!empty($jobLeave['add_public_holidays'])) {
      return PublicHoliday::getAllForPeriod(
        $this->contract['period_start_date'],
        $this->contract['period_end_date']
      );
    }

    return [];
  }

  /**
   * Returns the start date of this calculation's contract
   *
   * @return string
   */
  public function getContractStartDate() {
    return $this->contract['period_start_date'];
  }

  /**
   * Returns the end date of this calculation's contract
   *
   * @return string
   */
  public function getContractEndDate() {
    return $this->contract['period_end_date'];
  }

  /**
   * Returns an array with the values of the JobLeave for the given contract and
   * the calculation's  absence type.
   *
   * @return array|null
   *   An array with the JobLeave fields or null if there's no JobLeave for this AbsenceType
   */
  private function getJobLeave() {
    if($this->jobLeave === false) {
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
}
