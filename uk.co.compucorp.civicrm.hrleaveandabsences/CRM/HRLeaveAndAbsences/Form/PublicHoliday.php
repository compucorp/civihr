<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_HRLeaveAndAbsences_Form_PublicHoliday extends CRM_Core_Form {

  private $defaultValues = [];

  /**
   * {@inheritdoc}
   */
  public function setDefaultValues() {
    if(empty($this->defaultValues)) {
      if ($this->_id) {
        $this->defaultValues = CRM_HRLeaveAndAbsences_BAO_PublicHoliday::getValuesArray($this->_id);
      } else {
        $this->defaultValues = [
          'id' => null,
          'title' => '',
          'date' => '',
          'is_active' => 1,
        ];
      }
    }

    return $this->defaultValues;
  }

  /**
   * {@inheritdoc}
   */
  public function buildQuickForm() {
    $this->_id = CRM_Utils_Request::retrieve('id' , 'Positive', $this);

    $this->addFields();
    $this->addFieldsRules();

    $this->addButtons($this->getAvailableButtons());
    $this->assign('deleteUrl', $this->getDeleteUrl());

    CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/hrleaveandabsences.css');
    CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/leaveandabsence.css');
    parent::buildQuickForm();
  }

  /**
   * {@inheritdoc}
   */
  public function postProcess() {
    if ($this->_action & (CRM_Core_Action::ADD | CRM_Core_Action::UPDATE)) {
        // store the submitted values in an array
        $params = $this->exportValues();
        $params['date'] = !empty($params['date']) ? CRM_Utils_Date::processDate($params['date']) : NULL;

        if ($this->_action & CRM_Core_Action::UPDATE) {
            $params['id'] = $this->_id;
        }

        //when a checkbox is not checked, it is not sent on the request
        //so we check if it wasn't sent and set the param value to 0
        $checkboxFields = ['is_active'];
        foreach ($checkboxFields as $field) {
            if(!array_key_exists($field, $params)) {
                $params[$field] = 0;
            }
        }

        $actionDescription = ($this->_action & CRM_Core_Action::UPDATE) ? 'updated' : 'created';
        try {
            $publicHoliday = CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create($params);
            CRM_Core_Session::setStatus(ts("The Public Holiday '%1' has been $actionDescription.", array( 1 => $publicHoliday->title)), 'Success', 'success');
        } catch(CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException $ex) {
            $message = ts("The Public Holiday could not be $actionDescription.");
            $message .= ' ' . $ex->getMessage();
            CRM_Core_Session::setStatus($message, 'Error', 'error');
        }

        $url = CRM_Utils_System::url('civicrm/admin/leaveandabsences/public_holidays', 'reset=1&action=browse');
        $session = CRM_Core_Session::singleton();
        $session->replaceUserContext($url);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultEntity() {
    return 'PublicHoliday';
  }

  /**
   * Adds fields to the Form.
   */
  private function addFields() {
    $this->add(
      'text',
      'title',
      ts('Title'),
      $this->getDAOFieldAttributes('title'),
      true
    );
    $this->add(
      'datepicker',
      'date',
      ts('Date'),
      $this->getDAOFieldAttributes('date'),
      true,
      ['time' => false]
    );
    $this->add(
      'checkbox',
      'is_active',
      ts('Enabled')
    );
  }

  /**
   * Return an array containing attributes of given field.
   *
   * @param string $field
   * @return array|null
   */
  private function getDAOFieldAttributes($field) {
    $dao = 'CRM_HRLeaveAndAbsences_DAO_PublicHoliday';
    return CRM_Core_DAO::getAttribute($dao, $field);
  }

  /**
   * Add validation rules for the fields on this form.
   */
  private function addFieldsRules() {
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
    $this->validateDate($values, $errors);
    return empty($errors) ? true : $errors;
  }

  /**
   * Validates date.
   *
   * @param array $values An array containing all the form's fields values
   * @param array $errors A reference to the errors array where errors will be
   *                      added if dates are invalid
   */
  private function validateDate($values, &$errors) {
    if (empty($values['date'])) {
      return;
    }

    $dateIsValid = CRM_HRLeaveAndAbsences_Validator_Date::isValid($values['date'], 'Y-m-d');
    if(!$dateIsValid) {
      $errors['date'] = ts('Date should be a valid date');
    }
  }

  private function getAvailableButtons() {
    $buttons = [
      [ 'type' => 'next', 'name' => ts('Save'), 'isDefault' => true ],
      [ 'type' => 'cancel', 'name' => ts('Cancel') ],
    ];
    if ($this->_action & CRM_Core_Action::UPDATE) {
        $buttons[] = [ 'type' => 'delete', 'name' => ts('Delete') ];
    }
    return $buttons;
  }

  private function getDeleteUrl() {
    return CRM_Utils_System::url(
      'civicrm/admin/leaveandabsences/public_holidays',
      "action=delete&id={$this->_id}&reset=1",
      false,
      null,
      false
    );
  }
}
