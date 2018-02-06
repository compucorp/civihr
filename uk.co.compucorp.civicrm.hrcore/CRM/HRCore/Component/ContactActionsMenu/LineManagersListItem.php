<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;
use CRM_HRCore_Service_Manager as ManagerService;

class CRM_HRCore_Component_ContactActionsMenu_LineManagersListItem implements ActionsGroupItemInterface {

  /**
   * @var ManagerService
   */
  private $managerService;

  /**
   * @var int
   */
  private $contactID;

  /**
   * CRM_HRCore_Component_ContactActionsMenu_LineManagersListItem constructor.
   *
   * @param ManagerService $managerService
   * @param int $contactID
   */
  public function __construct(ManagerService $managerService, $contactID) {
    $this->managerService = $managerService;
    $this->contactID = $contactID;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $lineManagers = $this->managerService->getLineManagersFor($this->contactID);
    $markup = '<h4>Line Manager(s): </h4>';

    foreach($lineManagers as $lineManager) {
      $markup .= '<p><a href="#" class="text-primary"> ' . $lineManager . ' </a></p>';
    }

    return $markup;
  }
}
