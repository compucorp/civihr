<?php

use CRM_HRLeaveAndAbsences_Service_EntitlementCalculation as EntitlementCalculation;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

/**
 * This class encapsulates the creation of EntitlementCalculations for an
 * specific AbsencePeriod.
 */
class CRM_HRLeaveAndAbsences_Service_EntitlementCalculator {

  /**
   * The AbsencePeriod to calculate the entitlements for
   *
   * @var \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   */
  private $period;

  /**
   * An array to cache the loaded enabled AbsenceType instances,
   * used to calculate the entitlements.
   *
   * @var array
   */
  private $absenceTypes = [];

  /**
   * CRM_HRLeaveAndAbsences_Service_EntitlementCalculator constructor.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $period
   */
  public function __construct(AbsencePeriod $period)
  {
    $this->period = $period;
  }

  /**
   * This method generates EntitlementCalculation instances for the given
   * contact. One EntitlementCalculation instance is returned for each currently
   * enabled AbsenceType.
   *
   * @param array $contact
   *  A Contact in array format, like when it is returned from an API call
   *
   * @return array An array of EntitlementCalculations
   */
  public function calculateEntitlementsFor($contact) {
    $absenceTypes = $this->getEnabledAbsenceTypes();
    $calculations = [];
    foreach($absenceTypes as $absenceType) {
      $calculations[] = new EntitlementCalculation($this->period, $contact, $absenceType);
    }

    return $calculations;
  }

  /**
   * Returns a list of enabled AbsenceTypes.
   *
   * @return array
   */
  private function getEnabledAbsenceTypes() {
    if(empty($this->absenceTypes)) {
      $this->absenceTypes = AbsenceType::getEnabledAbsenceTypes();
    }

    return $this->absenceTypes;
  }

}
