<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfig as LeaveRequestCalendarFeedConfig;
use CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException as InvalidLeaveRequestCalendarFeedConfigException;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_HRLeaveAndAbsences_Form_LeaveRequestCalendarFeedConfig extends CRM_Core_Form {

  /**
   * When in edit mode, this is the ID of the LeaveRequestCalendarFeedConfig being edited
   *
   * @var int
   */
  protected $_id = null;

  /**
   * @var array
   *  Holds the default values for all the fields in this form
   */
  private $defaultValues = [];

  /**
   * {@inheritdoc}
   */
  public function setDefaultValues() {
    if(empty($this->defaultValues)) {
      if ($this->_id) {
        $results = LeaveRequestCalendarFeedConfig::getValuesArray($this->_id);
        $this->setDefaultFilterFieldValues('composed_of', $results);
        $this->setDefaultFilterFieldValues('visible_to', $results);
        $this->defaultValues = $results;
        $this->defaultValues['_id'] = $this->_id;
      }
    }

    return $this->defaultValues;
  }

  /**
   * {@inheritdoc}
   */
  public function buildQuickForm() {
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    $this->addCalendarFeedConfigFields();
    $this->addButtons($this->getAvailableButtons());
    $this->assign('deleteUrl', $this->getDeleteUrl());

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

      if ($this->_action & CRM_Core_Action::UPDATE) {
        $params['id'] = $this->_id;
      }

      //when a checkbox is not checked, it is not sent on the request
      //so we check if it wasn't sent and set the param value to 0
      if(!array_key_exists('is_active', $params)) {
        $params['is_active'] = 0;
      }

      $composedOfValues = $this->extractFromFormValues('composed_of', $params);
      $visibleToValues = $this->extractFromFormValues('visible_to', $params);
      $params = array_merge($params, $composedOfValues, $visibleToValues);

      $actionDescription = ($this->_action & CRM_Core_Action::UPDATE) ? 'updated' : 'created';
      try {
        $leaveRequestCalendarFeedConfig = LeaveRequestCalendarFeedConfig::create($params);
        CRM_Core_Session::setStatus(
          ts("The calendar feed '%1' has been $actionDescription.", [1 => $leaveRequestCalendarFeedConfig->title]),
          'Success',
          'success'
        );
      } catch (InvalidLeaveRequestCalendarFeedConfigException $ex) {
        $message = ts("The calendar feed could not be $actionDescription");
        $message .= ' ' . $ex->getMessage();
        CRM_Core_Session::setStatus($message, 'Error', 'error');
      }

      $url = CRM_Utils_System::url('civicrm/admin/leaveandabsences/calendar-feeds', 'reset=1&action=browse');
      $session = CRM_Core_Session::singleton();
      $session->replaceUserContext($url);
    }
  }

  /**
   * Adds the form fields for configuring the calendar feed
   */
  private function addCalendarFeedConfigFields() {
    $this->add(
      'text',
      'title',
      ts('Title'),
      $this->getDAOFieldAttributes('title'),
      TRUE
    );

    $this->addSelect(
      'composed_of_department',
      [
        'options' => $this->getDepartmentsList(),
        'multiple' => TRUE,
        'placeholder' => 'Filter by Department',
        'label' => 'Staff to include',
      ]
    );

    $this->addSelect(
      'composed_of_location',
      [
        'options' => $this->getLocationsList(),
        'multiple' => TRUE,
        'placeholder' => 'Filter by Location',
        'label' => '',
      ]
    );

    $this->addSelect(
      'visible_to_department',
      [
        'options' => $this->getDepartmentsList(),
        'multiple' => TRUE,
        'placeholder' => 'Filter by Department',
        'label' => 'Share link with',
      ]
    );

    $this->addSelect(
      'visible_to_location',
      [
        'options' => $this->getLocationsList(),
        'multiple' => TRUE,
        'placeholder' => 'Filter by Location',
        'label' => '',
      ]
    );

    $this->addSelect(
      'composed_of_leave_type',
      [
        'options' => $this->getAbsenceTypesList(),
        'multiple' => TRUE,
        'placeholder' => 'Select leave types',
        'label' => 'Leave types to include',
      ],
      true
    );

    $this->addSelect(
      'timezone',
      [
        'options' => $this->getTimezonesList(),
        'multiple' => FALSE,
        'placeholder' => 'Select a timezone',
        'label' => 'Timezone',
      ],
      true
    );

    $this->add(
      'checkbox',
      'is_active',
      ts('Enabled')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultEntity() {
    return 'LeaveRequestCalendarFeedConfig';
  }

  /**
   * Returns the timezones list.
   *
   * @return array
   */
  private function getTimezonesList() {
    $timeZones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

    return array_combine($timeZones, $timeZones);
  }

  /**
   * Gets the attributes for the given field from the DAO metadata
   *
   * @param string $field
   *
   * @return array
   */
  private function getDAOFieldAttributes($field) {
    $dao = 'CRM_HRLeaveAndAbsences_DAO_LeaveRequestCalendarFeedConfig';
    return CRM_Core_DAO::getAttribute($dao, $field);
  }

  /**
   * Returns the action URL used to delete a Absence Type
   *
   * @return string
   */
  private function getDeleteUrl() {
    return CRM_Utils_System::url(
      'civicrm/admin/leaveandabsences/calendar-feeds',
      "action=delete&id={$this->_id}&reset=1",
      false,
      null,
      false
    );
  }

  /**
   * Returns a list of buttons available on this form.
   *
   * @return array
   */
  private function getAvailableButtons() {
    $buttons = [
      ['type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE],
      ['type' => 'cancel', 'name' => ts('Cancel')],
    ];

    if (($this->_action & CRM_Core_Action::UPDATE)) {
      $buttons[] = ['type' => 'delete', 'name' => ts('Delete')];
    }

    return $buttons;
  }

  /**
   * Returns the departments list.
   *
   * @return array
   */
  private function getDepartmentsList() {
    return $this->getOptionValuesList('hrjc_department');
  }

  /**
   * Returns the locations list.
   *
   * @return array
   */
  private function getLocationsList() {
    return $this->getOptionValuesList('hrjc_location');
  }

  /**
   * Returns the values for an option group formatted for a
   * select list options.
   *
   * @param array $optionGroupName
   *
   * @return array
   */
  private function getOptionValuesList($optionGroupName) {
    $result = civicrm_api3('OptionValue', 'get', [
      'return' => ['label', 'value'],
      'option_group_id' => $optionGroupName,
      'is_active' => 1,
    ]);

    return array_column($result['values'], 'label', 'value');
  }

  /**
   * Returns enabled absence types.
   *
   * @return array
   */
  private function getAbsenceTypesList() {
    $absenceTypes = AbsenceType::getEnabledAbsenceTypes();
    $absenceTypesList = [];

    foreach ($absenceTypes as $absenceType) {
      $absenceTypesList[$absenceType->id] = $absenceType->title;
    }

    return $absenceTypesList;
  }

  /**
   * Extracts the filter field values for composed_of and visible_to
   * filters in a format expected by the BAO create method.
   *
   * @param string $filterName
   * @param array $params
   *
   * @return mixed
   */
  private function extractFromFormValues($filterName, $params) {
    $formValues[$filterName] = [];
    foreach($params as $field => $value) {
      if(strpos($field, $filterName) === 0 && !empty($value)) {
        $filterFieldName = str_replace($filterName . '_', '', $field);
        $formValues[$filterName][$filterFieldName] = $value;
      }
    }

    return $formValues;
  }

  /**
   * Sets the default form field values for the composed_of and visible_to
   * filter fields.
   *
   * @param string $filterName
   * @param array $params
   */
  private function setDefaultFilterFieldValues($filterName, &$params) {
    foreach ($params as $field => $value) {
      if ($field === $filterName) {
        foreach($value as $itemName => $itemValue) {
          $params[$filterName . '_' . $itemName] = $itemValue;
        }
      }
    }
  }
}
