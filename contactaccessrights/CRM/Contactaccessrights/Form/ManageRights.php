<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Contactaccessrights_Form_ManageRights extends CRM_Core_Form {
  /**
   * @var int ID of the contact for whom rights are being managed.
   */
  private $contactId;

  /**
   * {@inheritdoc}
   */
  public function preProcess() {
    $this->contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE);

    parent::preProcess();
  }

  /**
   * {@inheritdoc}
   */
  public function buildQuickForm() {
    // Region
    $this->add('select', 'regions', 'Regions', $this->getRegionOptions(), FALSE, ['multiple' => TRUE]);

    // Location
    $this->add('select', 'locations', 'Locations', $this->getLocationOptions(), FALSE, ['multiple' => TRUE]);

    $this->addButtons([
      ['type' => 'submit', 'name' => ts('Save'), 'isDefault' => TRUE],
      ['type' => 'cancel', 'name' => ts('Cancel'), 'isDefault' => FALSE]
    ]);

    parent::buildQuickForm();
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   * @throws \CRM_Extension_Exception
   */
  public function setDefaultValues() {
    return $this->getExistingValues();
  }

  /**
   * {@inheritdoc}
   */
  public function postProcess() {
    try {
      $values = $this->exportValues();

      $this->updateRightsByType(
        CRM_Contactaccessrights_Utils_RightType_Region::TYPE,
        $values['regions'],
        $this->getExistingRegions()
      );

      $this->updateRightsByType(
        CRM_Contactaccessrights_Utils_RightType_Location::TYPE,
        $values['locations'],
        $this->getExistingLocations()
      );

      CRM_Core_Session::setStatus(ts('Saved'), '', 'success');
    } catch (Exception $e) {
      throw new CRM_Extension_Exception('An error has occurred: ' . $e->getMessage(), $e->getCode());
    }

    parent::postProcess();
  }

  /////////////////////
  // Private Methods //
  /////////////////////

  /**
   * @return array
   * @throws \CRM_Extension_Exception
   */
  private function getExistingValues() {
    $defaults = [];

    try {
      $defaults['locations'] = array_column($this->getExistingLocations(), 'entity_id');
      $defaults['regions'] = array_column($this->getExistingRegions(), 'entity_id');
    } catch (Exception $e) {
      throw new CRM_Extension_Exception('An error has occurred: ' . $e->getMessage(), $e->getCode());
    }

    return $defaults;
  }

  /**
   * Helper method for returning a list of locations accessible to the user in question.
   *
   * @return mixed
   * @throws \CRM_Extension_Exception
   */
  private function getExistingLocations() {
    try {
      $locations = civicrm_api3('Rights', 'getlocations', ['contact_id' => $this->contactId]);

      return $locations['values'];
    } catch (Exception $e) {
      throw new CRM_Extension_Exception('An error has occurred: ' . $e->getMessage(), $e->getCode());
    }
  }

  /**
   * Helper method for returning a list of regions accessible to the user in question.
   *
   * @return mixed
   * @throws \CRM_Extension_Exception
   */
  private function getExistingRegions() {
    try {
      $regions = civicrm_api3('Rights', 'getregions', ['contact_id' => $this->contactId]);

      return $regions['values'];
    } catch (Exception $e) {
      throw new CRM_Extension_Exception('An error has occurred: ' . $e->getMessage(), $e->getCode());
    }
  }

  private function updateRightsByType($type, array $newEntityIds, array $oldValues) {
    $newEntityIds = array_filter($newEntityIds);
    $oldEntityIds = array_column($oldValues, 'entity_id');

    $addedEntityIds = array_diff($newEntityIds, $oldEntityIds);
    $removedEntityIds = array_diff($oldEntityIds, $newEntityIds);

    foreach ($addedEntityIds as $value) {
      $data = [
        'contact_id'  => $this->contactId,
        'entity_type' => $type,
        'entity_id'   => $value,
        'options'     => [
          'match' => ['contact_id', 'entity_type', 'entity_id']
        ]
      ];

      $result = civicrm_api3('Rights', 'create', $data);
    }

    foreach ($removedEntityIds as $entityId) {
      if (FALSE !== ($rightId = array_search($entityId, array_column($oldValues, 'entity_id', 'id')))) {
        $result = civicrm_api3('Rights', 'delete', ['id' => $rightId]);
      }
    }
  }

  /**
   * @return array
   * @throws \CRM_Extension_Exception
   */
  private function getRegionOptions() {
    return $this->getOptions(CRM_Contactaccessrights_Utils_RightType_Region::TYPE);
  }

  /**
   * @return array
   * @throws \CRM_Extension_Exception
   */
  private function getLocationOptions() {
    return $this->getOptions(CRM_Contactaccessrights_Utils_RightType_Location::TYPE);
  }

  /**
   * @param $optionGroupName
   *
   * @return array
   * @throws \CRM_Extension_Exception
   */
  private function getOptions($optionGroupName) {
    try {
      $options = ['' => ts('- select -')];

      $result = civicrm_api3('OptionValue', 'get', ['sequential' => 1, 'option_group_name' => $optionGroupName]);

      foreach ($result['values'] as $option) {
        $options[$option['id']] = $option['label'];
      }

      return $options;
    } catch (Exception $e) {
      throw new CRM_Extension_Exception('An error has occurred: ' . $e->getMessage(), $e->getCode());
    }
  }
}
