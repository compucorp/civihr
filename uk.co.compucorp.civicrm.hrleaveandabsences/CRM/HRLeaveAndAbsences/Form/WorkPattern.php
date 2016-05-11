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

    public function formRules($values)
    {
      $errors = [];
      $this->validateWorkDays($values, $errors);

      return empty($errors) ? true : $errors;
    }

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

    private function validateWorkDay($weekIndex, $dayIndex, $day, &$errors)
    {
      $isWorkingDay = $day['type'] == CRM_HRLeaveAndAbsences_BAO_WorkDay::WORK_DAY_OPTION_YES;
      $hasTimeFrom = strlen(trim($day['time_from'])) > 0;
      $hasTimeTo = strlen(trim($day['time_to'])) > 0;
      $hasBreak = strlen(trim($day['break'])) > 0;
      if($isWorkingDay) {
        if(!$hasTimeFrom) {
          $field = "weeks[{$weekIndex}][days][{$dayIndex}][time_from]";
          $errors[$field] = ts('Please inform the Time From');
        }
        if(!$hasTimeTo) {
          $field = "weeks[{$weekIndex}][days][{$dayIndex}][time_to]";
          $errors[$field] = ts('Please inform the Time To');
        }
        //break can be 0, so we can't check it with empty
        if(!$hasBreak) {
          $field = "weeks[{$weekIndex}][days][{$dayIndex}][break]";
          $errors[$field] = ts('Please inform the Break');
        }
      } else {
        if($hasTimeFrom) {
          $field = "weeks[{$weekIndex}][days][{$dayIndex}][time_from]";
          $errors[$field] = ts('Time From should be empty');
        }
        if($hasTimeTo) {
          $field = "weeks[{$weekIndex}][days][{$dayIndex}][time_to]";
          $errors[$field] = ts('Time To should be empty');
        }
        if($hasBreak) {
          $field = "weeks[{$weekIndex}][days][{$dayIndex}][break]";
          $errors[$field] = ts('Break should be empty');
        }
        if(!empty($day['leave_days']) ) {
          $field = "weeks[{$weekIndex}][days][{$dayIndex}][leave_days]";
          $errors[$field] = ts('Leave Days should be empty');
        }
      }
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
