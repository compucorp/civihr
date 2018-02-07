<?php

use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as ActionsGroupButtonItem;
use CRM_HRContactActionsMenu_Component_GroupSeparatorItem as GroupSeparatorItem;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;
use CRM_HRLeaveAndAbsences_Component_ContactActionsMenu_NoSelectedLeaveApproverItem as NoSelectedLeaveApproverItem;
use CRM_HRLeaveAndAbsences_Component_ContactActionsMenu_LeaveApproversListItem as LeaveApproversListItem;

/**
 * Class CRM_HRLeaveAndAbsences_Helper_ContactActionsMenu_LeaveActionGroup
 */
class CRM_HRLeaveAndAbsences_Helper_ContactActionsMenu_LeaveActionGroup {

  use CRM_HRLeaveAndAbsences_Service_SettingsManagerTrait;

  /**
   * @var LeaveManagerService
   */
  private $leaveManagerService;

  /**
   * @var int
   */
  private $contactID;

  /**
   * CRM_HRLeaveAndAbsences_Helper_ContactActionsMenu_LeaveActionGroup constructor.
   *
   * @param \CRM_HRLeaveAndAbsences_Service_LeaveManager $leaveManagerService
   * @param int $contactID
   */
  public function __construct(LeaveManagerService $leaveManagerService, $contactID) {
    $this->leaveManagerService = $leaveManagerService;
    $this->contactID = $contactID;
  }

  /**
   * Gets Leave Menu Group with menu items already
   * added.
   *
   * @return ActionsGroup
   */
  public function get() {
    $actionsGroup = new ActionsGroup('Leave:');
    $actionsGroup->addItem($this->getRecordLeaveButton());
    $actionsGroup->addItem($this->getRecordSicknessButton());
    $actionsGroup->addItem($this->getRecordOvertimeButton());
    $actionsGroup->addItem($this->getViewEntitlementsButton());
    $actionsGroup->addItem(new GroupSeparatorItem());

    $leaveApprovers = $this->getLeaveApprovers();

    if ($leaveApprovers) {
      $leaveApproversListItem = new LeaveApproversListItem($this->leaveManagerService, $this->contactID);
      $actionsGroup->addItem($leaveApproversListItem);
      $actionsGroup->addItem($this->getManageLeaveApproverButton());
    }
    else {
      $noLeaveApprovertItem = new NoSelectedLeaveApproverItem();
      $actionsGroup->addItem($noLeaveApprovertItem);
      $actionsGroup->addItem($this->getAddLeaveApproverButton());
    }

    return $actionsGroup;
  }

  /**
   * Gets the View Entitlement button
   *
   * @return ActionsGroupButtonItem
   */
  private function getViewEntitlementsButton() {
    $params = [
      'label' => 'View Entitlements',
      'class' => 'btn-primary-outline',
      'icon' => 'fa-search',
      'url' => $this->getLeaveTabUrl()
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Gets the Add Leave Approver button.
   *
   * @return ActionsGroupButtonItem
   */
  private function getAddLeaveApproverButton() {
    $relTypeID = $this->getLeaveApproverRelationshipTypeSelectId();
    $attribute = ['onclick' => "CRM.loadForm('/civicrm/contact/view/rel?cid={$this->contactID}&action=add&relTypeId={$relTypeID}')"];
    $params = [
      'label' => 'Add Leave Approver',
      'class' => 'btn-secondary-outline',
      'icon' => '',
      'url' => '#'
    ];

    return $this->getMenuButton($params, $attribute);
  }

  /**
   * Gets the Manage Leave Approver button
   *
   * @return ActionsGroupButtonItem
   */
  private function getManageLeaveApproverButton() {
    $url = CRM_Utils_System::url(
      'civicrm/contact/view',
      "reset=1&cid={$this->contactID}&selectedChild=rel"
    );
    $params = [
      'label' => 'Manage Leave Approver',
      'class' => 'btn btn-secondary',
      'icon' => '',
      'url' => $url
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Gets the Record Sickness button.
   *
   * @return ActionsGroupButtonItem
   */
  private function getRecordSicknessButton() {
    return $this->getLeaveButton('Record Sickness', 'fa-stethoscope', 'sickness');
  }

  /**
   * Gets the Record Leave button.
   *
   * @return \CRM_HRContactActionsMenu_Component_GroupButtonItem
   */
  private function getRecordLeaveButton() {
    return $this->getLeaveButton('Record Leave', 'fa-briefcase', 'leave');
  }

  /**
   * Gets the Record Overtime button.
   *
   * @return ActionsGroupButtonItem
   */
  private function getRecordOvertimeButton() {
    return $this->getLeaveButton('Record Overtime', 'fa-calendar-plus-o', 'toil');
  }

  /**
   * Gets the Leave button based on parameters passed in.
   *
   * @param string $label
   * @param string $icon
   * @param string $modalType
   *
   * @return ActionsGroupButtonItem
   */
  private function getLeaveButton($label, $icon, $modalType) {
    $params = [
      'label' => $label,
      'class' => 'btn-primary-outline',
      'icon' => $icon,
      'url' => $this->getLeaveTabUrl(['openModal' => $modalType])
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Gets the URl for the Leave tab on the contact summary page.
   *
   * @param array $queryParameters
   *
   * @return mixed
   */
  private function getLeaveTabUrl($queryParameters = []) {
    $defaultParameters = ['reset' => 1, 'cid' => $this->contactID, 'selectedChild' => 'absence'];
    $queryParameters = array_merge($defaultParameters, $queryParameters);

    $url = CRM_Utils_System::url(
      'civicrm/contact/view',
      http_build_query($queryParameters)
    );

    return $url;
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
   * Gets Leave Approvers for the contact.
   *
   * @return array
   */
  private function getLeaveApprovers() {
    return $this->leaveManagerService->getLeaveApproversForContact($this->contactID);
  }

  /**
   * Returns the relationship type Id for the `has Leave Approved by`
   * relationship used to default to the relationship type
   * in the relationship type select field on the Add relationship modal.
   *
   * @return string
   */
  private function getLeaveApproverRelationshipTypeSelectId() {
    $leaveApproverRelationshipTypes = $this->getLeaveApproverRelationshipsTypes();

    return $leaveApproverRelationshipTypes[0] . '_a_b';
  }
}
