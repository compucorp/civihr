<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_HRLeaveAndAbsences_Form_WorkPattern extends CRM_Core_Form
{

    /**
     * The maximum number of weeks this form can handle
     */
    const MAX_NUMBER_OF_WEEKS = 5;

    /**
     * An array used to store the loaded WorkPattern's default values, so we
     * only need to load them once.
     *
     * @var array
     */
    private $defaultValues = [];

    /**
     * When in edit mode, this is the ID of the WorkPattern being edited
     *
     * @var int
     */
    protected $_id = null;

    /**
     * {@inheritdoc}
     */
    public function setDefaultValues() {
      if (empty($this->defaultValues)) {
        if ($this->_id) {
          $this->defaultValues = CRM_HRLeaveAndAbsences_BAO_WorkPattern::getValuesArray($this->_id);
          $this->setIsVisibleForWeeksInDefaultValues();

        } else {
          $this->defaultValues = [
            'is_active' => 1,
            'weeks'     => [
              // this is used to simulate an existing week, so we can have the
              // first week visible when adding a new pattern
              [
                'is_visible' => true,
                'days' => [
                  ['type' => CRM_HRLeaveAndAbsences_BAO_WorkDay::getWorkingDayTypeValue()],
                  ['type' => CRM_HRLeaveAndAbsences_BAO_WorkDay::getWorkingDayTypeValue()],
                  ['type' => CRM_HRLeaveAndAbsences_BAO_WorkDay::getWorkingDayTypeValue()],
                  ['type' => CRM_HRLeaveAndAbsences_BAO_WorkDay::getWorkingDayTypeValue()],
                  ['type' => CRM_HRLeaveAndAbsences_BAO_WorkDay::getWorkingDayTypeValue()],
                  ['type' => CRM_HRLeaveAndAbsences_BAO_WorkDay::getWeekendTypeValue()],
                  ['type' => CRM_HRLeaveAndAbsences_BAO_WorkDay::getWeekendTypeValue()],
                ]
              ]
            ]
          ];
        }
      }

      return $this->defaultValues;
    }

    /**
     * {@inheritdoc}
     */
    public function buildQuickForm()
    {
        $this->_id = CRM_Utils_Request::retrieve('id' , 'Positive', $this);

        $this->addBasicDetailsFields();
        $this->addCalendarFields();
        $this->addFieldsRules();

        $this->addButtons($this->getAvailableButtons());

        $this->assign('weeks_visibility', $this->getWeeksVisibility());
        $this->assign('weeks_hours', $this->getWeeksNumberOfHours());
        $this->assign('number_of_visible_weeks', $this->getNumberOfVisibleWeeks());
        $this->assign('max_number_of_weeks', self::MAX_NUMBER_OF_WEEKS);
        $this->assign('delete_url', $this->getDeleteUrl());

        CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/hrleaveandabsences.css');
        CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/vendor/inputmask.min.js');
        CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/vendor/inputmask.numeric.extensions.min.js');
        CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/hrleaveandabsences.form.workpattern.js');

        parent::buildQuickForm();
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess()
    {
        if ($this->_action & (CRM_Core_Action::ADD | CRM_Core_Action::UPDATE)) {
            // store the submitted values in an array
            $params = $this->exportValues();
            $params['weeks'] = $this->getWeeksFromSubmittedParams($params);
            if ($this->_action & CRM_Core_Action::UPDATE) {
                $params['id'] = $this->_id;
            }

            //when a checkbox is not checked, it is not sent on the request
            //so we check if it wasn't sent and set the param value to 0
            $checkboxFields = ['is_default', 'is_active'];
            foreach ($checkboxFields as $field) {
                if(!array_key_exists($field, $params)) {
                    $params[$field] = 0;
                }
            }

            $actionDescription = ($this->_action & CRM_Core_Action::UPDATE) ? 'updated' : 'created';
            try {
                $workPattern = CRM_HRLeaveAndAbsences_BAO_WorkPattern::create($params);
                CRM_Core_Session::setStatus(ts("The Work Pattern '%1' has been $actionDescription.", array( 1 => $workPattern->label)), 'Success', 'success');
            } catch(Exception $ex) {
                $message = ts("The Work Pattern could not be $actionDescription.");
                $message .= ' ' . $ex->getMessage();
                CRM_Core_Session::setStatus($message, 'Error', 'error');
            }

            $url = CRM_Utils_System::url('civicrm/admin/leaveandabsences/work_patterns', 'reset=1&action=browse');
            $session = CRM_Core_Session::singleton();
            $session->replaceUserContext($url);
        }
    }

    /**
     * Adds the fields of the Details tab to the form
     */
    private function addBasicDetailsFields()
    {
        $this->add(
            'text',
            'label',
            ts('Label'),
            $this->getDAOFieldAttributes('label'),
            true
        );
        $this->add(
            'text',
            'description',
            ts('Description'),
            $this->getDAOFieldAttributes('description')
        );
        $this->add(
            'checkbox',
            'is_active',
            ts('Enabled')
        );
        $this->add(
            'checkbox',
            'is_default',
            ts('Is default')
        );
    }

    /**
     * Adds the fields of the Calendar tab to the form
     */
    private function addCalendarFields()
    {
      $leaveDaysAmounts = CRM_Core_BAO_OptionValue::getOptionValuesAssocArrayFromName('hrleaveandabsences_leave_days_amounts');
      $daysPerWeek = 7;
      for($i = 0; $i < self::MAX_NUMBER_OF_WEEKS; $i++) {
        $this->add('hidden', "weeks[$i][is_visible]");
        for($j = 0; $j < $daysPerWeek; $j++) {
          $this->add(
            'select',
            "weeks[$i][days][$j][type]",
            false,
            CRM_HRLeaveAndAbsences_BAO_WorkDay::buildOptions('type'),
            false,
            ['class' => 'work-day-type']
          );
          $this->add('text', "weeks[$i][days][$j][time_from]", '', ['maxlength' => 5, 'class' => 'work-day-time']);
          $this->add('text', "weeks[$i][days][$j][time_to]", '', ['maxlength' => 5, 'class' => 'work-day-time']);
          $this->add('text', "weeks[$i][days][$j][break]", '', ['maxlength' => 4, 'class' => 'work-day-break']);
          $this->add('text', "weeks[$i][days][$j][number_of_hours]", '', ['readonly' => 'readonly', 'class' => 'work-day-hours']);
          $this->add(
            'select',
            "weeks[$i][days][$j][leave_days]",
            false,
            $leaveDaysAmounts,
            false,
            ['class' => 'leave-days']
          );
        }
      }
    }

    /**
     * A helper method to call the CRM_Core_DAO::getAttribute to get the
     * fields attributes of the WorkPattern BAO
     *
     * @param $field - The name of a field of the WorkPattern BAO
     *
     * @return array - The attributes returned by CRM_Core_DAO::getAttribute
     */
    private function getDAOFieldAttributes($field)
    {
        $dao = 'CRM_HRLeaveAndAbsences_DAO_WorkPattern';
        return CRM_Core_DAO::getAttribute($dao, $field);
    }

    /**
     * Adds fields validations rules to the form
     */
    private function addFieldsRules()
    {
      $this->addFormRule([$this, 'formRules']);
    }

    /**
     * Execute validations concerning all the form fields.
     *
     * Form Rules can be used for validations that depends on multiple fields,
     * (for example, if field X is not empty, then Y is required). Here, we use
     * it to validate work days, because time from, time to and break are
     * required only if the day is a working day.
     *
     * @param array $values An array containing all the form's fields values
     *
     * @return array|bool Returns true if form is valid. Otherwise, an
     *                    array containing all the validation errors is returned.
     */
    public function formRules($values)
    {
      $errors = [];
      $this->validateWorkDays($values, $errors);

      return empty($errors) ? true : $errors;
    }

    /**
     * Validates each work day of each work week.
     *
     * @param array $values An array containing all the form's fields values
     * @param array $errors An array containing all the validation errors
     */
    private function validateWorkDays($values, &$errors)
    {

      foreach($values['weeks'] as $weekIndex => $week) {
        foreach($week['days'] as $dayIndex => $day) {
          if(!empty($day)) {
            $this->validateWorkDay($weekIndex, $dayIndex, $day, $errors);
          }
        }
      }
    }

    /**
     * Validates a single work day.
     *
     * Since the work day fields are stored in a nested array, we need the
     * weekIndex and dayIndex parameters to be able of properly set an error
     * message for a field.
     *
     * @param int $weekIndex The array index of the week this work day is in
     * @param int $dayIndex The array index of this day, inside the weeks[weekIndex][days] array
     * @param array $day An array containing the work day fields values sent through the form
     * @param array $errors An array where validation errors will be stored
     */
    private function validateWorkDay($weekIndex, $dayIndex, $day, &$errors)
    {
      if($day['type'] == CRM_HRLeaveAndAbsences_BAO_WorkDay::getWorkingDayTypeValue()) {
        $this->validateWorkingDay($weekIndex, $dayIndex, $day, $errors);
      } else {
        $this->validateNonWorkingDay($weekIndex, $dayIndex, $day, $errors);
      }
    }

    /**
     * Validates a single Working Day (a WorkDay where "Working Day?" is Yes).
     *
     * A valid Working Day has:
     * - Time From, Time To and Break not empty
     * - Time From less than Time To
     * - Time From and Time To matching the HH:MM format
     * - Break not larger than the period between Time From and Time To
     *
     * @param int $weekIndex The array index of the week this work day is in
     * @param int $dayIndex The array index of this day, inside the weeks[weekIndex][days] array
     * @param array $day An array containing the work day fields values sent through the form
     * @param array $errors An array where validation errors will be stored
     *
     */
    private function validateWorkingDay($weekIndex, $dayIndex, $day, &$errors) {
      $hasTimeFrom = strlen(trim($day['time_from'])) > 0;
      $hasTimeTo = strlen(trim($day['time_to'])) > 0;
      $hasBreak = strlen(trim($day['break'])) > 0;
      $timeFromField = $this->getWorkDayFieldName($weekIndex, $dayIndex, 'time_from');
      $timeToField = $this->getWorkDayFieldName($weekIndex, $dayIndex, 'time_to');
      $breakField = $this->getWorkDayFieldName($weekIndex, $dayIndex, 'break');

      if (!$hasTimeFrom) {
        $errors[$timeFromField] = ts('Please inform the Time From');
      } else {
        if (!$this->isValidHour($day['time_from'])) {
          $errors[$timeFromField] = ts('Invalid hour');
        }
      }

      if (!$hasTimeTo) {
        $errors[$timeToField] = ts('Please inform the Time To');
      } else {
        if (!$this->isValidHour($day['time_to'])) {
          $errors[$timeToField] = ts('Invalid hour');
        }
      }

      if (!$hasBreak) {
        $errors[$breakField] = ts('Please inform the Break');
      } else {
        if (!is_numeric($day['break'])) {
          $errors[$breakField] = ts('Break should be a valid number');
        }
      }

      $timeFrom = strtotime($day['time_from']);
      $timeTo   = strtotime($day['time_to']);
      if (($hasTimeFrom && $hasTimeTo) && ($timeFrom >= $timeTo)) {
        $errors[$timeFromField] = ts('Time From should be less than Time To');
      }

      $secondsInWorkingHours = $timeTo - $timeFrom;
      $secondsInBreak        = $day['break'] * 3600;
      $hasTimesAndBreak      = $hasTimeFrom && $hasTimeTo && $hasBreak;
      if ($hasTimesAndBreak && $secondsInBreak >= $secondsInWorkingHours) {
        $errors[$breakField] = ts('Break should be less than the number of hours between Time From and Time To');
      }
    }

    /**
     * Validates a single Non Working Day
     *
     * On a Non Working Day, Time From, Time To, Break and Leave Days should be
     * empty
     *
     * @param int $weekIndex The array index of the week this work day is in
     * @param int $dayIndex The array index of this day, inside the weeks[weekIndex][days] array
     * @param array $day An array containing the work day fields values sent through the form
     * @param array $errors An array where validation errors will be stored
     *
     */
    private function validateNonWorkingDay($weekIndex, $dayIndex, $day, &$errors)
    {
      $hasTimeFrom = isset($day['time_from']) && strlen(trim($day['time_from'])) > 0;
      $hasTimeTo = isset($day['time_to']) && strlen(trim($day['time_to'])) > 0;
      $hasBreak = isset($day['break']) && strlen(trim($day['break'])) > 0;
      $timeFromField = $this->getWorkDayFieldName($weekIndex, $dayIndex, 'time_from');
      $timeToField = $this->getWorkDayFieldName($weekIndex, $dayIndex, 'time_to');
      $breakField = $this->getWorkDayFieldName($weekIndex, $dayIndex, 'break');
      $leaveDaysField = $this->getWorkDayFieldName($weekIndex, $dayIndex, 'leave_days');

      if ($hasTimeFrom) {
        $errors[$timeFromField] = ts('Time From should be empty');
      }
      if ($hasTimeTo) {
        $errors[$timeToField] = ts('Time To should be empty');
      }
      if ($hasBreak) {
        $errors[$breakField] = ts('Break should be empty');
      }
      if (!empty($day['leave_days'])) {
        $errors[$leaveDaysField] = ts('Leave Days should be empty');
      }
    }

    /**
     * A helper method to easily get the names of the Calendar fields.
     *
     * The calendar fields are stored in a nested array structure, and their
     * names reflect this structure.
     *
     * @param $weekIndex - The index of the Week this field belongs to
     * @param $dayIndex - The index of the Day this field belongs to
     * @param $field - The field name
     *
     * @return string
     */
    private function getWorkDayFieldName($weekIndex, $dayIndex, $field)
    {
      return "weeks[{$weekIndex}][days][{$dayIndex}][$field]";
    }

    /**
     * Checks if the times entered on the form are valid.
     *
     * The times are expected to follow the HH:MM format. This method also,
     * checks for invalid hours like 32:99
     *
     * @param string $time The value to be validated
     *
     * @return boolean
     */
    private function isValidHour($time)
    {
      return preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $time);
    }

    /**
     * A helper method to retrieve the buttons available
     * on the form.
     *
     * The Delete button is only available while editing an
     * existing Work Pattern
     *
     * @return array
     */
    private function getAvailableButtons()
    {
        $buttons = [
            [ 'type' => 'next', 'name' => ts('Save'), 'isDefault' => true ],
            [ 'type' => 'cancel', 'name' => ts('Cancel') ],
        ];

        if($this->_action & CRM_Core_Action::UPDATE) {
            $buttons[] = [ 'type' => 'delete', 'name' => ts('Delete') ];
        }

        return $buttons;
    }

    /**
     * Returns the URL to where the user will be redirected after they click
     * on the Delete button.
     *
     * @return string
     */
    private function getDeleteUrl()
    {
        return CRM_Utils_System::url(
            'civicrm/admin/leaveandabsences/work_patterns',
            "action=delete&id={$this->_id}&reset=1",
            false,
            null,
            false
        );
    }

    /**
     * This is a helper method to check which weeks are visible on the Calendar
     * tab.
     *
     * If the form has been submitted, we look at the values of the
     * weeks[index][is_visible] fields. Otherwise, we just go with the
     * defaultValues loaded from the database;
     *
     * @return array An indexed array in the format [weekIndex => true|false]
     */
    private function getWeeksVisibility()
    {
      $visibility = [];
      //we need to call this to make sure the defaultValues are loaded
      $this->setDefaultValues();

      for($i = 0; $i < self::MAX_NUMBER_OF_WEEKS; $i++) {
        if($this->isSubmitted()) {
          $visibility[$i] = (bool)$this->getSubmitValue("weeks[$i][is_visible]");
        } else {
          $visibility[$i] = empty($this->defaultValues['weeks'][$i]['is_visible']) ? false : true;
        }
      }

      return $visibility;
    }

    /**
     * Get the number of Weeks visible on the Calendar tab.
     *
     * @return int
     */
    private function getNumberOfVisibleWeeks()
    {
      return count(array_filter($this->getWeeksVisibility()));
    }

    /**
     * Returns the weeks from a params array of submitted form values.
     *
     * The submitted form values will include even weeks that are not visible
     * on the form, but this method returns only the weeks that were visible.
     *
     * @param array $params An array of the submitted form values
     *
     * @return array An array containing only the visible weeks among the submitted values.
     */
    private function getWeeksFromSubmittedParams($params)
    {
      $weeks = [];
      if(!isset($params['weeks']) || !is_array($params['weeks'])) {
        return $weeks;
      }

      foreach($params['weeks'] as $week) {
        if(!empty($week['is_visible'])) {
          $weeks[] = $week;
        }
      }

      return $weeks;
    }

    /**
     * Sets the is_visible property for the weeks in the defaultValues array.
     *
     * A week is visible if it has days in it.
     */
    private function setIsVisibleForWeeksInDefaultValues()
    {
      // We need to add the is_visible information here so it can be used
      // to fill the weeks[index][is_visible] fields
      foreach($this->defaultValues['weeks'] as $i => $week) {
        $this->defaultValues['weeks'][$i]['is_visible'] = !empty($week['days']);
      }
    }

    /**
     * Returns the total number of hours for each week.
     *
     * If the form has been submitted, it calculate the number of hours based
     * on the submitted values. Otherwise, the it will be calculated from
     * defaultValues.
     *
     * @return array An array in the format [weekIndex => numberOfHours]
     */
    private function getWeeksNumberOfHours()
    {
      if($this->isSubmitted()) {
        return $this->calculateWeekHoursFromSubmittedValues();
      } else {
        return $this->calculateWeekHoursFromDefaultValues();
      }
    }

    /**
     * Calculate the total number of hours for each week, based on the submitted
     * values.
     *
     * @return array An array in the format [weekIndex => numberOfHours]
     */
    private function calculateWeekHoursFromSubmittedValues()
    {
      $numberOfHours = [];
      for($i = 0; $i < self::MAX_NUMBER_OF_WEEKS; $i++) {
        $weekNumberOfHours = 0;
        for($j = 0; $j < 7; $j++) {
          $fieldHours = $this->getSubmitValue("weeks[$i][days][$j][number_of_hours]");
          if($fieldHours) {
            $weekNumberOfHours += (float)$fieldHours;
          }
        }

        $numberOfHours[$i] = $weekNumberOfHours;
      }

      return $numberOfHours;
    }

    /**
     * Calculate the total number of hours for each week, based on the loaded
     * defaultValues.
     *
     * @return array An array in the format [weekIndex => numberOfHours]
     */
    private function calculateWeekHoursFromDefaultValues()
    {
      $numberOfHours = [];
      //we need to call this to make sure the defaultValues are loaded
      $this->setDefaultValues();

      for($i = 0; $i < self::MAX_NUMBER_OF_WEEKS; $i++) {
        $weekNumberOfHours = 0;
        if(isset($this->defaultValues['weeks'][$i]['days'])) {
          $days = $this->defaultValues['weeks'][$i]['days'];
          $weekNumberOfHours = array_sum(array_column($days, 'number_of_hours'));
        }
        $numberOfHours[$i] = $weekNumberOfHours;
      }

      return $numberOfHours;
    }
}
