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
   * The Entitlement Calculations for this form AbsencePeriod
   *
   * @var CRM_HRLeaveAndAbsences_EntitlementCalculation[]
   */
  private $calculations;

  /**
   * The AbsencePeriod loaded with the ID passed to this form
   *
   * @var CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   */
  private $absencePeriod;

  /**
   * An array used to store the values processed by the setDefaultValues method
   *
   * @var array
   */
  private $defaultValues = [];

  /**
   * If any contract has a previously calculated entitlement, and it has a
   * comment, we add the comment to the array of default values, so it will be
   * available/visible on the form
   *
   * @return array
   */
  public function setDefaultValues()
  {
    if(!empty($this->calculations) && empty($this->defaultValues)) {
      $this->defaultValues = [];
      foreach($this->calculations as $calculation) {
        if($calculation->getCurrentPeriodEntitlementComment()) {
          $contractID = $calculation->getContract()['id'];
          $absenceTypeID = $calculation->getAbsenceType()->id;
          $this->defaultValues['comment'][$contractID][$absenceTypeID] = $calculation->getCurrentPeriodEntitlementComment();
        }
      }
    }

    return $this->defaultValues;
  }

  /**
   * {@inheritdoc}
   */
  public function buildQuickForm() {
    $this->setReturnUrl();
    $this->absencePeriod = $this->getAbsencePeriodFromRequest();
    $this->setFormPageTitle($this->absencePeriod);

    $this->calculations = $this->getEntitlementCalculations($this->absencePeriod);

    $this->addProposedEntitlementAndCommentFields();

    $exportCSV = CRM_Utils_Request::retrieve('export_csv', 'Integer');
    if($exportCSV) {
      $this->exportCSV();
    }

    $this->addButtons($this->getAvailableButtons());
    $this->assign('period', $this->absencePeriod);
    $this->assign('contractsIDs', $this->getContractsIDsFromRequest());
    $this->assign('calculations', $this->calculations);
    $this->assign('enabledAbsenceTypes', $this->getEnabledAbsenceTypes());

    CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/hrleaveandabsences.css', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/inputmask.min.js');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/inputmask.numeric.extensions.min.js');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/hrleaveandabsences.form.manage_entitlements.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    parent::buildQuickForm();
  }

  /**
   * {@inheritdoc}
   */
  public function postProcess()
  {
    $values = $this->exportValues();
    foreach($this->calculations as $calculation) {
      $absenceTypeID = $calculation->getAbsenceType()->id;
      $contractID = $calculation->getContract()['id'];

      CRM_HRLeaveAndAbsences_BAO_Entitlement::saveFromCalculation(
        $calculation,
        $values['proposed_entitlement'][$contractID][$absenceTypeID],
        $values['comment'][$contractID][$absenceTypeID]
      );
    }

    CRM_Core_Session::setStatus(ts('Entitlements successfully updated'), 'Success', 'success');

    $session = CRM_Core_Session::singleton();
    $url =  $session->get('ManageEntitlementsReturnUrl');
    //We won't need this value anymore, so let's remove it from the session
    $session->set('ManageEntitlementsReturnUrl', null);

    if(!$url) {
      $contractsIDs = $this->getContractsIDsFromRequest();
      $url = CRM_Utils_System::url(
        'civicrm/admin/leaveandabsences/periods/manage_entitlements',
        'reset=1&id='.$this->absencePeriod->id . '&' . http_build_query(['cid' => $contractsIDs])
      );
    }
    $session->replaceUserContext($url);
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
    if(!$periodId) {
      return AbsencePeriod::getCurrentPeriod();
    }
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
      $contracts = CRM_Hrjobcontract_BAO_HRJobContract::getActiveContracts(
        $absencePeriod->start_date,
        $absencePeriod->end_date
      );

      foreach($contracts as $i => $contract) {
        if(!empty($filter) && !in_array($contract['contact_id'], $filter)) {
          unset($contracts[$i]);
        }
      }

      $contactsIDs = array_column($contracts, 'contact_id');
      $result = civicrm_api3('Contact', 'get', [
        'return' => ['display_name'],
        'id' => ['IN' => $contactsIDs],
        'options' => ['limit' => count($contactsIDs)],
      ]);

      $contacts = $result['values'];
      foreach($contracts as $i => $contract) {
        $contactID = $contract['contact_id'];
        if(isset($contacts[$contactID])) {
          $contracts[$i]['contact_display_name'] = $contacts[$contactID]['display_name'];
        }
      }

      return $contracts;
    } catch(\Exception $e) {
      return [];
    }
  }

  /**
   * Adds the Proposed Entitlements and the Comments fields to this form.
   *
   * These fields are hidden by default. The Proposed Entitlement field is
   * visible only if the user chose to override the calculated proposed
   * entitlement.
   *
   * As this is a list of calculations, the field names contains the contract id
   * and the absence type id, in order to make it possible to related the fields
   * to the right calculation.
   *
   */
  private function addProposedEntitlementAndCommentFields() {
    foreach($this->calculations as $calculation) {
      $proposedEntitlementFieldName = sprintf(
        'proposed_entitlement[%d][%d]',
        $calculation->getContract()['id'],
        $calculation->getAbsenceType()->id
      );

      $this->add(
        'text',
        $proposedEntitlementFieldName,
        '',
        [
          'class' => 'overridden-proposed-entitlement',
          'maxlength' => 4
        ]
      );

      $commentFieldName = sprintf(
        'comment[%d][%d]',
        $calculation->getContract()['id'],
        $calculation->getAbsenceType()->id
      );
      $this->add(
        'textarea',
        $commentFieldName,
        '',
        ['class' => 'comment-text']
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
   * Exports a CSV File containing all this form's calculations.
   *
   * This function takes into account if any of the proposed entitlements were
   * overridden on the page and will include the overridden value in the CSV.
   */
  private function exportCSV() {
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
    foreach($this->calculations as $calculation) {
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

  /**
   * Get the list of action buttons available to this form
   *
   * @return array
   */
  private function getAvailableButtons() {
    $buttons = [
      [
        'type'      => 'next',
        'class'     => 'save-new-entitlements-button',
        'name'      => ts('Save new entitlements'),
        'isDefault' => TRUE
      ],
    ];

    return $buttons;
  }

  /**
   * To be able to return to the URL the user was before comming to this form,
   * we store the Referer in the session.
   *
   */
  private function setReturnUrl() {
    $session = CRM_Core_Session::singleton();
    $referer = CRM_Utils_System::refererPath();
    $vars = [];
    parse_str(parse_url($referer, PHP_URL_QUERY), $vars);
    if(!empty($vars['q']) && $this->isValidReturnPath($vars['q'])) {
      $q = $vars['q'];
      unset($vars['q']);
      $url = CRM_Utils_System::url($q, http_build_query($vars));
      $session->set('ManageEntitlementsReturnUrl', $url);
    }
  }

  /**
   * Checks if the given path is valid to be used as part of the return URL.
   *
   * A path is valid to be used as a return URL if it is a CiviCRM path (that
   * is, the q parameter starts with "civicrm/") and is different from the form
   * path.
   *
   * @param string $path
   *
   * @return bool
   */
  private function isValidReturnPath($path) {
    return strpos($path, 'civicrm/') === 0 &&
           $path != 'civicrm/admin/leaveandabsences/periods/manage_entitlements';
  }
}
