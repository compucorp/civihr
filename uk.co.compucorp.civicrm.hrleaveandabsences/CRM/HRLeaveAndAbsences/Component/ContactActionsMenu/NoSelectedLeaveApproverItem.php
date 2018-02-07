<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;

/**
 * Class CRM_HRLeaveAndAbsences_Component_ContactActionsMenu_NoSelectedLeaveApproverItem
 */
class CRM_HRLeaveAndAbsences_Component_ContactActionsMenu_NoSelectedLeaveApproverItem
  implements ActionsGroupItemInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    return '<p>You have not selected a Leave Approver</p>';
  }
}
