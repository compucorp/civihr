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

    $this->assign('period', $absencePeriod);
    $this->assign('calculations', $calculations);

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
    $contracts    = $this->getActiveContractsForPeriod($absencePeriod);
    $calculator   = new EntitlementCalculator($absencePeriod);
    $calculations = [];
    foreach ($contracts as $contract) {
      $calculations = array_merge($calculations,
        $calculator->calculateEntitlementsFor($contract));
    }
    return $calculations;
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
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $absencePeriod
   *
   * @return array
   */
  private function getActiveContractsForPeriod(AbsencePeriod $absencePeriod) {
    try {
      $result = civicrm_api3('HRJobContract', 'getactivecontracts', [
        'start_date' => $absencePeriod->start_date,
        'end_date' => $absencePeriod->end_date,
        'api.Contact.getvalue' => ['return' => 'display_name']
      ]);

      array_walk($result['values'], function(&$item) {
        $item['contact_display_name'] = $item['api.Contact.getvalue'];
        unset($item['api.Contact.getvalue']);
      });

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
}
