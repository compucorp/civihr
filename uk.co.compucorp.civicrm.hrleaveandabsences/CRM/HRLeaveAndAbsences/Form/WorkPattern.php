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
                    'is_active' => 1
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

        $this->addButtons($this->getAvailableButtons());

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

    private function getDAOFieldAttributes($field)
    {
        $dao = 'CRM_HRLeaveAndAbsences_DAO_WorkPattern';
        return CRM_Core_DAO::getAttribute($dao, $field);
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
