<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_HRLeaveAndAbsences_Form_AbsenceType extends CRM_Core_Form
{

    public function setDefaultValues() {
        if ($this->_id) {
            $defaults = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getDefaultValues($this->_id);
        } else {
            $defaults = [
                'allow_request_cancelation' => CRM_HRLeaveAndAbsences_BAO_AbsenceType::REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE,
                'add_public_holiday_to_entitlement' => 0,
                'must_take_public_holiday_as_leave' => 0
            ];
        }

        return $defaults;
    }

    public function buildQuickForm()
    {
        $this->addBasicDetailsFields();
        $this->addRequestingLeaveFields();
        $this->addTOILFields();
        $this->addCarryForwardFields();

        $this->addButtons([
            [ 'type' => 'next', 'name' => ts('Save'), 'isDefault' => true ],
            [ 'type' => 'cancel', 'name' => ts('Cancel') ],
        ]);

        $this->assign('elementNames', $this->getRenderableElementNames());
        $this->assign('availableColors', json_encode(CRM_HRLeaveAndAbsences_BAO_AbsenceType::getAvailableColors()));

        $this->_id = CRM_Utils_Request::retrieve('id' , 'Positive', $this);
        CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/hrleaveandabsences.css');
        CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/spectrum.css');
        CRM_Core_Resources::singleton()->addScriptFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'js/spectrum-min.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
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

            try {
                $absenceType = CRM_HRLeaveAndAbsences_BAO_AbsenceType::create($params);
            } catch(CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException $ex) {
                $this->setElementError('title', $ex->getMessage());
            }

            if ($this->_action & CRM_Core_Action::UPDATE) {
                CRM_Core_Session::setStatus(ts('The Leave/Absence type \'%1\' has been updated.', array( 1 => $absenceType->title)), 'Success', 'success');
            }
            else {
                CRM_Core_Session::setStatus(ts('The Leave/Absence type \'%1\' has been added.', array( 1 => $absenceType->title)), 'Success', 'success');
            }

            $url = CRM_Utils_System::url('civicrm/admin/leaveandabsences/types', 'reset=1&action=browse');
            $session = CRM_Core_Session::singleton();
            $session->replaceUserContext($url);
        }
    }

    /**
     * Get the fields/elements defined in this form.
     *
     * @return array (string)
     */
    public function getRenderableElementNames()
    {
// The _elements list includes some items which should not be
// auto-rendered in the loop -- such as "qfKey" and "buttons".  These
// items don't have labels.  We'll identify renderable by filtering on
// the 'label'.
        $elementNames = array();
        foreach ($this->_elements as $element) {
            /** @var HTML_QuickForm_Element $element */
            $label = $element->getLabel();
            if (!empty($label)) {
                $elementNames[] = $element->getName();
            }
        }

        return $elementNames;
    }

    private function getMonthsOptions()
    {
        return [
            1 => ts('Jan'),
            2 => ts('Feb'),
            3 => ts('Mar'),
            4 => ts('Apr'),
            5 => ts('May'),
            6 => ts('Jun'),
            7 => ts('Jul'),
            8 => ts('Ago'),
            9 => ts('Sep'),
            10 => ts('Oct'),
            11 => ts('Nov'),
            12 => ts('Dec'),
        ];
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
        $this->add(
            'text',
            'carry_forward_expiration_day',
            '',
            $this->getDAOFieldAttributes('carry_forward_expiration_day')
        );
        $this->addSelect(
            'carry_forward_expiration_month',
            ['options' => $this->getMonthsOptions()]
        );
    }

    private function getDAOFieldAttributes($field)
    {
        $dao = 'CRM_HRLeaveAndAbsences_DAO_AbsenceType';
        return CRM_Core_DAO::getAttribute($dao, $field);
    }
}
