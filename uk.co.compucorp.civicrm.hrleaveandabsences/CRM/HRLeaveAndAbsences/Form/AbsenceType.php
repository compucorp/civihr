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
        $defaults = array();

        if ($this->_id) {
            $defaults = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getDefaultValues($this->_id);
        }

        return $defaults;
    }

    public function buildQuickForm()
    {

        $this->addButtons([
            [ 'type' => 'next', 'name' => ts('Save'), 'isDefault' => true ],
            [ 'type' => 'cancel', 'name' => ts('Cancel') ],
        ]);

        $this->add(
            'text',
            'title',
            ts('Title'),
            CRM_Core_DAO::getAttribute('CRM_HRLeaveAndAbsences_DAO_AbsenceType', 'title'),
            true
        );
        $this->add(
            'text',
            'color',
            ts('Calendar Colour'),
            CRM_Core_DAO::getAttribute('CRM_HRLeaveAndAbsences_DAO_AbsenceType', 'color'),
            true
        );
        $this->add(
            'text',
            'default_entitlement',
            ts('Default entitlement'),
            CRM_Core_DAO::getAttribute('CRM_HRLeaveAndAbsences_DAO_AbsenceType', 'default_entitlement'),
            true
        );
        $this->add(
            'text',
            'default_entitlement',
            ts('Default entitlement'),
            CRM_Core_DAO::getAttribute('CRM_HRLeaveAndAbsences_DAO_AbsenceType', 'default_entitlement'),
            true
        );
        $this->addSelect(
            'allow_request_cancelation',
            [
                'options' => CRM_HRLeaveAndAbsences_BAO_AbsenceType::getRequestCancelationOptions(),
            ],
            true
        );

        $this->assign('elementNames', $this->getRenderableElementNames());

        $this->_id = CRM_Utils_Request::retrieve('id' , 'Positive', $this);

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
}
