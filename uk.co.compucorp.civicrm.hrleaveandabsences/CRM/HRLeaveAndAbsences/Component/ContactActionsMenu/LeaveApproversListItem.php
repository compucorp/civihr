<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

/**
 * Class CRM_HRLeaveAndAbsences_Component_ContactActionsMenu_LeaveApproversListItem
 */
class CRM_HRLeaveAndAbsences_Component_ContactActionsMenu_LeaveApproversListItem
  implements ActionsGroupItemInterface {

  /**
   * @var LeaveManagerService
   */
  private $leaveManagerService;

  /**
   * @var int
   */
  private $contactID;

  /**
   * LeaveApproversListItem constructor.
   *
   * @param LeaveManagerService $leaveManagerService
   * @param int $contactID
   */
  public function __construct(LeaveManagerService $leaveManagerService, $contactID) {
    $this->leaveManagerService = $leaveManagerService;
    $this->contactID = $contactID;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $leaveApprovers = $this->leaveManagerService->getLeaveApproversForContact($this->contactID);
    $markup = '<h4>Leave Approver(s): </h4>';

    foreach($leaveApprovers as $leaveApprover) {
      $markup .= '<p><a href="#" class="text-primary"> ' . $leaveApprover . ' </a></p>';
    }

    return $markup;
  }
}
