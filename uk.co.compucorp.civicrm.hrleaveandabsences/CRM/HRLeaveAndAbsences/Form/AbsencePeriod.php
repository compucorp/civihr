<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_HRLeaveAndAbsences_Form_AbsencePeriod extends CRM_Core_Form {

  /**
   * When in edit mode, this is the ID of the AbsencePeriod being edited
   *
   * @var int
   */
  protected $_id = null;

  /**
   * An array used to store the loaded AbsencePeriods's default values, so we
   * only need to load them once.
   *
   * @var array
   */
  private $defaultValues = [];

  /**
   * {@inheritdoc}
   */
  public function setDefaultValues() {
    if (empty($this->defaultValues)) {
      if ($this->_id) {
        $this->defaultValues = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::getValuesArray($this->_id);
        $this->defaultValues['_id'] = $this->_id;
      }
      else {
        $this->defaultValues = [
          'start_date' => CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::getMostRecentStartDateAvailable(),
          'weight' => CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::getMaxWeight() + 1
        ];
      }
    }

    return $this->defaultValues;
  }

  /**
   * {@inheritdoc}
   */
  public function buildQuickForm() {
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);

    $this->addFields();
    $this->addFieldsRules();
    $this->addButtons($this->getAvailableButtons());

    CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/hrleaveandabsences.css');
    CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/leaveandabsence.css');
    CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/hrleaveandabsences.form.absenceperiod.js');
    parent::buildQuickForm();
  }

  /**
   * {@inheritdoc}
   */
  public function postProcess() {
    if ($this->_action & (CRM_Core_Action::ADD | CRM_Core_Action::UPDATE)) {
      // store the submitted values in an array
      $params = $this->exportValues();
      $params['start_date'] = !empty($params['start_date']) ? CRM_Utils_Date::processDate($params['start_date']) : NULL;
      $params['end_date'] = !empty($params['end_date']) ? CRM_Utils_Date::processDate($params['end_date']) : NULL;

      if ($this->_action & CRM_Core_Action::UPDATE) {
        $params['id'] = $this->_id;
      }

      $actionDescription = ($this->_action & CRM_Core_Action::UPDATE) ? 'updated' : 'created';
      try {
        $absenceType = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::create($params);
        CRM_Core_Session::setStatus(
          ts("The Absence Period '%1' has been $actionDescription.", [1 => $absenceType->title]),
          'Success',
          'success'
        );
      } catch (CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException $ex) {
        $message = ts("The Absence Period could not be $actionDescription.");
        $message .= ' ' . $ex->getMessage();
        CRM_Core_Session::setStatus($message, 'Error', 'error');
      }

      $url = CRM_Utils_System::url('civicrm/admin/leaveandabsences/periods', 'reset=1&action=browse');
      $session = CRM_Core_Session::singleton();
      $session->replaceUserContext($url);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultEntity() {
    return 'AbsencePeriod';
  }

  /**
   * Adds all the fields to the form
   */
  private function addFields() {
    // This hidden field is used to get the AbsencePeriod
    // id when validating the Order number before submitting the form
    $this->add('hidden', '_id');
    $this->add(
      'text',
      'title',
      ts('Title'),
      $this->getDAOFieldAttributes('title'),
      true
    );
    $this->add(
      'datepicker',
      'start_date',
      ts('Start Date'),
      $this->getDAOFieldAttributes('start_date'),
      true,
      ['time' => false]
    );
    $this->add(
      'datepicker',
      'end_date',
      ts('End Date'),
      $this->getDAOFieldAttributes('end_date'),
      true,
      ['time' => false]
    );
    $this->add(
      'number',
      'weight',
      ts('Order'),
      $this->getDAOFieldAttributes('weight'),
      true
    );
  }

  /**
   * A helper method to call the CRM_Core_DAO::getAttribute to get the
   * fields attributes of the AbsencePeriod BAO
   *
   * @param $field - The name of a field of the AbsencePeriod BAO
   *
   * @return array - The attributes returned by CRM_Core_DAO::getAttribute
   */
  private function getDAOFieldAttributes($field) {
    $dao = 'CRM_HRLeaveAndAbsences_DAO_AbsencePeriod';
    return CRM_Core_DAO::getAttribute($dao, $field);
  }

  /**
   * Add validation rules for the fields on this form.
   */
  private function addFieldsRules() {
    $this->addRule('weight', ts('The Order should be a positive number'), 'positiveInteger');
    $this->addFormRule([$this, 'formRules']);
  }

  /**
   * Execute validations concerning all the form fields.
   *
   * Different from regular field rules, Form Rules can be used for validations
   * that depends on multiple fields,(for example, if field X is not empty,
   * then Y is required). Here, we use it to validate the dates, because start
   * date can't be greater or equal to end date.
   *
   * @param array $values An array containing all the form's fields values
   *
   * @return array|bool Returns true if form is valid. Otherwise, an
   *                    array containing all the validation errors is returned.
   */
  public function formRules($values) {
    $errors = [];
    $this->validatePeriodDates($values, $errors);

    return empty($errors) ? true : $errors;
  }

  /**
   * Validates if Start and End Dates are valid and if Start Date is less than
   * End Date
   *
   * @param array $values An array containing all the form's fields values
   * @param array $errors A reference to the errors array where errors will be
   *                      added if dates are invalid
   */
  private function validatePeriodDates($values, &$errors) {
    if(empty($values['start_date']) || empty($values['end_date'])) {
      return;
    }

    $startDateIsValid = CRM_HRLeaveAndAbsences_Validator_Date::isValid($values['start_date'], 'Y-m-d');
    $endDateIsValid = CRM_HRLeaveAndAbsences_Validator_Date::isValid($values['end_date'], 'Y-m-d');

    if(!$startDateIsValid) {
      $errors['start_date'] = ts('Start Date should be a valid date');
    }

    if(!$endDateIsValid) {
      $errors['end_date'] = ts('End Date should be a valid date');
    }

    $datesAreValid = $startDateIsValid && $endDateIsValid;
    $startDate = strtotime($values['start_date']);
    $endDate = strtotime($values['end_date']);
    if($datesAreValid && $startDate >= $endDate) {
      $errors['start_date'] = ts('Start Date should be less than End Date');
    }
  }

  /**
   * Get the list of action buttons available to this form
   *
   * @return array
   */
  private function getAvailableButtons() {
    $buttons = [
      ['type' => 'next', 'name' => ts('Save'), 'isDefault' => true],
      ['type' => 'cancel', 'name' => ts('Cancel')],
    ];

    return $buttons;
  }
}
