<?php

use CRM_HRCore_Service_DrupalUserService as DrupalUserService;
use CRM_Utils_Array as ArrayHelper;

abstract class CRM_HRCore_Form_AbstractDrupalInteractionTaskForm extends CRM_Contact_Form_Task {
  /**
   * @var array
   */
  protected $contactDetails = [];

  /**
   * @var DrupalUserService
   */
  protected $drupalUserService;

  /**
   * @param null $state
   * @param mixed $action
   * @param string $method
   * @param null $name
   */
  public function __construct(
    $state = NULL,
    $action = CRM_Core_Action::NONE,
    $method = 'post',
    $name = NULL
  ) {
    $this->drupalUserService = Civi::container()->get('drupal_user_service');
    parent::__construct($state, $action, $method, $name);
  }

  public function preProcess() {
    parent::preProcess();
    $this->initContactDetails();
  }

  /**
   * Creates an array to store contact details
   */
  protected function initContactDetails() {
    $emailParams = [
      'contact_id' => '$value.id',
      'return' => ['email'],
      'is_primary' => 1
    ];
    $ufMatchParams = [
      'contact_id' => '$value.id',
      'return' => ['uf_id']
    ];
    $params = [
      'return' => ['display_name'],
      'id' => ['IN' => $this->_contactIds],
      'api.Email.getsingle' => $emailParams,
      'api.UFMatch.getsingle' => $ufMatchParams
    ];
    $contactDetails = civicrm_api3('Contact', 'get', $params);
    $contactDetails = ArrayHelper::value('values', $contactDetails);

    foreach ($contactDetails as $detail) {
      $contactID = (int) ArrayHelper::value('contact_id', $detail);
      $displayName = ArrayHelper::value('display_name', $detail);
      $ufMatch = ArrayHelper::value('api.UFMatch.getsingle', $detail, []);
      $ufId = (int) ArrayHelper::value('uf_id', $ufMatch);
      $emailDetails = ArrayHelper::value('api.Email.getsingle', $detail, []);
      $email = ArrayHelper::value('email', $emailDetails);

      $this->setContactDetail($contactID, 'display_name', $displayName);
      $this->setContactDetail($contactID, 'uf_id', $ufId);
      $this->setContactDetail($contactID, 'email', $email);
      $this->setContactDetail($contactID, 'id', $contactID);
    }
  }

  /**
   * Helper method to prevent overwriting of contact details
   *
   * @param int $contactId
   * @param string $type
   * @param mixed $value
   */
  private function setContactDetail($contactId, $type, $value) {
    if (!isset($this->contactDetails[$contactId][$type])) {
      $this->contactDetails[$contactId][$type] = $value;
    }
  }

  /**
   * @param $property
   *  The property to check if empty
   *
   * @return array
   */
  protected function getContactsWithoutAttribute($property) {
    $attributesFilter = function ($contactDetail) use ($property) {
      return empty($contactDetail[$property]);
    };

    return array_filter($this->contactDetails, $attributesFilter);
  }

}
