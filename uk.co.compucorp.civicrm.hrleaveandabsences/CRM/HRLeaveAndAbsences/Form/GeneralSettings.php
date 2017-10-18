<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_HRLeaveAndAbsences_Form_GeneralSettings extends CRM_Core_Form {

  /**
   * @var array
   *   The filter to pass to the setting.getfields API
   */
  private $settingFilter = ['group' => 'leave_and_absences_general_settings'];

  /**
   * @var array
   *   An array to store settings once it has been retrieved from the settings API
   */
  private $settings = [];

  private $submittedValues = [];

  public function buildQuickForm() {
    $settings = $this->getFormSettings();

    foreach ($settings as $name => $setting) {
      if ($name == 'relationship_types_allowed_to_approve_leave') {
        $this->$setting['html_type'](
          $name,
          [
            'options' => $this->getRelationshipTypes(),
            'multiple' => true,
            'label' => $setting['label'],
            'style' => $setting['html_attributes']['style'],
          ],
          true
        );
      }
    }

    $this->addButtons($this->getAvailableButtons());

    $this->assign('elementNames', $this->getRenderableElementNames());

    CRM_Core_Resources::singleton()->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/leaveandabsence.css');
    parent::buildQuickForm();
  }

  public function postProcess() {
    $this->submittedValues = $this->exportValues();
    $this->saveSettings();

    $url = CRM_Utils_System::url('civicrm/admin/leaveandabsences/general_settings');
    $session = CRM_Core_Session::singleton();
    $session->replaceUserContext($url);
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons". These
    // items don't have labels. We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = [];
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  public function getFormSettings() {
    if (empty($this->settings)) {
      $settings = civicrm_api3('Setting', 'getfields', ['filters' => $this->settingFilter]);
    }

    return $settings['values'];
  }

  /**
   * Save settings.
   */
  public function saveSettings() {
    $values = array_intersect_key($this->submittedValues, $this->getFormSettings());
    civicrm_api3('Setting', 'create', $values);
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   *
   * @return array
   */
  public function setDefaultValues() {
    $existing = civicrm_api3('Setting', 'get', ['return' => array_keys($this->getFormSettings())]);
    $defaults = [];
    $domainID = CRM_Core_Config::domainID();

    foreach ($existing['values'][$domainID] as $name => $value) {
      $defaults[$name] = $value;
    }
    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultEntity() {
    return 'Setting';
  }

  /**
   * Get RelationShip Types needed to populate the relationship_types_allowed_to_approve_leave
   * multi-select field
   *
   * @return array
   */
  private function getRelationshipTypes() {
    $relationshipTypes = civicrm_api3('RelationshipType', 'get');
    $result = [];
    foreach ($relationshipTypes['values'] as $relationshipType) {
      $result[$relationshipType['id']] = $relationshipType['name_a_b'];
    }

    return $result;
  }

  /**
   * Return buttons for this form
   *
   * @return array
   */
  private function getAvailableButtons() {
    $buttons = [
      [ 'type' => 'next', 'name' => ts('Save'), 'isDefault' => true],
      [ 'type' => 'cancel', 'name' => ts('Cancel')],

    ];
    return $buttons;
  }
}
