<?php

use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as ActionsGroupButtonItem;

/**
 * Class CRM_HRContactActionsMenu_Helper_CommunicationActionGroup
 */
class CRM_HRContactActionsMenu_Helper_CommunicationActionGroup {

  /**
   * @var array
   */
  private $communicationActivityTypes;

  /**
   * @var int
   */
  private $contactID;

  /**
   * CRM_HRContactActionsMenu_Helper_CommunicationActionGroup constructor
   *
   * @param int $contactID
   */
  public function __construct($contactID) {
    $this->contactID = $contactID;
  }

  /**
   * Gets Communicate Menu Group with menu items already
   * added.
   *
   * @return ActionsGroup
   */
  public function get() {
    $actionsGroup = new ActionsGroup('Communications:');
    $actionsGroup->addItem($this->getSendEmailButton());
    $actionsGroup->addItem($this->getRecordMeetingButton());
    $actionsGroup->addItem($this->getCreatePdfLetterButton());

    return $actionsGroup;
  }

  /**
   * Gets the Send Email Button Item.
   *
   * @return ActionsGroupButtonItem
   */
  private function getSendEmailButton() {
    $activityValue = $this->getActivityTypeValue('Email');
    $formUrl = $this->getAddActivityUrl(['atype' => $activityValue], 'email');
    $attribute = ['onclick' => "CRM.loadForm('{$formUrl}')"];
    $params = [
      'label' => 'Send Email',
      'class' => 'btn-primary-outline',
      'icon' => 'fa-envelope-o',
      'url' => '#'
    ];

    return $this->getMenuButton($params, $attribute);
  }

  /**
   * Gets the Record Meeting Button Item.
   *
   * @return ActionsGroupButtonItem
   */
  private function getRecordMeetingButton(){
    $activityValue = $this->getActivityTypeValue('Meeting');
    $formUrl = $this->getAddActivityUrl(['atype' => $activityValue]);
    $attribute = ['onclick' => "CRM.loadForm('{$formUrl}')"];
    $params = [
      'label' => 'Record Meeting',
      'class' => 'btn-primary-outline',
      'icon' => 'fa-users',
      'url' => '#'
    ];

    return $this->getMenuButton($params, $attribute);
  }

  /**
   * Gets the Create PDF Letter Button Item.
   *
   * @return ActionsGroupButtonItem
   */
  private function getCreatePdfLetterButton() {
    $activityValue = $this->getActivityTypeValue('Print PDF Letter');
    $formUrl = $this->getAddActivityUrl(['atype' => $activityValue], 'pdf');
    $attribute = ['onclick' => "CRM.loadForm('{$formUrl}')"];
    $params = [
      'label' => 'Create PDF Letter',
      'class' => 'btn-primary-outline',
      'icon' => 'fa-file-pdf-o',
      'url' => '#'
    ];

    return $this->getMenuButton($params, $attribute);
  }

  /**
   * Returns an instance of an ActionsGroupButtonItem
   *
   * @param array $params
   * @param array $attributes
   *
   * @return ActionsGroupButtonItem
   */
  private function getMenuButton($params, array $attributes = []) {
    $button = new ActionsGroupButtonItem($params['label']);
    $button->setClass($params['class'])
      ->setIcon($params['icon'])
      ->setUrl($params['url']);

    foreach($attributes as $attribute => $value) {
      $button->setAttribute($attribute, $value);
    }

    return $button;
  }

  /**
   * Gets the Add Activity URL depending on the activity type
   * and query parameters.
   *
   * @param array $queryParameters
   * @param string $type
   *
   * @return string
   */
  private function getAddActivityUrl($queryParameters = [], $type = '') {
    $defaultParameters = ['action' => 'add', 'cid' => $this->contactID];
    $queryParameters = array_merge($defaultParameters, $queryParameters);
    $url = 'civicrm/activity/add';

    if ($type) {
      $url = "civicrm/activity/{$type}/add";
    }

    return CRM_Utils_System::url(
      $url,
      http_build_query($queryParameters)
    );
  }

  /**
   * Returns the communication related activity types.
   *
   * @return array
   *   The activity option values indexed by their names
   */
  private function getCommunicationActivityTypes() {
    if(!$this->communicationActivityTypes) {
      $result = civicrm_api3('OptionValue', 'get', [
        'option_group_id' => 'activity_type',
        'name' => ['IN' => ['Print PDF Letter', 'Meeting', 'Email']],
      ]);

      $this->communicationActivityTypes = array_column($result['values'], 'value', 'name');
    }

    return $this->communicationActivityTypes;
  }

  /**
   * Returns the Activity type option value
   *
   * @param string $activityName
   *
   * @return string
   */
  private function getActivityTypeValue($activityName) {
    $activityTypes = $this->getCommunicationActivityTypes();

    return isset($activityTypes[$activityName]) ? $activityTypes[$activityName] : '';
  }
}
