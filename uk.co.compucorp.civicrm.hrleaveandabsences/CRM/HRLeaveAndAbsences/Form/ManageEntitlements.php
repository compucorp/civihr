<?php

require_once 'CRM/Core/Form.php';

use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_EntitlementCalculator as EntitlementCalculator;

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_HRLeaveAndAbsences_Form_ManageEntitlements extends CRM_Core_Form {

  /**
   * {@inheritdoc}
   */
  public function buildQuickForm() {
    $absencePeriod = $this->getAbsencePeriodFromRequest();
    $this->setFormPageTitle($absencePeriod);

    $calculations = $this->getEntitlementCalculations($absencePeriod);

    $this->addProposedEntitlementsFields($calculations);

    $exportCSV = CRM_Utils_Request::retrieve('export_csv', 'Integer');
    if($exportCSV) {
      $this->exportCSV($calculations);
    }

    $this->assign('period', $absencePeriod);
    $this->assign('contractsIDs', $this->getContractsIDsFromRequest());
    $this->assign('calculations', $calculations);
    $this->assign('enabledAbsenceTypes', $this->getEnabledAbsenceTypes());

    CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/hrleaveandabsences.css', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/hrleaveandabsences.form.manage_entitlements.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    parent::buildQuickForm();
  }

  /**
   * Retrieves and returns an AbsencePeriod BAO instance for the id parameter
   * passed through the URL
   *
   * @return CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   * @throws \Exception
   */
  private function getAbsencePeriodFromRequest() {
    $periodId = CRM_Utils_Request::retrieve('id', 'Integer');
    return AbsencePeriod::findById((int)$periodId);
  }

  /**
   * Sets the page title with the period title and dates
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $absencePeriod
   */
  public function setFormPageTitle(AbsencePeriod $absencePeriod) {
    $pageTitle = ts(
      'Manage Leave Entitlements for period "%1" - %2 to %3',
      [
        1 => $absencePeriod->title,
        2 => CRM_Utils_Date::customFormat($absencePeriod->start_date),
        3 => CRM_Utils_Date::customFormat($absencePeriod->end_date),
      ]
    );
    CRM_Utils_System::setTitle($pageTitle);
  }

  /**
   * Creates EntitlementCalculation instances for every active contract in
   * given AbsencePeriod.
   *
   * @param CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $absencePeriod
   *
   * @return array An array containing all the EntitlementCalculations created
   */
  private function getEntitlementCalculations(AbsencePeriod $absencePeriod) {
    $contracts    = $this->getContractsForCalculation($absencePeriod);
    $calculator   = new EntitlementCalculator($absencePeriod);
    $calculations = [];
    foreach ($contracts as $contract) {
      $calculations = array_merge($calculations,
        $calculator->calculateEntitlementsFor($contract));
    }
    return $calculations;
  }

  /**
   * Returns a list of contracts to run the entitlement calculation for.
   *
   * By default, it returns all the Contracts that are active on the given
   * Absence Period. If the request contains the "cid" parameter, it will only
   * return the active contracts with the IDs on it.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $absencePeriod
   *
   * @return array
   */
  private function getContractsForCalculation(AbsencePeriod $absencePeriod) {
    $contractsIDs = $this->getContractsIDsFromRequest();
    return $this->getActiveContractsForPeriod($absencePeriod, $contractsIDs);
  }

  /**
   * Returns an array containing all the active contracts for the given
   * AbsencePeriod.
   *
   * This method uses the HRJobContract API to retrieve the contacts, so the
   * return values will be an array, as this is how they are returned by the
   * API.
   *
   * To help things while displaying the entitlement calculations, the API call
   * is chained with the Contact API in order to retrieve the staff display
   * name. Its value is available as 'contact_display_name'.
   *
   * It is possible to filter the returned contracts to include only contracts
   * with specific IDs. For that, you need pass a list of $id to the $filter
   * parameter.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $absencePeriod
   *
   * @param array $filter
   *  A list of IDs that will be used to filter the returned contracts
   *
   * @return array
   */
  private function getActiveContractsForPeriod(AbsencePeriod $absencePeriod, $filter = []) {
    try {
      $result = civicrm_api3('HRJobContract', 'getactivecontracts', [
        'start_date' => $absencePeriod->start_date,
        'end_date' => $absencePeriod->end_date,
        'api.Contact.getvalue' => ['return' => 'display_name']
      ]);

      foreach($result['values'] as $i => $contract) {
        if(!empty($filter) && !in_array($contract['id'], $filter)) {
          unset($result['values'][$i]);
        } else {
          $result['values'][$i]['contact_display_name'] = $contract['api.Contact.getvalue'];
          unset($result['values'][$i]['api.Contact.getvalue']);
        }
      }

      return $result['values'];
    } catch(\Exception $e) {
      return [];
    }
  }

  /**
   * Adds the Proposed Entitlements fields to this form.
   *
   * These fields are hidden by default, and are visible only if the user chose
   * to override the calculated proposed entitlement.
   *
   * As this is a list of calculations, the field name contains the contract id
   * and the absence type id, in order to make it possible to related the fields
   * to the right calculation.
   *
   * @param $calculations
   */
  private function addProposedEntitlementsFields($calculations) {
    foreach($calculations as $calculation) {
      $fieldName = sprintf(
        'proposed_entitlement[%d][%d]',
        $calculation->getContract()['id'],
        $calculation->getAbsenceType()->id
      );

      $this->add(
        'text',
        $fieldName,
        '',
        ['class' => 'overridden-proposed-entitlement']
      );
    }
  }

  /**
   * Returns a list of enabled Absence Types
   *
   * @return array
   */
  private function getEnabledAbsenceTypes() {
    return CRM_HRLeaveAndAbsences_BAO_AbsenceType::getEnabledAbsenceTypes();
  }

  /**
   * Returns all the Contracts IDs passed through the cid request parameter
   *
   * @return array
   */
  private function getContractsIDsFromRequest() {
    $contractsIDs = empty($_REQUEST['cid']) ? [] : $_REQUEST['cid'];
    if (!is_array($contractsIDs)) {
      $contractsIDs = [$contractsIDs];
    }

    return $contractsIDs;
  }

  /**
   * Exports a CSV File containing all the given calculations.
   *
   * This function takes into account if any of the proposed entitlements were
   * overridden on the page and will include the overridden value in the CSV.
   *
   * @param array $calculations
   *   An array of CRM_HRLeaveAndAbsences_EntitlementCalculation objects
   */
  private function exportCSV($calculations) {
    $headers = [
      'employee_id',
      'employee_name',
      'leave_type',
      'prev_year_entitlement',
      'days_taken',
      'remaining',
      'brought_forward',
      'contractual_entitlement',
      'period_pro_rata',
      'proposed_entitlement',
      'overridden'
    ];

    $formValues = $this->exportValues();

    $rows = [];
    foreach($calculations as $calculation) {
      $contractID = $calculation->getContract()['id'];
      $absenceTypeID = $calculation->getAbsenceType()->id;

      $row = [
        'employee_id' => $contractID,
        'employee_name' => $calculation->getContract()['contact_display_name'],
        'leave_type' => $calculation->getAbsenceType()->title,
        'prev_year_entitlement' => $calculation->getPreviousPeriodProposedEntitlement(),
        'days_taken' => $calculation->getNumberOfLeavesTakenOnThePreviousPeriod(),
        'remaining' => $calculation->getNumberOfDaysRemainingInThePreviousPeriod(),
        'brought_forward' => $calculation->getBroughtForward(),
        'contractual_entitlement' => $calculation->getContractualEntitlement(),
        'period_pro_rata' => $calculation->getProRata(),
        'proposed_entitlement' => $calculation->getProposedEntitlement(),
        'overridden' => 0
      ];

      if(!empty($formValues['proposed_entitlement'][$contractID][$absenceTypeID])) {
        $row['proposed_entitlement'] = $formValues['proposed_entitlement'][$contractID][$absenceTypeID];
        $row['overridden'] = 1;
      }

      $rows[] = $row;
    }

    CRM_Core_Report_Excel::writeCSVFile('entitlement_calculations', $headers, $rows);
    CRM_Utils_System::civiExit();
  }
}
