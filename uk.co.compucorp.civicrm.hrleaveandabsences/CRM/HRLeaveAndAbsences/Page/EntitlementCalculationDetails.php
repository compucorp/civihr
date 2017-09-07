<?php

use CRM_HRLeaveAndAbsences_Service_EntitlementCalculation as EntitlementCalculation;

/**
 * Class CRM_HRLeaveAndAbsences_Page_EntitlementCalculationDetails
 */
class CRM_HRLeaveAndAbsences_Page_EntitlementCalculationDetails extends CRM_Core_Page {

  /**
   * {@inheritDoc}
   */
  public function run() {
    $typeID = CRM_Utils_Request::retrieve('type_id', 'Integer');
    $contactID = CRM_Utils_Request::retrieve('contact_id', 'Integer');
    $periodID = CRM_Utils_Request::retrieve('period_id', 'Integer');

    if(empty($typeID) || empty($contactID) || empty($periodID)) {
      return;
    }

    $type = CRM_HRLeaveAndAbsences_BAO_AbsenceType::findById($typeID);
    $contact = civicrm_api3('Contact', 'getsingle', ['id' => $contactID, 'sequential' => 1]);
    $period = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::findById($periodID);

    $calculation = new EntitlementCalculation($period, $contact, $type);

    $this->assign('calculation', $calculation);
    $this->assign('proRataCalculationDescription', $this->buildProRataCalculationDescription($calculation));

    CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/leaveandabsence.css', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');

    parent::run();
  }

  /**
   * A helper method that builds the period pro rata description, which is a sum
   * of all pro ratas for every contract in this calculation.
   *
   * This was created basically to remove all this logic from the template
   *
   * @param CRM_HRLeaveAndAbsences_Service_EntitlementCalculation $entitlementCalculation
   *
   * @return string
   */
  private function buildProRataCalculationDescription(EntitlementCalculation $entitlementCalculation) {
    $contractsCalculation = $entitlementCalculation->getContractEntitlementCalculations();
    $contractsProRatas = [];
    $i = 1;
    foreach($contractsCalculation as $calculation) {
      $proRata = number_format($calculation->getProRata(), 2);
      $contractsProRatas[] = '<span class="contract-'.$i.'-pro-rata">' . $proRata . '</span>';
      $i++;
    }

    $proRataCalculation = implode(' + ', $contractsProRatas);
    $proRataSum = '<span class="calculation-pro-rata">' . $entitlementCalculation->getProRata().'</span>';

    return "{$proRataCalculation} = {$proRataSum}";
  }
}
