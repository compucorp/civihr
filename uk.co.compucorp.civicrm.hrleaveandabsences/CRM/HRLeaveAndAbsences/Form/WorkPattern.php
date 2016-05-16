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
    *  The maximum number of weeks this form can handle
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
        if(empty($this->defaultValues)) {
            if ($this->_id) {
                $this->defaultValues = CRM_HRLeaveAndAbsences_BAO_WorkPattern::getValuesArray($this->_id);
            } else {
                $this->defaultValues = [
                    'is_active' => 1,
                    'weeks' => []
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

        $this->assign('max_number_of_weeks', self::MAX_NUMBER_OF_WEEKS);

        CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/hrleaveandabsences.css');

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

    private function addCalendarFields()
    {
      $leaveDaysAmounts = CRM_Core_BAO_OptionValue::getOptionValuesAssocArrayFromName('hrleaveandabsences_leave_days_amounts');
      $daysPerWeek = 7;
      for($i = 0; $i < self::MAX_NUMBER_OF_WEEKS; $i++) {
        $this->add('hidden', "weeks[$i][days][number]");
        for($j = 0; $j < $daysPerWeek; $j++) {
          $this->add(
            'select',
            "weeks[$i][days][$j][type]",
            false,
            CRM_HRLeaveAndAbsences_BAO_WorkDay::getWorkTypeOptions()
          );
          $this->add('text', "weeks[$i][days][$j][time_from]");
          $this->add('text', "weeks[$i][days][$j][time_to]");
          $this->add('text', "weeks[$i][days][$j][break]");
          $this->add('text', "weeks[$i][days][$j][number_of_hours]");
          $this->add('select', "weeks[$i][days][$j][leave_days]", false, $leaveDaysAmounts);
        }
      }
    }

    private function getDAOFieldAttributes($field)
    {
        $dao = 'CRM_HRLeaveAndAbsences_DAO_WorkPattern';
        return CRM_Core_DAO::getAttribute($dao, $field);
    }

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
      if($day['type'] == CRM_HRLeaveAndAbsences_BAO_WorkDay::WORK_DAY_OPTION_YES) {
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
      $hasTimeFrom = strlen(trim($day['time_from'])) > 0;
      $hasTimeTo = strlen(trim($day['time_to'])) > 0;
      $hasBreak = strlen(trim($day['break'])) > 0;
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

    private function getWorkDayFieldName($weekIndex, $dayIndex, $field)
    {
      return "weeks[{$weekIndex}][days][{$dayIndex}][$field]";
    }

    private function isValidHour($time)
    {
      return preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $time);
    }

    /**
     * An helper method to retrieve the buttons available
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
}
