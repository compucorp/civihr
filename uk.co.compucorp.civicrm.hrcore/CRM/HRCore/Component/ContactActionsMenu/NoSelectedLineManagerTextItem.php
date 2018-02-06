<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;

/**
 * Class CRM_HRCore_Component_ContactActionsMenu_NoSelectedLineManagerTextItem
 */
class CRM_HRCore_Component_ContactActionsMenu_NoSelectedLineManagerTextItem implements ActionsGroupItemInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    return '<p>You have not selected a Line Manager</p>';
  }
}
