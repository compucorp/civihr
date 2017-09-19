<?php

require_once 'CRM/Core/Form.php';

use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;
use CRM_HRLeaveAndAbsences_Service_EntitlementCalculator as EntitlementCalculator;

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_HRLeaveAndAbsences_Form_ManageEntitlements extends CRM_Core_Form {

  /**
   * The Entitlement Calculations for this form AbsencePeriod
   *
   * @var CRM_HRLeaveAndAbsences_Service_EntitlementCalculation[]
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
   * If any contact has a previously calculated entitlement, and it has a
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
        $contactID = $calculation->getContact()['id'];
        $absenceTypeID = $calculation->getAbsenceType()->id;
        if($calculation->getCurrentPeriodEntitlementComment()) {
          $this->defaultValues['comment'][$contactID][$absenceTypeID] = $calculation->getCurrentPeriodEntitlementComment();
        }

        if($calculation->isCurrentPeriodEntitlementOverridden()) {
          $this->defaultValues['overridden_entitlement'][$contactID][$absenceTypeID] = $calculation->getOverriddenEntitlement();
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

    $session = CRM_Core_Session::singleton();

    $this->addButtons($this->getAvailableButtons());
    $this->assign('period', $this->absencePeriod);
    $this->assign('contactsIDs', $this->getContactsIDsFromRequest());
    $this->assign('calculations', $this->calculations);
    $this->assign('enabledAbsenceTypes', $this->getEnabledAbsenceTypes());
    $this->assign('returnUrl', $session->get('ManageEntitlementsReturnUrl'));

    CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/leaveandabsence.css', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/vendor/inputmask.min.js');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/vendor/inputmask.numeric.extensions.min.js');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/hrleaveandabsences.form.manage_entitlements.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    parent::buildQuickForm();
  }

  /**
   * {@inheritdoc}
   */
  public function postProcess() {
    $values = $this->exportValues();
    foreach($this->calculations as $calculation) {
      $absenceTypeID = $calculation->getAbsenceType()->id;
      $contactID = $calculation->getContact()['id'];

      LeavePeriodEntitlement::saveFromCalculation(
        $calculation,
        $values['overridden_entitlement'][$contactID][$absenceTypeID],
        $values['comment'][$contactID][$absenceTypeID]
      );
    }

    CRM_Core_Session::setStatus(ts('Entitlements successfully updated'), 'Success', 'success');

    $session = CRM_Core_Session::singleton();

    $nextPeriod = $this->absencePeriod->getNextPeriod();
    if ($nextPeriod) {
      $url = $this->createManageEntitlementsURL($nextPeriod);
    } else {
      $url =  $session->get('ManageEntitlementsReturnUrl');
      //We won't need this value anymore, so let's remove it from the session
      $session->set('ManageEntitlementsReturnUrl', null);

      if(!$url) {
        $url = $this->createManageEntitlementsURL($this->absencePeriod);
      }
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
   * Creates EntitlementCalculation instances for every contact with active
   * contracts during the given AbsencePeriod.
   *
   * @param CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $absencePeriod
   *
   * @return array An array containing all the EntitlementCalculations created
   */
  private function getEntitlementCalculations(AbsencePeriod $absencePeriod) {
    $contacts    = $this->getContactsForCalculation($absencePeriod);
    $calculator   = new EntitlementCalculator($absencePeriod);
    $calculations = [];
    foreach ($contacts as $contact) {
      $calculations = array_merge($calculations,
        $calculator->calculateEntitlementsFor($contact));
    }
    return $calculations;
  }

  /**
   * Returns a list of contacts to run the entitlement calculation for.
   *
   * By default, it returns all the Contacts which have at least one
   * contract during the given Absence Period. If the request contains the
   * "cid" parameter, it will only return the the contact with the give IDs
   * (only if they have active contracts).
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $absencePeriod
   *
   * @return array
   */
  private function getContactsForCalculation(AbsencePeriod $absencePeriod) {
    $contactsIDs = $this->getContactsIDsFromRequest();
    return $this->getContactsWithContractsInPeriod($absencePeriod, $contactsIDs);
  }

  /**
   * Returns an array containing all contacts with contracts for the given
   * AbsencePeriod.
   *
   * This method uses the HRJobContract API to retrieve the contacts, so the
   * return values will be an array, as this is how they are returned by the
   * API.
   *
   * It is possible to filter the returned list to include only specific Contacts.
   * For that, you need pass a list of contact IDs to the $filter parameter.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $absencePeriod
   * @param array $filter
   *  A list of Contact IDs that will be used to filter the returned contacts
   *
   * @return array
   */
  private function getContactsWithContractsInPeriod(AbsencePeriod $absencePeriod, $filter = []) {
    try {
      $contacts = civicrm_api3('HRJobContract', 'getcontactswithcontractsinperiod' , [
        'start_date' => $absencePeriod->start_date,
        'end_date' => $absencePeriod->end_date
      ])['values'];

      if(!empty($filter)) {
        $contacts = array_filter($contacts, function($contact) use($filter) {
          return in_array($contact['id'], $filter);
        });
      }

      return $contacts;
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
   * As this is a list of calculations, the field names contains the contact id
   * and the absence type id, in order to make it possible to related the fields
   * to the right calculation.
   *
   */
  private function addProposedEntitlementAndCommentFields() {
    foreach($this->calculations as $calculation) {
      $proposedEntitlementFieldName = sprintf(
        'overridden_entitlement[%d][%d]',
        $calculation->getContact()['id'],
        $calculation->getAbsenceType()->id
      );

      $this->add(
        'text',
        $proposedEntitlementFieldName,
        '',
        [
          'class' => 'overridden-proposed-entitlement',
          'maxlength' => 4,
          'size' => 4
        ]
      );

      $commentFieldName = sprintf(
        'comment[%d][%d]',
        $calculation->getContact()['id'],
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
   * Returns all the Contacts IDs passed through the cid request parameter
   *
   * @return array
   */
  private function getContactsIDsFromRequest() {
    $contactsIDs = empty($_REQUEST['cid']) ? [] : $_REQUEST['cid'];
    if (!is_array($contactsIDs)) {
      $contactsIDs = [$contactsIDs];
    }

    return $contactsIDs;
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
      'period_pro_rata',
      'proposed_entitlement',
      'overridden'
    ];

    $formValues = $this->exportValues();

    $rows = [];
    foreach($this->calculations as $calculation) {
      $contactID = $calculation->getContact()['id'];
      $absenceTypeID = $calculation->getAbsenceType()->id;

      $row = [
        'employee_id' => $contactID,
        'employee_name' => $calculation->getContact()['display_name'],
        'leave_type' => $calculation->getAbsenceType()->title,
        'prev_year_entitlement' => $calculation->getPreviousPeriodProposedEntitlement(),
        'days_taken' => $calculation->getNumberOfDaysTakenOnThePreviousPeriod(),
        'remaining' => $calculation->getNumberOfDaysRemainingInThePreviousPeriod(),
        'brought_forward' => $calculation->getBroughtForward(),
        'period_pro_rata' => $calculation->getProRata(),
        'proposed_entitlement' => $calculation->getProposedEntitlement(),
        'overridden' => 0
      ];

      if(!empty($formValues['overridden_entitlement'][$contactID][$absenceTypeID])) {
        $row['proposed_entitlement'] = $formValues['overridden_entitlement'][$contactID][$absenceTypeID];
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
    $buttonName = ts('Save new entitlements');
    if($this->hasMorePeriodsToCalculate()) {
      $buttonName = ts('Save and go to the next period');
    }
    $buttons = [
      [
        'type'      => 'next',
        'class'     => 'save-new-entitlements-button',
        'name'      => $buttonName,
        'isDefault' => TRUE
      ],
    ];

    return $buttons;
  }

  /**
   * To be able to return to the URL the user was before coming to this form,
   * we store the return URL in the session.
   */
  private function setReturnUrl() {
    $session = CRM_Core_Session::singleton();
    $returnUrl = isset($_GET['returnUrl']) ? urldecode($_GET['returnUrl']) : '';
    $vars = [];
    parse_str(parse_url($returnUrl, PHP_URL_QUERY), $vars);
    //q will not exist in $vars for sites with clean Urls enabled
    $url = empty($vars['q']) ? $returnUrl : $vars['q'];
    //we need to trim preceding slash when clean url is enabled
    if (!empty($url) && $this->isValidReturnPath(ltrim($url, '/'))) {
      $returnUrl = filter_var($returnUrl, FILTER_SANITIZE_URL);
      $session->set('ManageEntitlementsReturnUrl', $returnUrl);
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

  /**
   * Creates an URL to manage the entitlements for the given Absence Period
   *
   * If there are any cid query string parameters on the current request, they
   * will all be addeed to the URL
   *
   * @return string
   *  The created URL
   */
  private function createManageEntitlementsURL(AbsencePeriod $period) {
    $contactsIDs = $this->getContactsIDsFromRequest();
    $url         = CRM_Utils_System::url(
      'civicrm/admin/leaveandabsences/periods/manage_entitlements',
      'reset=1&id=' . $period->id . '&' . http_build_query(['cid' => $contactsIDs])
    );
    return $url;
  }

  /**
   * Returns true is there are more Absence Periods available to calculate the
   * entitlements
   *
   * @return bool
   */
  private function hasMorePeriodsToCalculate() {
    return $this->absencePeriod->getNextPeriod() != null;
  }
}
