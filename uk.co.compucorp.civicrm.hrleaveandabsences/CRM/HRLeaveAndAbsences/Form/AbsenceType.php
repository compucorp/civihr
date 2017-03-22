<?php

use CRM_HRLeaveAndAbsences_Service_AbsenceType as AbsenceTypeService;

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_HRLeaveAndAbsences_Form_AbsenceType extends CRM_Core_Form
{

    private $defaultValues = [];

    public function setDefaultValues() {
        if(empty($this->defaultValues)) {
            if ($this->_id) {
                $this->defaultValues = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getValuesArray($this->_id);
            } else {
                $this->defaultValues = [
                    'allow_request_cancelation' => CRM_HRLeaveAndAbsences_BAO_AbsenceType::REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE,
                    'add_public_holiday_to_entitlement' => 0,
                    'must_take_public_holiday_as_leave' => 0
                ];
            }
        }

        return $this->defaultValues;
    }

    public function buildQuickForm()
    {
        $this->_id = CRM_Utils_Request::retrieve('id' , 'Positive', $this);

        $this->addBasicDetailsFields();
        $this->addRequestingLeaveFields();
        $this->addTOILFields();
        $this->addCarryForwardFields();
        $this->addFieldsRules();

        $this->addButtons($this->getAvailableButtons());
        $this->assign('canDeleteType', $this->canDelete());
        $this->assign('deleteUrl', $this->getDeleteUrl());
        $this->assign('availableColors', json_encode(CRM_HRLeaveAndAbsences_BAO_AbsenceType::getAvailableColors()));

        CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/hrleaveandabsences.css');
        CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/spectrum.css');
        CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/crm/vendor/spectrum-min.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
        parent::buildQuickForm();
    }

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
            $checkboxFields = ['is_default', 'is_active', 'allow_accruals_request', 'allow_carry_forward'];
            foreach ($checkboxFields as $field) {
                if(!array_key_exists($field, $params)) {
                    $params[$field] = 0;
                }
            }

            if(!empty($params['notification_receivers_ids'])) {
                $params['notification_receivers_ids'] = explode(',', $params['notification_receivers_ids']);
            }

            $actionDescription = ($this->_action & CRM_Core_Action::UPDATE) ? 'updated' : 'created';
            try {
                $absenceType = CRM_HRLeaveAndAbsences_BAO_AbsenceType::create($params);
                CRM_Core_Session::setStatus(ts("The Leave/Absence type '%1' has been $actionDescription.", array( 1 => $absenceType->title)), 'Success', 'success');
            } catch(CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException $ex) {
                $message = ts("The Leave/Absence could not be $actionDescription.");
                $message .= ' ' . $ex->getMessage();
                CRM_Core_Session::setStatus($message, 'Error', 'error');
            }

            $url = CRM_Utils_System::url('civicrm/admin/leaveandabsences/types', 'reset=1&action=browse');
            $session = CRM_Core_Session::singleton();
            $session->replaceUserContext($url);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultEntity()
    {
      return 'AbsenceType';
    }

    private function addBasicDetailsFields()
    {
        $this->add(
            'text',
            'title',
            ts('Title'),
            $this->getDAOFieldAttributes('title'),
            true
        );
        $this->add(
            'text',
            'color',
            ts('Calendar Colour'),
            $this->getDAOFieldAttributes('color'),
            true
        );
        $this->add(
            'checkbox',
            'is_default',
            ts('Is default leave type')
        );
        if($this->_action & CRM_Core_Action::UPDATE) {
            $this->add(
                'checkbox',
                'is_reserved',
                ts('Is reserved'),
                false,
                false,
                ['disabled' => 'disabled']
            );
        }
        $this->addYesNo(
            'must_take_public_holiday_as_leave',
            ts('Must staff take public holiday as leave?')
        );
        $this->add(
            'text',
            'default_entitlement',
            ts('Default entitlement'),
            $this->getDAOFieldAttributes('default_entitlement'),
            true
        );
        $this->addEntityRef(
            'notification_receivers_ids',
            ts('When an employee does not have a leave approver, who should be notified of leave requests'),
            ['multiple' => TRUE, 'create' => TRUE]
        );
        $this->addYesNo(
            'add_public_holiday_to_entitlement',
            ts('By default should public holiday be added to the default entitlement? You can always modify this for each staff member on the add/edit job contract screen')
        );
        $this->add(
            'checkbox',
            'is_active',
            ts('Enabled')
        );
    }

    private function addRequestingLeaveFields()
    {
        $this->add(
            'text',
            'max_consecutive_leave_days',
            ts('Duration of consecutive leave permitted to be taken at once? (Leave blank for unlimited)'),
            $this->getDAOFieldAttributes('max_consecutive_leave_days')
        );
        $this->addSelect(
            'allow_request_cancelation',
            [ 'options' => CRM_HRLeaveAndAbsences_BAO_AbsenceType::getRequestCancelationOptions() ],
            true
        );
        $this->addYesNo(
            'allow_overuse',
            ts('Can employees apply for this leave type even if they have used up their entitlement for the year? The system will keep a note of the overuse of holiday and this can be used to calculate any pay reduction')
        );
    }

    private function addTOILFields()
    {
        $this->add(
            'checkbox',
            'allow_accruals_request',
            ts('Allow staff to request to accrue additional days leave of this type during the period')
        );
        $this->add(
            'text',
            'max_leave_accrual',
            ts('Maximum amount of leave that can be accrued of this absence type during a period (Leave blank for unlimited)'),
            $this->getDAOFieldAttributes('max_leave_accrual')
        );
        $this->addYesNo(
            'allow_accrue_in_the_past',
            ts('Can staff request to accrue leave for dates in the past? (Note that admin and managers can always accrue leave on behalf of employees)')
        );
        $this->add(
            'text',
            'accrual_expiration_duration',
            '',
            $this->getDAOFieldAttributes('accrual_expiration_duration')
        );
        $this->addSelect(
            'accrual_expiration_unit',
            ['options' => CRM_HRLeaveAndAbsences_BAO_AbsenceType::getExpirationUnitOptions()]
        );
    }

    private function addCarryForwardFields()
    {
        $this->add(
            'checkbox',
            'allow_carry_forward',
            ts('Allow leave of this type to be carried forward from one period to another?')
        );
        $this->add(
            'text',
            'max_number_of_days_to_carry_forward',
            ts('Maximum number of days that can be carried forward to a new period? (Leave blank for unlimited)'),
            $this->getDAOFieldAttributes('max_number_of_days_to_carry_forward')
        );
        $this->add(
            'text',
            'carry_forward_expiration_duration',
            '',
            $this->getDAOFieldAttributes('carry_forward_expiration_duration')
        );
        $this->addSelect(
            'carry_forward_expiration_unit',
            ['options' => CRM_HRLeaveAndAbsences_BAO_AbsenceType::getExpirationUnitOptions()]
        );
    }

    private function getDAOFieldAttributes($field)
    {
        $dao = 'CRM_HRLeaveAndAbsences_DAO_AbsenceType';
        return CRM_Core_DAO::getAttribute($dao, $field);
    }

    private function addFieldsRules()
    {
        $positiveNumberMessage = ts('The value should be a positive number');
        $this->addRule('default_entitlement', $positiveNumberMessage, 'positiveInteger');
        $this->addRule('max_consecutive_leave_days', $positiveNumberMessage, 'positiveInteger');
        $this->addRule('max_leave_accrual', $positiveNumberMessage, 'positiveInteger');
        $this->addRule('accrual_expiration_duration', $positiveNumberMessage, 'positiveInteger');
        $this->addRule('max_number_of_days_to_carry_forward', $positiveNumberMessage, 'positiveInteger');
        $this->addRule('carry_forward_expiration_duration', $positiveNumberMessage, 'positiveInteger');
        $this->addFormRule([$this, 'formRules']);
    }

    public function formRules($values)
    {
        $errors = [];
        $this->validateToilExpiration($values, $errors);
        $this->validateCarryForwardExpiration($values, $errors);

        return empty($errors) ? true : $errors;
    }

    private function validateToilExpiration($values, &$errors)
    {
        $expiration_unit = CRM_Utils_Array::value('accrual_expiration_unit', $values);
        $expiration_duration = CRM_Utils_Array::value('accrual_expiration_duration', $values);

        if($expiration_unit && !$expiration_duration) {
            $errors['accrual_expiration_duration'] = ts('You must also set the expiration duration');
        }

        if($expiration_duration && !$expiration_unit) {
            $errors['accrual_expiration_unit'] = ts('You must also set the expiration unit');
        }
    }

    private function validateCarryForwardExpiration($values, &$errors)
    {
        $expiration_unit = CRM_Utils_Array::value('carry_forward_expiration_unit', $values);
        $expiration_duration = CRM_Utils_Array::value('carry_forward_expiration_duration', $values);

        if($expiration_unit && !$expiration_duration) {
            $errors['carry_forward_expiration_duration'] = ts('You must also set the expiration duration');
        }

        if($expiration_duration && !$expiration_unit) {
            $errors['carry_forward_expiration_unit'] = ts('You must also set the expiration unit');
        }
    }

    private function getAvailableButtons()
    {
        $buttons = [
            [ 'type' => 'next', 'name' => ts('Save'), 'isDefault' => true ],
            [ 'type' => 'cancel', 'name' => ts('Cancel') ],
        ];

        $defaultValues = $this->setDefaultValues();
        $is_reserved = empty($defaultValues['is_reserved']) ? false : true;
        if(($this->_action & CRM_Core_Action::UPDATE) && !$is_reserved) {
            $buttons[] = [ 'type' => 'delete', 'name' => ts('Delete') ];
        }

        return $buttons;
    }

    private function getDeleteUrl()
    {
        return CRM_Utils_System::url(
            'civicrm/admin/leaveandabsences/types',
            "action=delete&id={$this->_id}&reset=1",
            false,
            null,
            false
        );
    }

    /**
     * Checks whether an AbsenceType object can be deleted.
     *
     * @return bool
     */
    private function canDelete() {
      $absenceTypeService = new AbsenceTypeService();
      return !$absenceTypeService->absenceTypeHasEverBeenUsed($this->_id);
    }
}
